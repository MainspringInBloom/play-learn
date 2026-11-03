<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('student');

$page_title = 'Quizzes';
$active_nav = 'quizzes';

// Every quiz, grouped by course, in teaching order.
$rows = $pdo->query('
    SELECT c.course_id, c.title AS course_title,
           l.lesson_id, l.title AS lesson_title,
           q.quiz_id, q.title AS quiz_title
    FROM quizzes q
    JOIN lessons l ON l.lesson_id = q.lesson_id
    JOIN courses c ON c.course_id = l.course_id
    ORDER BY c.course_id, l.sort_order, l.lesson_id, q.quiz_id
')->fetchAll();

$courses = [];
foreach ($rows as $row) {
    $cid = (int) $row['course_id'];
    if (!isset($courses[$cid])) {
        $courses[$cid] = ['title' => $row['course_title'], 'quizzes' => []];
    }
    $courses[$cid]['quizzes'][] = $row;
}

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<div class="pl-narrow">
    <h1 class="pl-page-title mb-1">Quizzes</h1>
    <p class="pl-page-sub mb-4">Pick a quiz to test what you've learned. You get feedback after every question.</p>

    <?php foreach ($courses as $course): ?>
        <section class="pl-panel mb-3">
            <p class="pl-eyebrow"><?= htmlspecialchars($course['title']) ?></p>
            <ul class="pl-quiz-list">
                <?php foreach ($course['quizzes'] as $quiz): ?>
                    <li>
                        <span>
                            <?= htmlspecialchars($quiz['quiz_title']) ?>
                            <span class="pl-quiz-lesson">Lesson: <?= htmlspecialchars($quiz['lesson_title']) ?></span>
                        </span>
                        <a class="btn btn-pl-primary btn-sm"
                           href="/student/quiz.php?quiz_id=<?= (int) $quiz['quiz_id'] ?>">Start</a>
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
