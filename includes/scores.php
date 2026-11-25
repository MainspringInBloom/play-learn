<?php
/**
 * Progress and score summaries shared by the endpoints behind the Progress page, the
 * parent dashboard and the admin Students pages. Plain portable SQL; the date maths is
 * done in PHP so nothing here depends on MySQL-only functions.
 */

const PL_GAME_LABELS = [
    'match_output' => 'Match the output',
    'memory'       => 'Memory match',
];

function pl_game_label(?string $type): string
{
    return PL_GAME_LABELS[$type] ?? 'Game';
}

/** Whole seconds between two DB timestamps (never negative). */
function pl_seconds_between(?string $from, ?string $to): int
{
    $a = $from ? strtotime($from) : false;
    $b = $to ? strtotime($to) : false;
    return ($a === false || $b === false) ? 0 : max(0, $b - $a);
}

/** A student's basic row, or null if there is no student with that id. */
function pl_student_row(PDO $pdo, int $studentId): ?array
{
    $stmt = $pdo->prepare("SELECT user_id AS student_id, first_name, last_name, email FROM users WHERE user_id = ? AND role = 'student'");
    $stmt->execute([$studentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }
    $row['student_id'] = (int) $row['student_id'];
    return $row;
}

/** Admin: any student. Parent: only linked children. Student: only themselves. */
function pl_can_view_student(PDO $pdo, string $role, int $viewerId, int $studentId): bool
{
    if ($role === 'admin') {
        return true;
    }
    if ($role === 'student') {
        return $viewerId === $studentId;
    }
    if ($role === 'parent') {
        $stmt = $pdo->prepare('SELECT 1 FROM parent_students WHERE parent_id = ? AND student_id = ?');
        $stmt->execute([$viewerId, $studentId]);
        return (bool) $stmt->fetchColumn();
    }
    return false;
}

/**
 * Totals for one student:
 *   lessons_completed, total_lessons, quizzes_taken, quiz_average|null, games_played,
 *   time_spent_seconds, last_active_at|null
 */
function pl_summary(PDO $pdo, int $studentId): array
{
    $one = function (string $sql, array $args = []) use ($pdo) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchColumn();
    };

    $totalLessons = (int) $pdo->query('SELECT COUNT(*) FROM lessons')->fetchColumn();
    $completed = (int) $one("SELECT COUNT(*) FROM lesson_progress WHERE student_id = ? AND status = 'completed'", [$studentId]);

    $stmt = $pdo->prepare('SELECT score, started_at, finished_at FROM quiz_attempts WHERE student_id = ? AND score IS NOT NULL');
    $stmt->execute([$studentId]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $quizSeconds = 0;
    $scoreSum = 0.0;
    $last = [];
    foreach ($attempts as $a) {
        $scoreSum += (float) $a['score'];
        $quizSeconds += pl_seconds_between($a['started_at'], $a['finished_at']);
        $last[] = $a['finished_at'];
    }

    $stmt = $pdo->prepare('SELECT time_spent_seconds, completed_at FROM game_attempts WHERE student_id = ?');
    $stmt->execute([$studentId]);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $gameSeconds = 0;
    foreach ($games as $g) {
        $gameSeconds += (int) $g['time_spent_seconds'];
        $last[] = $g['completed_at'];
    }

    $lessonSeconds = (int) $one('SELECT COALESCE(SUM(time_spent_seconds), 0) FROM lesson_progress WHERE student_id = ?', [$studentId]);
    $last[] = $one('SELECT MAX(completed_at) FROM lesson_progress WHERE student_id = ?', [$studentId]) ?: null;
    $last = array_filter($last);

    return [
        'lessons_completed'  => $completed,
        'total_lessons'      => $totalLessons,
        'quizzes_taken'      => count($attempts),
        'quiz_average'       => $attempts ? round($scoreSum / count($attempts), 1) : null,
        'games_played'       => count($games),
        'time_spent_seconds' => $quizSeconds + $gameSeconds + $lessonSeconds,
        'last_active_at'     => $last ? max($last) : null,
    ];
}

/** The full payload behind PL.api.getProgress(). */
function pl_progress_payload(PDO $pdo, array $student): array
{
    $sid = (int) $student['student_id'];
    $totals = pl_summary($pdo, $sid);
    unset($totals['last_active_at']);

    $stmt = $pdo->prepare(
        'SELECT a.attempt_id, q.title AS quiz_title, a.score, a.finished_at
         FROM quiz_attempts a JOIN quizzes q ON q.quiz_id = a.quiz_id
         WHERE a.student_id = ? AND a.score IS NOT NULL
         ORDER BY a.finished_at DESC, a.attempt_id DESC LIMIT 20'
    );
    $stmt->execute([$sid]);
    $quizScores = array_reverse(array_map(function ($r) {
        return [
            'attempt_id'  => (int) $r['attempt_id'],
            'quiz_title'  => $r['quiz_title'],
            'score'       => (float) $r['score'],
            'finished_at' => $r['finished_at'],
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC)));

    // Activity per day for the last 14 days (days with nothing are left out).
    $from = date('Y-m-d', strtotime('-13 days'));
    $days = [];
    $bump = function (?string $stamp, string $kind) use (&$days, $from) {
        if (!$stamp) {
            return;
        }
        $day = substr($stamp, 0, 10);
        if ($day < $from) {
            return;
        }
        $days[$day] = $days[$day] ?? ['date' => $day, 'lessons' => 0, 'quizzes' => 0, 'games' => 0];
        $days[$day][$kind]++;
    };
    $queries = [
        ['lessons', "SELECT completed_at AS at FROM lesson_progress WHERE student_id = ? AND status = 'completed'"],
        ['quizzes', 'SELECT finished_at AS at FROM quiz_attempts WHERE student_id = ? AND score IS NOT NULL'],
        ['games',   'SELECT completed_at AS at FROM game_attempts WHERE student_id = ?'],
    ];
    foreach ($queries as [$kind, $sql]) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sid]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $at) {
            $bump($at, $kind);
        }
    }
    ksort($days);

    $stmt = $pdo->prepare(
        'SELECT c.course_id, c.title, COUNT(l.lesson_id) AS total_lessons,
                SUM(CASE WHEN p.status = ? THEN 1 ELSE 0 END) AS completed_lessons
         FROM courses c
         LEFT JOIN lessons l ON l.course_id = c.course_id
         LEFT JOIN lesson_progress p ON p.lesson_id = l.lesson_id AND p.student_id = ?
         GROUP BY c.course_id, c.title ORDER BY c.course_id'
    );
    $stmt->execute(['completed', $sid]);
    $courses = array_map(function ($r) {
        return [
            'course_id'         => (int) $r['course_id'],
            'title'             => $r['title'],
            'completed_lessons' => (int) $r['completed_lessons'],
            'total_lessons'     => (int) $r['total_lessons'],
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));

    $stmt = $pdo->prepare(
        'SELECT l.title AS lesson_title, l.game_type, g.score, g.max_score, g.completed_at
         FROM game_attempts g JOIN lessons l ON l.lesson_id = g.lesson_id
         WHERE g.student_id = ?
         ORDER BY g.completed_at DESC, g.game_attempt_id DESC LIMIT 6'
    );
    $stmt->execute([$sid]);
    $gameScores = array_map(function ($r) {
        return [
            'lesson_title' => $r['lesson_title'],
            'game_label'   => pl_game_label($r['game_type']),
            'score'        => (int) $r['score'],
            'max_score'    => (int) $r['max_score'],
            'completed_at' => $r['completed_at'],
        ];
    }, $stmt->fetchAll(PDO::FETCH_ASSOC));

    return [
        'student'        => $student,
        'totals'         => $totals,
        'quiz_scores'    => $quizScores,
        'daily_activity' => array_values($days),
        'courses'        => $courses,
        'game_scores'    => $gameScores,
    ];
}

/** "First Last" for a users row. */
function pl_full_name(array $row): string
{
    return trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
}

/** One feedback row in the shape PL.api.getFeedback() returns. */
function pl_feedback_row(array $r, int $viewerId): array
{
    return [
        'message_id'  => (int) $r['message_id'],
        'sender_name' => pl_full_name(['first_name' => $r['first_name'], 'last_name' => $r['last_name']]),
        'sender_role' => $r['role'],
        'body'        => $r['body'],
        'created_at'  => $r['created_at'],
        'read_at'     => $r['read_at'],
        'is_mine'     => (int) $r['sender_id'] === $viewerId,
    ];
}
