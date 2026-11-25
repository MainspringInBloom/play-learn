<?php
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/scores.php';

api_method('GET');
$user = api_require_role('student', 'parent', 'admin');

// Students always see themselves; parents and admins name a student with ?student_id=.
$studentId = $user['role'] === 'student' ? $user['user_id'] : api_id($_GET['student_id'] ?? null);
if (!$studentId) {
    api_error(400, 'Valid student_id required');
}

try {
    if (!pl_can_view_student($pdo, $user['role'], $user['user_id'], $studentId)) {
        api_error(403, 'You are not linked to this student');
    }
    $student = pl_student_row($pdo, $studentId);
    if (!$student) {
        api_error(404, 'Student not found');
    }
    api_respond(200, ['ok' => true] + pl_progress_payload($pdo, $student));
} catch (Throwable $e) {
    api_fail($e, 'Unable to load progress');
}
