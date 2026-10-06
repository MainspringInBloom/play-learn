<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

$lesson_id = (int) ($_POST['lesson_id'] ?? 0);
$course_id = (int) ($_POST['course_id'] ?? 0);

// ON DELETE CASCADE on quizzes.lesson_id takes care of that table's rows too.
$stmt = $pdo->prepare('DELETE FROM lessons WHERE lesson_id = ? AND course_id = ?');
$stmt->execute([$lesson_id, $course_id]);

header('Location: /admin/lessons.php?course_id=' . $course_id . '&deleted=1');
exit;
