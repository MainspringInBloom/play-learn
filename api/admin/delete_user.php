<?php
require_once __DIR__ . '/../../includes/api.php';

api_method('POST');
api_require_role('admin');
$userId = api_id(api_body()['user_id'] ?? null);
if (!$userId) {
    api_error(400, 'Valid user_id required');
}

try {
    $stmt = $pdo->prepare('SELECT role FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    $role = $stmt->fetchColumn();
    if ($role === false) {
        api_error(404, 'User not found');
    }
    if (!in_array($role, ['student', 'parent'], true)) {
        api_error(403, 'Admin accounts cannot be deleted here');
    }
    // Foreign keys cascade: links, messages, progress and scores go with the account.
    $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$userId]);
    api_respond(200, ['ok' => true]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to delete the account');
}
