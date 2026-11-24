<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

function respond(int $status, array $data): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'POST required']);
}

if (!is_logged_in() || ($_SESSION['role'] ?? '') !== 'student') {
    respond(401, ['ok' => false, 'error' => 'Student login required']);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond(400, ['ok' => false, 'error' => 'Invalid JSON']);
}

$quizId = filter_var($input['quizId'] ?? null, FILTER_VALIDATE_INT);
$answers = $input['answers'] ?? [];

// Seconds spent on the quiz (the page sends timeSpentSeconds). Whole number, 0 or more,
// capped at one day so a tab left open overnight can't produce a silly start time.
$seconds = filter_var($input['timeSpentSeconds'] ?? $input['time_spent_seconds'] ?? 0, FILTER_VALIDATE_INT);
$seconds = ($seconds === false || $seconds < 0) ? 0 : min($seconds, 86400);

if (!$quizId || $quizId < 1 || !is_array($answers)) {
    respond(400, ['ok' => false, 'error' => 'Invalid quiz submission']);
}

$studentId = (int) $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'SELECT q.question_id, ao.option_id, ao.is_correct
         FROM questions q
         JOIN answer_options ao ON ao.question_id = q.question_id
         WHERE q.quiz_id = ?'
    );
    $stmt->execute([$quizId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $questions = [];
    $correctOption = [];
    foreach ($rows as $row) {
        $qid = (int) $row['question_id'];
        $oid = (int) $row['option_id'];
        $questions[$qid][$oid] = (bool) $row['is_correct'];
        if ($row['is_correct']) {
            $correctOption[$qid] = $oid;
        }
    }

    if (!$questions) {
        $pdo->rollBack();
        respond(404, ['ok' => false, 'error' => 'Quiz has no questions']);
    }

    $selected = [];
    foreach ($answers as $answer) {
        $qid = filter_var($answer['question_id'] ?? null, FILTER_VALIDATE_INT);
        $oid = filter_var($answer['option_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$qid || !$oid || !isset($questions[$qid][$oid]) || isset($selected[$qid])) {
            $pdo->rollBack();
            respond(400, ['ok' => false, 'error' => 'Invalid or duplicate answer']);
        }

        $selected[$qid] = $oid;
    }

    $correct = 0;
    foreach ($selected as $qid => $oid) {
        if ($questions[$qid][$oid]) {
            $correct++;
        }
    }

    $total = count($questions);
    $score = round(($correct / $total) * 100, 2);

    $stmt = $pdo->prepare(
        'INSERT INTO quiz_attempts
         (student_id, quiz_id, score, correct_count, total_questions, started_at, finished_at)
         VALUES (?, ?, ?, ?, ?, NOW() - INTERVAL ? SECOND, NOW())'
    );
    // started_at is back-dated by the time spent, so finished_at - started_at is the duration
    // the Progress page adds up.
    $stmt->bindValue(1, $studentId, PDO::PARAM_INT);
    $stmt->bindValue(2, $quizId, PDO::PARAM_INT);
    $stmt->bindValue(3, $score);
    $stmt->bindValue(4, $correct, PDO::PARAM_INT);
    $stmt->bindValue(5, $total, PDO::PARAM_INT);
    $stmt->bindValue(6, $seconds, PDO::PARAM_INT);
    $stmt->execute();
    $attemptId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO quiz_responses
         (attempt_id, question_id, option_id, is_correct)
         VALUES (?, ?, ?, ?)'
    );

    foreach ($selected as $qid => $oid) {
        $stmt->execute([
            $attemptId,
            $qid,
            $oid,
            (int) $questions[$qid][$oid]
        ]);
    }

    $pdo->commit();

    // Per-question results so the page can show what was right (only after submitting).
    $results = [];
    foreach (array_keys($questions) as $qid) {
        $picked = $selected[$qid] ?? null;
        $results[] = [
            'question_id'        => $qid,
            'selected_option_id' => $picked,
            'correct_option_id'  => $correctOption[$qid] ?? null,
            'is_correct'         => $picked !== null && $questions[$qid][$picked],
        ];
    }

    respond(200, [
        'ok' => true,
        'attempt_id' => $attemptId,
        'score' => $score,
        'correct_count' => $correct,
        'total_questions' => $total,
        'results' => $results
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Unable to save quiz score']);
}
