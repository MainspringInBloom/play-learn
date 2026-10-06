<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('student');

$lesson_id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT l.lesson_id, l.title, l.content, l.course_id, c.title AS course_title
    FROM lessons l
    JOIN courses c ON c.course_id = l.course_id
    WHERE l.lesson_id = ?
');
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();
if (!$lesson) {
    http_response_code(404);
    die('Lesson not found.');
}

$page_title = $lesson['title'];
$active_nav = 'dashboard';

require __DIR__ . '/../includes/header.php';
?>
<a class="small d-inline-block mb-3" href="/student/dashboard.php">&larr; Back to dashboard</a>
<p class="text-muted small mb-1"><?= htmlspecialchars($lesson['course_title']) ?></p>
<h1 class="h3 mb-3"><?= htmlspecialchars($lesson['title']) ?></h1>

<div class="card pl-card">
    <div class="card-body">
        <?= nl2br(htmlspecialchars($lesson['content'] ?? 'No content yet.')) ?>
    </div>
</div>

<p class="text-muted small mt-3">
    Lesson progress tracking and the matching game for this lesson are built in week 5.
</p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
