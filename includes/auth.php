<?php
/**
 * Session + role-guard helpers.
 * Every protected page starts with:
 *   require __DIR__ . '/../includes/auth.php';
 *   require_role('admin');   // or 'student' / 'parent'
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

function require_role(string $role): void
{
    require_login();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        echo '<p>Access denied — this page is for ' . htmlspecialchars($role) . ' accounts only.</p>';
        echo '<p><a href="/login.php">Back to login</a></p>';
        exit;
    }
}

function current_user(): array
{
    return [
        'user_id'    => $_SESSION['user_id']    ?? null,
        'role'       => $_SESSION['role']       ?? null,
        'first_name' => $_SESSION['first_name'] ?? null,
    ];
}

/** Redirect a freshly logged-in user to the right dashboard for their role. */
function redirect_for_role(string $role): void
{
    $destinations = [
        'admin'   => '/admin/index.php',
        'student' => '/student/dashboard.php',
        'parent'  => '/parent/dashboard.php', // built in a later week
    ];
    header('Location: ' . ($destinations[$role] ?? '/login.php'));
    exit;
}
