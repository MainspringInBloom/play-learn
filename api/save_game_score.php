<?php
// POST /api/save_game_score.php
// Body (JSON): { lesson_id, score, max_score, time_spent_seconds }
// Saves one row in game_attempts for the logged-in student.
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

// Must match POINTS_PER_PAIR in assets/js/game-runner.js.
const POINTS_PER_PAIR = 50;
// Longest play time we accept (one day), so a bad value can't be stored.
const MAX_TIME_SECONDS = 86400;

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
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

$lessonId = filter_var($input['lesson_id'] ?? null, FILTER_VALIDATE_INT);
$score    = filter_var($input['score'] ?? null, FILTER_VALIDATE_INT);
$maxScore = filter_var($input['max_score'] ?? null, FILTER_VALIDATE_INT);
$seconds  = filter_var($input['time_spent_seconds'] ?? 0, FILTER_VALIDATE_INT);

if (!$lessonId || $lessonId < 1 || $score === false || $score === null
    || !$maxScore || $maxScore < 1 || $seconds === false || $seconds === null) {
    respond(400, ['ok' => false, 'error' => 'Invalid game submission']);
}
if ($score < 0 || $score > $maxScore) {
    respond(400, ['ok' => false, 'error' => 'Score must be between 0 and max_score']);
}
$seconds = max(0, min($seconds, MAX_TIME_SECONDS));

$studentId = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare('SELECT game_type, game_data FROM lessons WHERE lesson_id = ?');
    $stmt->execute([$lessonId]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson || !$lesson['game_type']) {
        respond(404, ['ok' => false, 'error' => 'This lesson has no game']);
    }

    // The best possible score is fixed by the game itself, so don't trust the browser's max_score.
    $data = json_decode((string) $lesson['game_data'], true);
    $pairs = is_array($data) && isset($data['pairs']) && is_array($data['pairs']) ? count($data['pairs']) : 0;
    if ($pairs < 1 || $maxScore !== $pairs * POINTS_PER_PAIR) {
        respond(400, ['ok' => false, 'error' => 'max_score does not match this game']);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO game_attempts (student_id, lesson_id, score, max_score, time_spent_seconds)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$studentId, $lessonId, $score, $maxScore, $seconds]);

    respond(200, [
        'ok'              => true,
        'game_attempt_id' => (int) $pdo->lastInsertId(),
        'score'           => $score,
        'max_score'       => $maxScore,
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Unable to save game score']);
}
