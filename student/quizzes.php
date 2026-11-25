<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/progress.php';
require_role('student');

$page_title = 'Quizzes';
$active_nav = 'quizzes';
$student_id = (int) $_SESSION['user_id'];

// Every quiz, grouped by course, in teaching order, with this student's status.
$courses = [];
foreach ($pdo->query('SELECT course_id, title FROM courses ORDER BY course_id')->fetchAll() as $c) {
    $quizzes = [];
    foreach (pl_course_lessons($pdo, (int) $c['course_id'], $student_id) as $lesson) {
        foreach (pl_playable_quizzes($lesson) as $q) {
            $quizzes[] = $q + ['lesson_title' => $lesson['title'], 'locked' => $lesson['locked']];
        }
    }
    if ($quizzes) {
        $courses[] = ['title' => $c['title'], 'quizzes' => $quizzes];
    }
}

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<div class="pl-narrow">
    <h1 class="pl-page-title mb-1">Quizzes</h1>
    <p class="pl-page-sub mb-4">Pass a lesson's quiz (<?= PL_PASS_PERCENT ?>% or more) to open the next lesson. Quizzes you've passed can be practised again without changing your scores.</p>

    <?php foreach ($courses as $course): ?>
        <section class="pl-panel mb-3">
            <p class="pl-eyebrow"><?= htmlspecialchars($course['title']) ?></p>
            <ul class="pl-quiz-list">
                <?php foreach ($course['quizzes'] as $quiz): ?>
                    <li>
                        <span>
                            <?= htmlspecialchars($quiz['title']) ?>
                            <span class="pl-quiz-lesson">
                                Lesson: <?= htmlspecialchars($quiz['lesson_title']) ?>
                                <?php if ($quiz['locked']): ?>&middot; locked
                                <?php elseif ($quiz['best'] !== null): ?>&middot; best <?= (int) round($quiz['best']) ?>%<?= $quiz['passed'] ? ' (passed)' : '' ?>
                                <?php endif; ?>
                            </span>
                        </span>
                        <?php if ($quiz['locked']): ?>
                            <span class="pl-status pl-status--locked">Locked</span>
                        <?php else: ?>
                            <a class="btn <?= $quiz['passed'] ? 'btn-outline-secondary' : 'btn-pl-primary' ?> btn-sm"
                               href="/student/quiz.php?quiz_id=<?= (int) $quiz['quiz_id'] ?>"><?= $quiz['passed'] ? 'Practice' : ($quiz['best'] !== null ? 'Try again' : 'Start') ?></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>

    <?php if (empty($courses)): ?>
        <div class="pl-panel pl-state"><p class="mb-0">No quizzes are available yet.</p></div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
