<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('admin');

$lesson_id = (int) ($_GET['lesson_id'] ?? $_POST['lesson_id'] ?? 0);
$quiz_id   = (int) ($_GET['id'] ?? $_POST['quiz_id'] ?? 0);
$is_edit   = $quiz_id > 0;

$lesson_stmt = $pdo->prepare('SELECT lesson_id, title FROM lessons WHERE lesson_id = ?');
$lesson_stmt->execute([$lesson_id]);
$lesson = $lesson_stmt->fetch();
if (!$lesson) {
    http_response_code(404);
    die('Lesson not found.');
}

$quiz = ['title' => ''];
$errors = [];

if ($is_edit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE quiz_id = ? AND lesson_id = ?');
    $stmt->execute([$quiz_id, $lesson_id]);
    $found = $stmt->fetch();
    if (!$found) {
        http_response_code(404);
        die('Quiz not found.');
    }
    $quiz = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz['title'] = trim($_POST['title'] ?? '');

    if ($quiz['title'] === '') {
        $errors[] = 'Title is required.';
    }

    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $pdo->prepare('UPDATE quizzes SET title = ? WHERE quiz_id = ? AND lesson_id = ?');
            $stmt->execute([$quiz['title'], $quiz_id, $lesson_id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO quizzes (lesson_id, title) VALUES (?, ?)');
            $stmt->execute([$lesson_id, $quiz['title']]);
        }

        header('Location: /admin/quizzes.php?lesson_id=' . $lesson_id . '&saved=1');
        exit;
    }
}

$page_title = ($is_edit ? 'Edit' : 'Add') . ' quiz';
require __DIR__ . '/../includes/admin_header.php';
?>
<nav class="small mb-2">
    <a href="/admin/index.php">Courses</a> /
    <a href="/admin/quizzes.php?lesson_id=<?= $lesson_id ?>"><?= htmlspecialchars($lesson['title']) ?></a> /
    <?= $is_edit ? 'Edit quiz' : 'Add quiz' ?>
</nav>

<h1 class="h3 mb-4"><?= $is_edit ? 'Edit quiz' : 'Add quiz' ?></h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<form method="post" class="pl-form" style="max-width: 640px;">
    <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
    <?php if ($is_edit): ?>
        <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label" for="title">Title</label>
        <input class="form-control" id="title" name="title" required
               value="<?= htmlspecialchars($quiz['title']) ?>">
    </div>

    <p class="text-muted small">
        Questions and answer options are added in a later week once the
        <code>questions</code> / <code>answer_options</code> tables are built.
    </p>

    <button class="btn btn-pl-primary" type="submit"><?= $is_edit ? 'Save changes' : 'Add quiz' ?></button>
    <a class="btn btn-outline-secondary" href="/admin/quizzes.php?lesson_id=<?= $lesson_id ?>">Cancel</a>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
