<?php
/**
 * Lesson flow rules: lesson > quiz > next lesson > quiz ...
 *
 *  - A lesson is COMPLETE when the student has passed every quiz that has questions
 *    (score >= PL_PASS_PERCENT). A lesson with no playable quiz is completed with the
 *    "Mark lesson complete" button instead.
 *  - A lesson is LOCKED until every earlier lesson in its course is complete
 *    (set PL_LOCK_FUTURE_LESSONS to false to let students roam freely).
 *  - A completed lesson (and its passed quizzes) can always be revisited. Revisiting a
 *    passed quiz is PRACTICE: it is graded but nothing is saved, so progress and scores
 *    are never overwritten.
 *
 * All helpers take the PDO connection first and only use portable SQL.
 */

const PL_PASS_PERCENT = 60;
const PL_LOCK_FUTURE_LESSONS = true;

/**
 * Every lesson of a course, in teaching order, with this student's state:
 *   lesson_id, title, sort_order, game_type, quizzes: [{quiz_id, title, questions, passed, best}],
 *   quiz_count (quizzes that have questions), status ('not_started'|'in_progress'|'completed'),
 *   locked (bool), is_current (the first lesson not yet completed)
 */
function pl_course_lessons(PDO $pdo, int $courseId, int $studentId): array
{
    $stmt = $pdo->prepare(
        'SELECT lesson_id, title, sort_order, game_type FROM lessons
         WHERE course_id = ? ORDER BY sort_order, lesson_id'
    );
    $stmt->execute([$courseId]);
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$lessons) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT q.quiz_id, q.lesson_id, q.title, COUNT(s.question_id) AS questions
         FROM quizzes q
         JOIN lessons l ON l.lesson_id = q.lesson_id
         LEFT JOIN questions s ON s.quiz_id = q.quiz_id
         WHERE l.course_id = ?
         GROUP BY q.quiz_id, q.lesson_id, q.title
         ORDER BY q.quiz_id'
    );
    $stmt->execute([$courseId]);
    $quizzesByLesson = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $q) {
        $quizzesByLesson[(int) $q['lesson_id']][] = [
            'quiz_id'   => (int) $q['quiz_id'],
            'title'     => $q['title'],
            'questions' => (int) $q['questions'],
        ];
    }

    $stmt = $pdo->prepare(
        'SELECT a.quiz_id, MAX(a.score) AS best
         FROM quiz_attempts a
         JOIN quizzes q ON q.quiz_id = a.quiz_id
         JOIN lessons l ON l.lesson_id = q.lesson_id
         WHERE a.student_id = ? AND l.course_id = ? AND a.score IS NOT NULL
         GROUP BY a.quiz_id'
    );
    $stmt->execute([$studentId, $courseId]);
    $best = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $best[(int) $row['quiz_id']] = (float) $row['best'];
    }

    $stmt = $pdo->prepare(
        'SELECT p.lesson_id, p.status FROM lesson_progress p
         JOIN lessons l ON l.lesson_id = p.lesson_id
         WHERE p.student_id = ? AND l.course_id = ?'
    );
    $stmt->execute([$studentId, $courseId]);
    $progress = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $progress[(int) $row['lesson_id']] = $row['status'];
    }

    $earlierDone = true;
    $currentFound = false;
    foreach ($lessons as &$lesson) {
        $id = (int) $lesson['lesson_id'];
        $lesson['lesson_id'] = $id;
        $lesson['quizzes'] = [];
        $lesson['quiz_count'] = 0;
        foreach ($quizzesByLesson[$id] ?? [] as $q) {
            $q['best'] = $best[$q['quiz_id']] ?? null;
            $q['passed'] = $q['best'] !== null && $q['best'] >= PL_PASS_PERCENT;
            $lesson['quizzes'][] = $q;
            if ($q['questions'] > 0) {
                $lesson['quiz_count']++;
            }
        }
        $lesson['status'] = $progress[$id] ?? 'not_started';
        $done = $lesson['status'] === 'completed';
        $lesson['locked'] = PL_LOCK_FUTURE_LESSONS && !$earlierDone && !$done;
        $lesson['is_current'] = !$done && !$currentFound;
        if ($lesson['is_current']) {
            $currentFound = true;
        }
        $earlierDone = $earlierDone && $done;
    }
    unset($lesson);

    return $lessons;
}

/**
 * One lesson in the context of its course for this student, or null if it doesn't exist.
 *   lesson (the row from pl_course_lessons, plus content/course_id/course_title),
 *   lessons (the whole course), prev / next (lesson rows or null)
 */
function pl_lesson_context(PDO $pdo, int $lessonId, int $studentId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT l.lesson_id, l.course_id, l.content, c.title AS course_title
         FROM lessons l JOIN courses c ON c.course_id = l.course_id
         WHERE l.lesson_id = ?'
    );
    $stmt->execute([$lessonId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }
    $lessons = pl_course_lessons($pdo, (int) $row['course_id'], $studentId);
    foreach ($lessons as $i => $lesson) {
        if ($lesson['lesson_id'] === $lessonId) {
            return [
                'lesson'  => $lesson + [
                    'content'      => $row['content'],
                    'course_id'    => (int) $row['course_id'],
                    'course_title' => $row['course_title'],
                ],
                'lessons' => $lessons,
                'prev'    => $lessons[$i - 1] ?? null,
                'next'    => $lessons[$i + 1] ?? null,
            ];
        }
    }
    return null;
}

/** The lesson a quiz belongs to, or null. */
function pl_quiz_lesson_id(PDO $pdo, int $quizId): ?int
{
    $stmt = $pdo->prepare('SELECT lesson_id FROM quizzes WHERE quiz_id = ?');
    $stmt->execute([$quizId]);
    $id = $stmt->fetchColumn();
    return $id === false ? null : (int) $id;
}

/** True when the student may open this lesson (and its quiz and game). */
function pl_lesson_unlocked(PDO $pdo, int $lessonId, int $studentId): bool
{
    $ctx = pl_lesson_context($pdo, $lessonId, $studentId);
    return $ctx !== null && !$ctx['lesson']['locked'];
}

/** First lesson in the course the student has not completed, else the first lesson, else null. */
function pl_current_lesson(array $lessons): ?array
{
    foreach ($lessons as $lesson) {
        if ($lesson['is_current']) {
            return $lesson;
        }
    }
    return $lessons[0] ?? null;
}

/** 'Completed' / 'Continue' etc. for badges. */
function pl_status_label(array $lesson): string
{
    if ($lesson['locked']) {
        return 'Locked';
    }
    if ($lesson['status'] === 'completed') {
        return 'Completed';
    }
    return $lesson['status'] === 'in_progress' ? 'In progress' : 'Not started';
}

/** Marks a lesson started (never downgrades a completed lesson). */
function pl_touch_lesson(PDO $pdo, int $studentId, int $lessonId): void
{
    $stmt = $pdo->prepare('SELECT status FROM lesson_progress WHERE student_id = ? AND lesson_id = ?');
    $stmt->execute([$studentId, $lessonId]);
    $status = $stmt->fetchColumn();
    if ($status === false) {
        $pdo->prepare("INSERT INTO lesson_progress (student_id, lesson_id, status) VALUES (?, ?, 'in_progress')")
            ->execute([$studentId, $lessonId]);
    } elseif ($status === 'not_started') {
        $pdo->prepare("UPDATE lesson_progress SET status = 'in_progress' WHERE student_id = ? AND lesson_id = ?")
            ->execute([$studentId, $lessonId]);
    }
}

/** Marks a lesson completed (once; the original completion time is kept). Returns true if it was newly completed. */
function pl_complete_lesson(PDO $pdo, int $studentId, int $lessonId): bool
{
    $stmt = $pdo->prepare('SELECT status FROM lesson_progress WHERE student_id = ? AND lesson_id = ?');
    $stmt->execute([$studentId, $lessonId]);
    $status = $stmt->fetchColumn();
    if ($status === 'completed') {
        return false;
    }
    if ($status === false) {
        $pdo->prepare("INSERT INTO lesson_progress (student_id, lesson_id, status, completed_at)
                       VALUES (?, ?, 'completed', NOW())")->execute([$studentId, $lessonId]);
    } else {
        $pdo->prepare("UPDATE lesson_progress SET status = 'completed', completed_at = NOW()
                       WHERE student_id = ? AND lesson_id = ?")->execute([$studentId, $lessonId]);
    }
    return true;
}

/** True when the student has passed every quiz of the lesson that has questions (and there is at least one). */
function pl_lesson_quizzes_passed(PDO $pdo, int $lessonId, int $studentId): bool
{
    $stmt = $pdo->prepare(
        'SELECT q.quiz_id,
                (SELECT COUNT(*) FROM questions s WHERE s.quiz_id = q.quiz_id) AS questions,
                (SELECT MAX(a.score) FROM quiz_attempts a
                  WHERE a.quiz_id = q.quiz_id AND a.student_id = ?) AS best
         FROM quizzes q WHERE q.lesson_id = ?'
    );
    $stmt->execute([$studentId, $lessonId]);
    $playable = 0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if ((int) $row['questions'] === 0) {
            continue;
        }
        $playable++;
        if ($row['best'] === null || (float) $row['best'] < PL_PASS_PERCENT) {
            return false;
        }
    }
    return $playable > 0;
}

/** Best score the student has on a quiz, or null if never finished. */
function pl_best_score(PDO $pdo, int $studentId, int $quizId): ?float
{
    $stmt = $pdo->prepare('SELECT MAX(score) FROM quiz_attempts WHERE student_id = ? AND quiz_id = ? AND score IS NOT NULL');
    $stmt->execute([$studentId, $quizId]);
    $best = $stmt->fetchColumn();
    return ($best === false || $best === null) ? null : (float) $best;
}

/** Where "continue" goes after finishing a quiz: another unpassed quiz of the lesson, else the next lesson, else the syllabus. [url, label] */
function pl_after_quiz(array $ctx, int $quizId): array
{
    foreach ($ctx['lesson']['quizzes'] as $q) {
        if ($q['quiz_id'] !== $quizId && $q['questions'] > 0 && !$q['passed']) {
            return ['/student/quiz.php?quiz_id=' . $q['quiz_id'], 'Next quiz'];
        }
    }
    if ($ctx['next']) {
        return ['/student/lesson.php?id=' . $ctx['next']['lesson_id'], 'Next lesson'];
    }
    return ['/student/syllabus.php?course_id=' . $ctx['lesson']['course_id'], 'Finish course'];
}

/** The playable quizzes (with questions) of a lesson row from pl_course_lessons. */
function pl_playable_quizzes(array $lesson): array
{
    return array_values(array_filter($lesson['quizzes'], function ($q) {
        return $q['questions'] > 0;
    }));
}
