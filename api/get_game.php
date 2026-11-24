<?php
// GET /api/get_game.php?lesson_id=N&type=match_output|memory
// Returns the game stored on a lesson (lessons.game_type / lessons.game_data).
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_logged_in() || ($_SESSION['role'] ?? '') !== 'student') {
    respond(401, ['ok' => false, 'error' => 'Student login required']);
}

$lessonId = filter_input(INPUT_GET, 'lesson_id', FILTER_VALIDATE_INT);
$type = (string) ($_GET['type'] ?? '');

if (!$lessonId || $lessonId < 1) {
    respond(400, ['ok' => false, 'error' => 'Valid lesson_id required']);
}

$labels = [
    'match_output' => 'Match the Output',
    'memory'       => 'Memory Match',
];

try {
    $stmt = $pdo->prepare(
        'SELECT l.lesson_id, l.title, l.game_type, l.game_data, c.title AS course_title
         FROM lessons l
         JOIN courses c ON c.course_id = l.course_id
         WHERE l.lesson_id = ?'
    );
    $stmt->execute([$lessonId]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson || !$lesson['game_type'] || !isset($labels[$lesson['game_type']])) {
        respond(404, ['ok' => false, 'error' => 'This lesson has no game yet']);
    }
    if ($type !== '' && $type !== $lesson['game_type']) {
        respond(404, ['ok' => false, 'error' => 'That game is not part of this lesson']);
    }

    $data = json_decode((string) $lesson['game_data'], true);
    if (!is_array($data) || empty($data['pairs']) || !is_array($data['pairs'])) {
        respond(404, ['ok' => false, 'error' => 'This lesson has no game yet']);
    }

    respond(200, [
        'ok'   => true,
        'game' => [
            'title'        => $labels[$lesson['game_type']],
            'context'      => $lesson['course_title'] . ' · ' . $lesson['title'],
            'instructions' => (string) ($data['instructions'] ?? ''),
            'pairs'        => $data['pairs'],
        ],
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    respond(500, ['ok' => false, 'error' => 'Unable to load game']);
}
