<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/text.php';
require_role('admin');

$quiz_id = (int) ($_GET['quiz_id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT q.quiz_id, q.title, q.lesson_id, l.title AS lesson_title, l.course_id, c.title AS course_title
    FROM quizzes q
    JOIN lessons l ON l.lesson_id = q.lesson_id
    JOIN courses c ON c.course_id = l.course_id
    WHERE q.quiz_id = ?
');
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    http_response_code(404);
    die('Quiz not found.');
}

$stmt = $pdo->prepare('
    SELECT s.question_id, s.question_text, s.sort_order,
           (SELECT COUNT(*) FROM answer_options o WHERE o.question_id = s.question_id) AS option_count
    FROM questions s
    WHERE s.quiz_id = ?
    ORDER BY s.sort_order, s.question_id
');
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

$page_title = $quiz['title'] . ' — Questions';
require __DIR__ . '/../includes/admin_header.php';
?>
<nav class="small mb-2">
    <a href="/admin/index.php">Courses</a> /
    <a href="/admin/lessons.php?course_id=<?= (int) $quiz['course_id'] ?>"><?= htmlspecialchars($quiz['course_title']) ?></a> /
    <a href="/admin/quizzes.php?lesson_id=<?= (int) $quiz['lesson_id'] ?>"><?= htmlspecialchars($quiz['lesson_title']) ?></a> /
    <?= htmlspecialchars($quiz['title']) ?>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= htmlspecialchars($quiz['title']) ?> — Questions</h1>
    <a class="btn btn-pl-primary" href="/admin/question_form.php?quiz_id=<?= $quiz_id ?>">+ Add question</a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Question saved.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Question deleted.</div>
<?php endif; ?>
<?php if (isset($_GET['new'])): ?>
    <div class="alert alert-info">Quiz created. Add its questions below. Students only see a quiz once it has at least one question.</div>
<?php endif; ?>

<table class="table pl-table align-middle">
    <thead>
        <tr><th>#</th><th>Question</th><th>Options</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($questions as $i => $question): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars(pl_truncate($question['question_text'], 90)) ?></td>
                <td><?= (int) $question['option_count'] ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary"
                       href="/admin/question_form.php?quiz_id=<?= $quiz_id ?>&amp;id=<?= (int) $question['question_id'] ?>">Edit</a>
                    <form class="d-inline" method="post" action="/admin/delete_question.php"
                          onsubmit="return confirm('Delete this question? Students\' past answers to it are removed too.');">
                        <input type="hidden" name="question_id" value="<?= (int) $question['question_id'] ?>">
                        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($questions)): ?>
            <tr><td colspan="4" class="text-muted">No questions yet. Students can't take this quiz until it has at least one.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
