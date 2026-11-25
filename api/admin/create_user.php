<?php
require_once __DIR__ . '/../../includes/api.php';
require_once __DIR__ . '/../../includes/users.php';

api_method('POST');
api_require_role('admin');
$in = api_body();

$role = (string) ($in['role'] ?? '');
$first = trim((string) ($in['first_name'] ?? ''));
$last = trim((string) ($in['last_name'] ?? ''));
$email = trim((string) ($in['email'] ?? ''));
$password = (string) ($in['password'] ?? '');

if (!in_array($role, ['student', 'parent'], true)) {
    api_error(400, 'Role must be student or parent');
}
if ($first === '' || $last === '' || strlen($first) > 50 || strlen($last) > 50) {
    api_error(400, 'First and last name are required (50 characters max)');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    api_error(400, 'Enter a valid email address');
}
if (strlen($password) < 8) {
    api_error(400, 'Password must be at least 8 characters');
}

try {
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn()) {
        api_error(409, 'An account with that email already exists');
    }
    $pdo->prepare('INSERT INTO users (role, first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?, ?)')
        ->execute([$role, $first, $last, $email, password_hash($password, PASSWORD_DEFAULT)]);
    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT user_id, role, first_name, last_name, email, created_at FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    api_respond(200, ['ok' => true, 'user' => pl_user_with_links($pdo, $stmt->fetch(PDO::FETCH_ASSOC))]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to create the account');
}
