<?php
require_once __DIR__ . '/../../includes/api.php';
require_once __DIR__ . '/../../includes/users.php';

api_method('GET');
api_require_role('admin');

$role = (string) ($_GET['role'] ?? '');
if (!in_array($role, ['student', 'parent'], true)) {
    api_error(400, 'role must be student or parent');
}

try {
    $stmt = $pdo->prepare('SELECT user_id, role, first_name, last_name, email, created_at FROM users WHERE role = ? ORDER BY first_name, last_name, user_id');
    $stmt->execute([$role]);
    $users = array_map(function ($u) use ($pdo) {
        return pl_user_with_links($pdo, $u);
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    api_respond(200, ['ok' => true, 'users' => $users]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to load users');
}
