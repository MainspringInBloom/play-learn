<?php
require_once __DIR__ . '/../../includes/api.php';

api_method('POST');
api_require_role('admin');
$in = api_body();
$parentId = api_id($in['parent_id'] ?? null);
$studentId = api_id($in['student_id'] ?? null);
if (!$parentId || !$studentId) {
    api_error(400, 'parent_id and student_id are required');
}

try {
    $pdo->prepare('DELETE FROM parent_students WHERE parent_id = ? AND student_id = ?')->execute([$parentId, $studentId]);
    api_respond(200, ['ok' => true]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to remove the link');
}
