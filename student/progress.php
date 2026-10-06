<?php
require __DIR__ . '/../includes/auth.php';
require_role('student');
$page_title = 'Progress';
$active_nav = 'progress';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Progress</h1>
<p class="text-muted">Progress charts land in week 6, once the progress tracker (scores + time spent) is built.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
