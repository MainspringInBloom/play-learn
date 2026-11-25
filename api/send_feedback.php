<?php
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/scores.php';
require_once __DIR__ . '/../includes/text.php';

api_method('POST');
$user = api_require_role('parent', 'admin');
$input = api_body();

$studentId = api_id($input['student_id'] ?? null);
$body = trim((string) ($input['body'] ?? ''));
$length = pl_strlen($body);
if (!$studentId) {
    api_error(400, 'Valid student_id required');
}
if ($length < 1 || $length > 2000) {
    api_error(400, 'Message must be 1 to 2000 characters');
}

try {
    // Admins may message any student; parents only their own children.
    if (!pl_can_view_student($pdo, $user['role'], $user['user_id'], $studentId)) {
        api_error(403, 'You are not linked to this student');
    }
    if (!pl_student_row($pdo, $studentId)) {
        api_error(404, 'Student not found');
    }
    $pdo->prepare('INSERT INTO feedback_messages (student_id, sender_id, body) VALUES (?, ?, ?)')
        ->execute([$studentId, $user['user_id'], $body]);
    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'SELECT m.message_id, m.sender_id, m.body, m.created_at, m.read_at, u.first_name, u.last_name, u.role
         FROM feedback_messages m JOIN users u ON u.user_id = m.sender_id WHERE m.message_id = ?'
    );
    $stmt->execute([$id]);
    api_respond(200, ['ok' => true, 'message' => pl_feedback_row($stmt->fetch(PDO::FETCH_ASSOC), $user['user_id'])]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to send message');
}
