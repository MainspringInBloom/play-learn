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
    $role = $pdo->prepare('SELECT role FROM users WHERE user_id = ?');
    $role->execute([$parentId]);
    if ($role->fetchColumn() !== 'parent') {
        api_error(404, 'Parent not found');
    }
    $role->execute([$studentId]);
    if ($role->fetchColumn() !== 'student') {
        api_error(404, 'Student not found');
    }
    $exists = $pdo->prepare('SELECT 1 FROM parent_students WHERE parent_id = ? AND student_id = ?');
    $exists->execute([$parentId, $studentId]);
    if (!$exists->fetchColumn()) {
        $pdo->prepare('INSERT INTO parent_students (parent_id, student_id) VALUES (?, ?)')->execute([$parentId, $studentId]);
    }
    api_respond(200, ['ok' => true]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to link these accounts');
}
