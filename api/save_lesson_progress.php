<?php
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/progress.php';

/**
 * "Mark lesson complete" for lessons that have no quiz to pass. A lesson that does have a
 * quiz can only be completed by passing it (api/save_quiz_score.php), so this refuses it.
 */
api_method('POST');
$user = api_require_role('student');
$lessonId = api_id(api_body()['lesson_id'] ?? null);
if (!$lessonId) {
    api_error(400, 'Valid lesson_id required');
}

try {
    $ctx = pl_lesson_context($pdo, $lessonId, $user['user_id']);
    if (!$ctx) {
        api_error(404, 'Lesson not found');
    }
    if ($ctx['lesson']['locked']) {
        api_error(403, 'Finish the earlier lessons first');
    }
    if ($ctx['lesson']['quiz_count'] > 0 && $ctx['lesson']['status'] !== 'completed') {
        api_error(409, 'Pass this lesson\'s quiz to complete it');
    }
    $new = pl_complete_lesson($pdo, $user['user_id'], $lessonId);
    api_respond(200, ['ok' => true, 'completed' => true, 'newly_completed' => $new]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to save progress');
}
