<?php
require __DIR__ . '/../includes/auth.php';
require_role('student');
$page_title = 'Quizzes';
$active_nav = 'quizzes';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Quizzes</h1>
<p class="text-muted">Quiz-taking UI lands in week 5, once questions/answer_options exist and the game/quiz pages are built.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
