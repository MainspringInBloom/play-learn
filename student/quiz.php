<?php
require __DIR__ . '/../includes/auth.php';
require_role('student');

$quiz_id = (int) ($_GET['quiz_id'] ?? 0);
if ($quiz_id < 1) {
    header('Location: /student/quizzes.php');
    exit;
}

$page_title = 'Quiz';
$active_nav = 'quizzes';

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<div class="pl-narrow" id="quiz-app" data-quiz-id="<?= $quiz_id ?>" data-back-url="/student/quizzes.php">
    <a class="pl-back" href="/student/quizzes.php">&larr; Back to quizzes</a>

    <div class="pl-page-head">
        <h1 class="pl-page-title" id="quiz-title">Loading quiz&hellip;</h1>
        <span class="pl-page-meta" id="quiz-count" aria-live="polite"></span>
    </div>

    <span class="pl-sample-badge" id="quiz-sample" hidden>Sample data &middot; API not connected</span>

    <div class="pl-progress" id="quiz-progress" role="progressbar" aria-label="Quiz progress"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <div class="pl-progress-bar"></div>
    </div>

    <div id="quiz-stage">
        <noscript><p class="pl-state">This quiz needs JavaScript to run. Please turn it on and reload.</p></noscript>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-sample-data.js"></script>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/quiz.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
