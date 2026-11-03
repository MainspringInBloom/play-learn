<?php
require __DIR__ . '/../includes/auth.php';
require_role('student');

$page_title = 'Progress';
$active_nav = 'progress';

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">
<link rel="stylesheet" href="/assets/css/progress.css">

<div id="progress-app">
    <div class="pl-page-head">
        <div>
            <h1 class="pl-page-title">Progress</h1>
            <p class="pl-page-sub">How you're doing across lessons, quizzes and games.</p>
        </div>
    </div>

    <span class="pl-sample-badge" id="progress-sample" hidden>Sample data &middot; API not connected</span>

    <div class="pl-stats" id="progress-tiles" aria-label="Summary"></div>

    <p class="pl-empty-banner" id="progress-empty" hidden>
        Nothing here yet. Finish a lesson, quiz or game and your charts will fill in.
    </p>

    <div class="pl-progress-grid" id="progress-charts" aria-busy="true">
        <section class="pl-panel pl-panel--wide">
            <h2>Quiz scores</h2>
            <p class="pl-panel-sub">Your result on each quiz you finished, oldest to newest.</p>
            <div id="chart-quiz"><p class="pl-state">Loading&hellip;</p></div>
        </section>

        <section class="pl-panel">
            <h2>Activity, last 14 days</h2>
            <p class="pl-panel-sub">Lessons, quizzes and games finished each day.</p>
            <div id="chart-activity"><p class="pl-state">Loading&hellip;</p></div>
            <ul class="pl-legend" aria-hidden="true">
                <li><i class="k-lessons"></i>Lessons</li>
                <li><i class="k-quizzes"></i>Quizzes</li>
                <li><i class="k-games"></i>Games</li>
            </ul>
        </section>

        <section class="pl-panel">
            <h2>Course progress</h2>
            <p class="pl-panel-sub">Lessons completed in each course.</p>
            <div id="chart-courses"><p class="pl-state">Loading&hellip;</p></div>
        </section>

        <section class="pl-panel">
            <h2>Recent games</h2>
            <p class="pl-panel-sub">Points earned out of the best possible.</p>
            <div id="chart-games"><p class="pl-state">Loading&hellip;</p></div>
        </section>

        <section class="pl-panel" aria-labelledby="feedback-title">
            <div class="pl-panel-head">
                <h2 id="feedback-title">Messages</h2>
                <span class="pl-unread" id="feedback-unread" hidden></span>
            </div>
            <p class="pl-panel-sub">Notes from your teachers and parents.</p>
            <div id="feedback-list" aria-live="polite"><p class="pl-state">Loading&hellip;</p></div>
        </section>
    </div>

    <noscript><p class="pl-state">The progress page needs JavaScript. Please turn it on and reload.</p></noscript>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-sample-progress.js"></script>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/pl-charts.js"></script>
<script src="/assets/js/progress.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
