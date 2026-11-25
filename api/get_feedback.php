<?php
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/scores.php';

api_method('GET');
$user = api_require_role('student', 'parent', 'admin');

$studentId = $user['role'] === 'student' ? $user['user_id'] : api_id($_GET['student_id'] ?? null);
if (!$studentId) {
    api_error(400, 'Valid student_id required');
}

try {
    if (!pl_can_view_student($pdo, $user['role'], $user['user_id'], $studentId)) {
        api_error(403, 'You are not linked to this student');
    }
    $stmt = $pdo->prepare(
        'SELECT m.message_id, m.sender_id, m.body, m.created_at, m.read_at,
                u.first_name, u.last_name, u.role
         FROM feedback_messages m JOIN users u ON u.user_id = m.sender_id
         WHERE m.student_id = ?
         ORDER BY m.created_at DESC, m.message_id DESC'
    );
    $stmt->execute([$studentId]);
    $messages = array_map(function ($r) use ($user) {
        return pl_feedback_row($r, $user['user_id']);
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    api_respond(200, ['ok' => true, 'messages' => $messages]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to load messages');
}
