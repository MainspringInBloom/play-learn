<?php
require_once __DIR__ . '/../includes/api.php';

api_method('POST');
$user = api_require_role('student');
$messageId = api_id(api_body()['message_id'] ?? null);
if (!$messageId) {
    api_error(400, 'Valid message_id required');
}

try {
    // Only the recipient can mark a message read, and only the first time.
    $stmt = $pdo->prepare('UPDATE feedback_messages SET read_at = NOW() WHERE message_id = ? AND student_id = ? AND read_at IS NULL');
    $stmt->execute([$messageId, $user['user_id']]);
    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare('SELECT 1 FROM feedback_messages WHERE message_id = ? AND student_id = ?');
        $check->execute([$messageId, $user['user_id']]);
        if (!$check->fetchColumn()) {
            api_error(404, 'Message not found');
        }
    }
    api_respond(200, ['ok' => true]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to update message');
}
