<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (!is_logged_in() || ($_SESSION['role'] ?? '') !== 'student') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Student login required']);
    exit;
}

$quizId = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);

if (!$quizId || $quizId < 1) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Valid quiz_id required']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT q.quiz_id, q.title, q.lesson_id,
                l.title AS lesson_title, l.course_id
         FROM quizzes q
         JOIN lessons l ON l.lesson_id = q.lesson_id
         WHERE q.quiz_id = ?'
    );
    $stmt->execute([$quizId]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Quiz not found']);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT question_id, question_text, code_snippet,
                explanation, sort_order
         FROM questions
         WHERE quiz_id = ?
         ORDER BY sort_order, question_id'
    );
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $optionsStmt = $pdo->prepare(
    'SELECT option_id, option_text, sort_order
     FROM answer_options
     WHERE question_id = ?
     ORDER BY sort_order, option_id'
);
    foreach ($questions as &$question) {
        $optionsStmt->execute([$question['question_id']]);
        $question['options'] = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($question);

    $quiz['questions'] = $questions;

    echo json_encode(['ok' => true, 'quiz' => $quiz]);

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to load quiz']);
}
