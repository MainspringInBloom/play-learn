<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('student');

$course_id = (int) ($_GET['course_id'] ?? 0);

$course_stmt = $pdo->prepare('SELECT course_id, title, description FROM courses WHERE course_id = ?');
$course_stmt->execute([$course_id]);
$course = $course_stmt->fetch();
if (!$course) {
    http_response_code(404);
    die('Course not found.');
}

$page_title = $course['title'] . ' — Syllabus';
$active_nav = 'dashboard';

$stmt = $pdo->prepare('SELECT lesson_id, title, sort_order FROM lessons WHERE course_id = ? ORDER BY sort_order, lesson_id');
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<a class="small d-inline-block mb-3" href="/student/dashboard.php">&larr; Back to dashboard</a>
<h1 class="h3 mb-1"><?= htmlspecialchars($course['title']) ?></h1>
<p class="text-muted mb-4"><?= htmlspecialchars($course['description'] ?? '') ?></p>

<ol class="list-group list-group-numbered">
    <?php foreach ($lessons as $lesson): ?>
        <li class="list-group-item"><?= htmlspecialchars($lesson['title']) ?></li>
    <?php endforeach; ?>
</ol>

<?php require __DIR__ . '/../includes/footer.php'; ?>
