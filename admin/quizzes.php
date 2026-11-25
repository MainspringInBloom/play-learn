<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('admin');

$lesson_id = (int) ($_GET['lesson_id'] ?? 0);

$lesson_stmt = $pdo->prepare('
    SELECT l.lesson_id, l.title, l.course_id, c.title AS course_title
    FROM lessons l
    JOIN courses c ON c.course_id = l.course_id
    WHERE l.lesson_id = ?
');
$lesson_stmt->execute([$lesson_id]);
$lesson = $lesson_stmt->fetch();

if (!$lesson) {
    http_response_code(404);
    die('Lesson not found.');
}

$page_title = $lesson['title'] . ' — Quizzes';

$quizzes_stmt = $pdo->prepare('SELECT q.quiz_id, q.title, (SELECT COUNT(*) FROM questions s WHERE s.quiz_id = q.quiz_id) AS question_count FROM quizzes q WHERE q.lesson_id = ? ORDER BY q.quiz_id');
$quizzes_stmt->execute([$lesson_id]);
$quizzes = $quizzes_stmt->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>
<nav class="small mb-2">
    <a href="/admin/index.php">Courses</a> /
    <a href="/admin/lessons.php?course_id=<?= (int) $lesson['course_id'] ?>"><?= htmlspecialchars($lesson['course_title']) ?></a> /
    <?= htmlspecialchars($lesson['title']) ?>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= htmlspecialchars($lesson['title']) ?> — Quizzes</h1>
    <a class="btn btn-pl-primary" href="/admin/quiz_form.php?lesson_id=<?= $lesson_id ?>">+ Add quiz</a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Quiz saved.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Quiz deleted.</div>
<?php endif; ?>

<table class="table pl-table align-middle">
    <thead>
        <tr>
            <th>Title</th>
            <th>Questions</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td><?= htmlspecialchars($quiz['title']) ?></td>
                <td>
                    <a href="/admin/questions.php?quiz_id=<?= (int) $quiz['quiz_id'] ?>"><?= (int) $quiz['question_count'] ?> question<?= (int) $quiz['question_count'] === 1 ? '' : 's' ?></a>
                    <?php if ((int) $quiz['question_count'] === 0): ?><span class="badge text-bg-warning ms-1">Hidden from students</span><?php endif; ?>
                </td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary"
                       href="/admin/questions.php?quiz_id=<?= (int) $quiz['quiz_id'] ?>">Questions</a>
                    <a class="btn btn-sm btn-outline-secondary"
                       href="/admin/quiz_form.php?lesson_id=<?= $lesson_id ?>&amp;id=<?= (int) $quiz['quiz_id'] ?>">Edit</a>
                    <form class="d-inline" method="post" action="/admin/delete_quiz.php"
                          onsubmit="return confirm('Delete this quiz, its questions and students\' scores on it?');">
                        <input type="hidden" name="quiz_id" value="<?= (int) $quiz['quiz_id'] ?>">
                        <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($quizzes)): ?>
            <tr><td colspan="3" class="text-muted">No quizzes yet for this lesson.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
