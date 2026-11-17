<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/viewer.php';
require_role('student');

$page_title = 'Progress';
$active_nav = 'progress';

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">
<link rel="stylesheet" href="/assets/css/progress.css">
<link rel="stylesheet" href="/assets/css/people.css">

<div id="progress-app">
    <div class="pl-page-head">
        <div>
            <h1 class="pl-page-title">Progress</h1>
            <p class="pl-page-sub">How you're doing across lessons, quizzes and games.</p>
        </div>
    </div>

    <span class="pl-sample-badge" id="progress-sample" hidden>Sample data &middot; API not connected</span>

    <div id="progress-view"></div>

    <!-- The charts view moves this panel into its grid as the last panel. -->
    <section class="pl-panel" id="feedback-panel" aria-labelledby="feedback-title">
        <div class="pl-panel-head">
            <h2 id="feedback-title">Messages</h2>
            <span class="pl-unread" id="feedback-unread" hidden></span>
        </div>
        <p class="pl-panel-sub">Notes from your teachers and parents.</p>
        <div id="feedback-list" aria-live="polite"><p class="pl-state">Loading&hellip;</p></div>
    </section>

    <noscript><p class="pl-state">The progress page needs JavaScript. Please turn it on and reload.</p></noscript>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-sample-progress.js"></script>
<?php render_viewer_script(); ?>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/pl-format.js"></script>
<script src="/assets/js/pl-charts.js"></script>
<script src="/assets/js/progress-view.js"></script>
<script src="/assets/js/progress.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
