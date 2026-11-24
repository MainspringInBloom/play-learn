<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/viewer.php';
require_role('admin');

$page_title = 'Family links';
$active_nav = 'links';

require __DIR__ . '/../includes/admin_header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">
<link rel="stylesheet" href="/assets/css/progress.css">
<link rel="stylesheet" href="/assets/css/people.css">

<div id="admin-app">
    <h1 class="h3 mb-1">Family links</h1>
    <p class="pl-muted">Choose which students each parent can see and message. A student can have more than one parent.</p>

    <span class="pl-sample-badge" id="admin-sample" hidden>Sample data &middot; API not connected</span>

    <p class="pl-status" id="links-status" role="status"></p>

    <div id="links-state"><p class="pl-state">Loading&hellip;</p></div>

    <div id="links-body" hidden>
        <div class="pl-links-layout">
            <section aria-labelledby="parents-title">
                <h2 class="h6 text-uppercase pl-muted" id="parents-title">Parents</h2>
                <div class="pl-parent-list" id="parent-list" role="group" aria-labelledby="parents-title"></div>
            </section>
            <section class="pl-panel" id="parent-detail" aria-live="polite"></section>
        </div>
        <section class="pl-panel mt-3" id="orphans" aria-label="Students without a parent"></section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-sample-people.js"></script>
<?php render_viewer_script(); ?>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/pl-format.js"></script>
<script src="/assets/js/admin-links.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
