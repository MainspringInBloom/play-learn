<?php
/**
 * Shared helpers for the JSON endpoints in /api.
 *
 * Usage at the top of an endpoint:
 *   require_once __DIR__ . '/../includes/api.php';   // sends the JSON header, loads auth + db
 *   api_method('POST');
 *   $user = api_require_role('student');             // 401 not logged in, 403 wrong role
 *   $body = api_body();                              // decoded JSON request body (array)
 *   api_respond(200, ['ok' => true, ...]);           // sends JSON and stops
 *
 * Every error is {"ok": false, "error": "message"} with a matching HTTP status,
 * which is what PL.api (assets/js/pl-api.js) turns into an Error.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

function api_respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function api_error(int $status, string $message): void
{
    api_respond($status, ['ok' => false, 'error' => $message]);
}

function api_method(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        api_error(405, $method . ' required');
    }
}

/** Requires a logged-in user with one of the given roles. Returns ['user_id' => int, 'role' => string]. */
function api_require_role(string ...$roles): array
{
    if (!is_logged_in()) {
        api_error(401, 'Please log in');
    }
    $role = (string) ($_SESSION['role'] ?? '');
    if (!in_array($role, $roles, true)) {
        api_error(403, 'Not allowed for your account type');
    }
    return ['user_id' => (int) $_SESSION['user_id'], 'role' => $role];
}

function api_body(): array
{
    $decoded = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($decoded)) {
        api_error(400, 'Invalid JSON');
    }
    return $decoded;
}

/** A positive integer from a request value, or null. */
function api_id($value): ?int
{
    $id = filter_var($value, FILTER_VALIDATE_INT);
    return ($id !== false && $id >= 1) ? (int) $id : null;
}

/** Catches anything unexpected so the browser always gets JSON, and logs the real reason. */
function api_fail(Throwable $e, string $message): void
{
    error_log($e->getMessage());
    api_error(500, $message);
}
