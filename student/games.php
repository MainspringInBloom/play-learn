<?php
require __DIR__ . '/../includes/auth.php';
require_role('student');
$page_title = 'Games';
$active_nav = 'games';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Games</h1>
<p class="text-muted">The matching game (Match the Output) is built in week 5.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
