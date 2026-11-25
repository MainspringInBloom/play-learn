<?php
require_once __DIR__ . '/../../includes/api.php';
require_once __DIR__ . '/../../includes/scores.php';

api_method('GET');
api_require_role('admin');

try {
    $students = $pdo->query(
        "SELECT user_id, first_name, last_name, email FROM users WHERE role = 'student' ORDER BY first_name, last_name, user_id"
    )->fetchAll(PDO::FETCH_ASSOC);

    $parents = [];
    $rows = $pdo->query(
        'SELECT ps.student_id, u.user_id, u.first_name, u.last_name
         FROM parent_students ps JOIN users u ON u.user_id = ps.parent_id
         ORDER BY u.first_name, u.last_name, u.user_id'
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $parents[(int) $r['student_id']][] = ['user_id' => (int) $r['user_id'], 'name' => pl_full_name($r)];
    }

    $out = [];
    foreach ($students as $s) {
        $sid = (int) $s['user_id'];
        $sum = pl_summary($pdo, $sid);
        $out[] = [
            'student_id'         => $sid,
            'first_name'         => $s['first_name'],
            'last_name'          => $s['last_name'],
            'email'              => $s['email'],
            'lessons_completed'  => $sum['lessons_completed'],
            'total_lessons'      => $sum['total_lessons'],
            'quiz_average'       => $sum['quiz_average'],
            'games_played'       => $sum['games_played'],
            'time_spent_seconds' => $sum['time_spent_seconds'],
            'last_active_at'     => $sum['last_active_at'],
            'parents'            => $parents[$sid] ?? [],
        ];
    }
    api_respond(200, ['ok' => true, 'students' => $out]);
} catch (Throwable $e) {
    api_fail($e, 'Unable to load students');
}
