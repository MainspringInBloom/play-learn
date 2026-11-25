<?php
require __DIR__ . '/../includes/auth.php';
require_role('parent');

$page_title = 'Dashboard';

require __DIR__ . '/../includes/parent_header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">
<link rel="stylesheet" href="/assets/css/progress.css">
<link rel="stylesheet" href="/assets/css/people.css">

<div id="parent-app" class="container py-4">
    <div class="pl-page-head">
        <div>
            <h1 class="pl-page-title">Your children</h1>
            <p class="pl-page-sub">Check progress and quiz and game scores, and send a note of encouragement.</p>
        </div>
    </div>


    <div class="pl-children" id="children" role="group" aria-label="Your children"></div>

    <section id="child-detail" hidden aria-labelledby="child-heading">
        <h2 class="pl-child-heading" id="child-heading"></h2>
        <div id="progress-view"></div>
    </section>

    <noscript><p class="pl-state">This page needs JavaScript. Please turn it on and reload.</p></noscript>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/pl-format.js"></script>
<script src="/assets/js/pl-charts.js"></script>
<script src="/assets/js/progress-view.js"></script>
<script src="/assets/js/feedback-panel.js"></script>
<script src="/assets/js/parent.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
