<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/text.php';
require_role('admin');

const MIN_OPTIONS = 2;
const MAX_OPTIONS = 6;

$quiz_id     = (int) ($_GET['quiz_id'] ?? $_POST['quiz_id'] ?? 0);
$question_id = (int) ($_GET['id'] ?? $_POST['question_id'] ?? 0);
$is_edit     = $question_id > 0;

$stmt = $pdo->prepare('SELECT quiz_id, title, lesson_id FROM quizzes WHERE quiz_id = ?');
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    http_response_code(404);
    die('Quiz not found.');
}

$question = ['question_text' => '', 'code_snippet' => '', 'explanation' => '', 'sort_order' => 0];
// Each option row: id (existing option_id or 0 for a new one), text, correct (bool)
$options = [];
$errors = [];

$existing = [];
if ($is_edit) {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE question_id = ? AND quiz_id = ?');
    $stmt->execute([$question_id, $quiz_id]);
    $found = $stmt->fetch();
    if (!$found) {
        http_response_code(404);
        die('Question not found.');
    }
    $stmt = $pdo->prepare('SELECT option_id, option_text, is_correct FROM answer_options WHERE question_id = ? ORDER BY sort_order, option_id');
    $stmt->execute([$question_id]);
    $existing = $stmt->fetchAll();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $question = $found;
        foreach ($existing as $o) {
            $options[] = ['id' => (int) $o['option_id'], 'text' => $o['option_text'], 'correct' => (bool) $o['is_correct']];
        }
    }
} else {
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM questions WHERE quiz_id = ?');
    $stmt->execute([$quiz_id]);
    $question['sort_order'] = (int) $stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question['question_text'] = trim($_POST['question_text'] ?? '');
    $question['code_snippet']  = rtrim((string) ($_POST['code_snippet'] ?? ''));
    $question['explanation']   = trim($_POST['explanation'] ?? '');
    $question['sort_order']    = (int) ($_POST['sort_order'] ?? 0);

    $correct_index = (int) ($_POST['correct'] ?? -1);
    $submitted = is_array($_POST['options'] ?? null) ? array_values($_POST['options']) : [];
    $kept_ids = array_map(function ($o) { return (int) $o['option_id']; }, $existing);

    foreach ($submitted as $i => $row) {
        $text = trim((string) ($row['text'] ?? ''));
        if ($text === '') {
            continue; // blank rows are dropped
        }
        $id = (int) ($row['id'] ?? 0);
        $options[] = [
            'id'      => in_array($id, $kept_ids, true) ? $id : 0,
            'text'    => $text,
            'correct' => $i === $correct_index,
        ];
    }

    if ($question['question_text'] === '') {
        $errors[] = 'The question text is required.';
    }
    if (count($options) < MIN_OPTIONS) {
        $errors[] = 'Add at least ' . MIN_OPTIONS . ' answer options.';
    }
    if (count($options) > MAX_OPTIONS) {
        $errors[] = 'Use at most ' . MAX_OPTIONS . ' answer options.';
    }
    $correct_count = count(array_filter($options, function ($o) { return $o['correct']; }));
    if ($correct_count !== 1) {
        $errors[] = 'Mark exactly one option as the correct answer (and make sure it has text).';
    }
    foreach ($options as $o) {
        if (pl_strlen($o['text']) > 255) {
            $errors[] = 'Answer options can be at most 255 characters.';
            break;
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $snippet = $question['code_snippet'] === '' ? null : $question['code_snippet'];
            $explanation = $question['explanation'] === '' ? null : $question['explanation'];

            if ($is_edit) {
                $pdo->prepare('UPDATE questions SET question_text = ?, code_snippet = ?, explanation = ?, sort_order = ? WHERE question_id = ? AND quiz_id = ?')
                    ->execute([$question['question_text'], $snippet, $explanation, $question['sort_order'], $question_id, $quiz_id]);
            } else {
                $pdo->prepare('INSERT INTO questions (quiz_id, question_text, code_snippet, explanation, sort_order) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$quiz_id, $question['question_text'], $snippet, $explanation, $question['sort_order']]);
                $question_id = (int) $pdo->lastInsertId();
            }

            // Options that were already there are updated in place (so students' past answers
            // stay linked to them); new ones are inserted; ones removed from the form are deleted.
            $update = $pdo->prepare('UPDATE answer_options SET option_text = ?, is_correct = ?, sort_order = ? WHERE option_id = ? AND question_id = ?');
            $insert = $pdo->prepare('INSERT INTO answer_options (question_id, option_text, is_correct, sort_order) VALUES (?, ?, ?, ?)');
            $still_used = [];
            foreach ($options as $order => $o) {
                if ($o['id'] > 0) {
                    $update->execute([$o['text'], (int) $o['correct'], $order, $o['id'], $question_id]);
                    $still_used[] = $o['id'];
                } else {
                    $insert->execute([$question_id, $o['text'], (int) $o['correct'], $order]);
                }
            }
            $delete = $pdo->prepare('DELETE FROM answer_options WHERE option_id = ? AND question_id = ?');
            foreach ($kept_ids as $old_id) {
                if (!in_array($old_id, $still_used, true)) {
                    $delete->execute([$old_id, $question_id]);
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log($e->getMessage());
            $errors[] = 'Could not save the question. Please try again.';
        }
        if (empty($errors)) {
            header('Location: /admin/questions.php?quiz_id=' . $quiz_id . '&saved=1');
            exit;
        }
    }
}

// Always show at least the minimum number of rows.
while (count($options) < MIN_OPTIONS) {
    $options[] = ['id' => 0, 'text' => '', 'correct' => false];
}
if (!array_filter($options, function ($o) { return $o['correct']; })) {
    $options[0]['correct'] = true;
}

$page_title = ($is_edit ? 'Edit' : 'Add') . ' question';
require __DIR__ . '/../includes/admin_header.php';

function option_row(array $o): void { ?>
    <div class="input-group mb-2 js-row">
        <span class="input-group-text">
            <input type="radio" name="correct" data-index class="form-check-input mt-0" aria-label="Correct answer"<?= $o['correct'] ? ' checked' : '' ?>>
        </span>
        <input type="hidden" data-name="options[{i}][id]" value="<?= (int) $o['id'] ?>">
        <input type="text" class="form-control" data-name="options[{i}][text]" maxlength="255"
               placeholder="Answer option" value="<?= htmlspecialchars($o['text']) ?>">
        <button type="button" class="btn btn-outline-danger js-remove" aria-label="Remove option">&times;</button>
    </div>
<?php } ?>
<nav class="small mb-2">
    <a href="/admin/index.php">Courses</a> /
    <a href="/admin/quizzes.php?lesson_id=<?= (int) $quiz['lesson_id'] ?>">Quizzes</a> /
    <a href="/admin/questions.php?quiz_id=<?= $quiz_id ?>"><?= htmlspecialchars($quiz['title']) ?></a> /
    <?= $is_edit ? 'Edit question' : 'Add question' ?>
</nav>

<h1 class="h3 mb-4"><?= $is_edit ? 'Edit question' : 'Add question' ?></h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<form method="post" class="pl-form" style="max-width: 720px;">
    <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
    <?php if ($is_edit): ?>
        <input type="hidden" name="question_id" value="<?= $question_id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label" for="question_text">Question</label>
        <textarea class="form-control" id="question_text" name="question_text" rows="2" required><?= htmlspecialchars($question['question_text']) ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label" for="code_snippet">Code snippet <span class="text-muted small">(optional, shown above the question)</span></label>
        <textarea class="form-control font-monospace" id="code_snippet" name="code_snippet" rows="4"><?= htmlspecialchars($question['code_snippet'] ?? '') ?></textarea>
    </div>

    <div class="mb-3" data-repeater data-min="<?= MIN_OPTIONS ?>" data-max="<?= MAX_OPTIONS ?>">
        <label class="form-label">Answer options <span class="text-muted small">(select the radio button next to the correct one, <?= MIN_OPTIONS ?>&ndash;<?= MAX_OPTIONS ?> options)</span></label>
        <div data-repeater-rows>
            <?php foreach ($options as $o) { option_row($o); } ?>
        </div>
        <template data-repeater-template><?php option_row(['id' => 0, 'text' => '', 'correct' => false]); ?></template>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-repeater-add>+ Add option</button>
        <?php if ($is_edit): ?>
            <p class="text-muted small mt-2 mb-0">Removing an option also removes any past student answers that picked it.</p>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label" for="explanation">Explanation <span class="text-muted small">(optional, shown in the student's review)</span></label>
        <textarea class="form-control" id="explanation" name="explanation" rows="2"><?= htmlspecialchars($question['explanation'] ?? '') ?></textarea>
    </div>

    <div class="mb-3" style="max-width: 160px;">
        <label class="form-label" for="sort_order">Order</label>
        <input class="form-control" type="number" id="sort_order" name="sort_order" value="<?= (int) $question['sort_order'] ?>">
    </div>

    <button class="btn btn-pl-primary" type="submit"><?= $is_edit ? 'Save changes' : 'Add question' ?></button>
    <a class="btn btn-outline-secondary" href="/admin/questions.php?quiz_id=<?= $quiz_id ?>">Cancel</a>
</form>

<script src="/assets/js/admin-repeater.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
