<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/progress.php';
require_role('student');

$student_id = (int) $_SESSION['user_id'];
$quiz_id = (int) ($_GET['quiz_id'] ?? 0);
if ($quiz_id < 1) {
    header('Location: /student/quizzes.php');
    exit;
}

$lesson_id = pl_quiz_lesson_id($pdo, $quiz_id);
$ctx = $lesson_id === null ? null : pl_lesson_context($pdo, $lesson_id, $student_id);
if (!$ctx) {
    http_response_code(404);
    die('Quiz not found.');
}
$lesson = $ctx['lesson'];
$syllabus_url = '/student/syllabus.php?course_id=' . $lesson['course_id'];
if ($lesson['locked']) {
    header('Location: ' . $syllabus_url . '&locked=1');
    exit;
}

[$next_url, $next_label] = pl_after_quiz($ctx, $quiz_id);
$lesson_url = '/student/lesson.php?id=' . $lesson['lesson_id'];
$already_passed = false;
foreach ($lesson['quizzes'] as $q) {
    if ($q['quiz_id'] === $quiz_id) { $already_passed = $q['passed']; }
}

$page_title = 'Quiz';
$active_nav = 'quizzes';

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<div class="pl-narrow" id="quiz-app" data-quiz-id="<?= $quiz_id ?>"
     data-back-url="/student/quizzes.php"
     data-lesson-url="<?= htmlspecialchars($lesson_url) ?>"
     data-syllabus-url="<?= htmlspecialchars($syllabus_url) ?>"
     data-next-url="<?= htmlspecialchars($next_url) ?>"
     data-next-label="<?= htmlspecialchars($next_label) ?>"
     data-passed="<?= $already_passed ? '1' : '0' ?>">
    <a class="pl-back" href="<?= htmlspecialchars($lesson_url) ?>">&larr; Back to the lesson: <?= htmlspecialchars($lesson['title']) ?></a>

    <div class="pl-page-head">
        <h1 class="pl-page-title" id="quiz-title">Loading quiz&hellip;</h1>
        <span class="pl-page-meta" id="quiz-count" aria-live="polite"></span>
    </div>

    <div class="pl-banner pl-banner--done" id="quiz-practice" hidden role="status">
        Practice mode: you've already passed this quiz, so this run is graded but nothing is saved.
    </div>

    <div class="pl-progress" id="quiz-progress" role="progressbar" aria-label="Quiz progress"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <div class="pl-progress-bar"></div>
    </div>

    <div id="quiz-stage">
        <noscript><p class="pl-state">This quiz needs JavaScript to run. Please turn it on and reload.</p></noscript>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/quiz.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
