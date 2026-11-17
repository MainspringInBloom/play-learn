
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
    foreach ($rows as $row) {
        $qid = (int) $row['question_id'];
        $oid = (int) $row['option_id'];
        $questions[$qid][$oid] = (bool) $row['is_correct'];
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
         (student_id, quiz_id, score, correct_count, total_questions, finished_at)
         VALUES (?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$studentId, $quizId, $score, $correct, $total]);
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

    respond(200, [
        'ok' => true,
        'attempt_id' => $attemptId,
        'score' => $score,
        'correct_count' => $correct,
        'total_questions' => $total
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Unable to save quiz score']);
}
