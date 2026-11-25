<?php
require_once __DIR__ . '/../includes/api.php';
require_once __DIR__ . '/../includes/scores.php';

api_method('GET');
$user = api_require_role('parent');

try {
    $stmt = $pdo->prepare(
        'SELECT u.user_id AS student_id, u.first_name, u.last_name
         FROM parent_students ps JOIN users u ON u.user_id = ps.student_id
         WHERE ps.parent_id = ? ORDER BY u.first_name, u.last_name, u.user_id'
    );
    $stmt->execute([$user['user_id']]);
    $children = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $s = pl_summary($pdo, (int) $row['student_id']);
        $children[] = [
            'student_id'        => (int) $row['student_id'],
            'first_name'        => $row['first_name'],
            'last_name'         => $row['last_name'],
            'lessons_completed' => $s['lessons_completed'],
            'total_lessons'     => $s['total_lessons'],
            'quiz_average'      => $s['quiz_average'],
            'games_played'      => $s['games_played'],
            'last_active_at'    => $s['last_active_at'],
        ];
    }
    api_respond(200, ['ok' => true, 'children' => $children]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to load your children');
}
