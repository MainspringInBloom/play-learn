<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

$quiz_id   = (int) ($_POST['quiz_id'] ?? 0);
$lesson_id = (int) ($_POST['lesson_id'] ?? 0);

$stmt = $pdo->prepare('DELETE FROM quizzes WHERE quiz_id = ? AND lesson_id = ?');
$stmt->execute([$quiz_id, $lesson_id]);

header('Location: /admin/quizzes.php?lesson_id=' . $lesson_id . '&deleted=1');
exit;
