<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

$question_id = (int) ($_POST['question_id'] ?? 0);
$quiz_id     = (int) ($_POST['quiz_id'] ?? 0);

// Options and students' answers to this question go with it (ON DELETE CASCADE).
$stmt = $pdo->prepare('DELETE FROM questions WHERE question_id = ? AND quiz_id = ?');
$stmt->execute([$question_id, $quiz_id]);

header('Location: /admin/questions.php?quiz_id=' . $quiz_id . '&deleted=1');
exit;
