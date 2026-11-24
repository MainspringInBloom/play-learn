<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('student');

$lesson_id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT l.lesson_id, l.title, l.content, l.game_type, l.course_id, c.title AS course_title
    FROM lessons l
    JOIN courses c ON c.course_id = l.course_id
    WHERE l.lesson_id = ?
');
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();
if (!$lesson) {
    http_response_code(404);
    die('Lesson not found.');
}

// This lesson's quiz (if any) and game (if any).
$qs = $pdo->prepare('SELECT quiz_id FROM quizzes WHERE lesson_id = ? ORDER BY quiz_id LIMIT 1');
$qs->execute([$lesson_id]);
$quiz_id = (int) ($qs->fetchColumn() ?: 0);
$game_types = ['match_output', 'memory'];
$has_game = in_array($lesson['game_type'], $game_types, true);

$page_title = $lesson['title'];
$active_nav = 'dashboard';

require __DIR__ . '/../includes/header.php';
?>
<a class="small d-inline-block mb-3" href="/student/dashboard.php">&larr; Back to dashboard</a>
<p class="text-muted small mb-1"><?= htmlspecialchars($lesson['course_title']) ?></p>
<h1 class="h3 mb-3"><?= htmlspecialchars($lesson['title']) ?></h1>

<div class="card pl-card">
    <div class="card-body">
        <?= nl2br(htmlspecialchars($lesson['content'] ?? 'No content yet.')) ?>
    </div>
</div>

<?php if ($quiz_id || $has_game): ?>
    <div class="d-flex flex-wrap gap-2 mt-3">
        <?php if ($has_game): ?>
            <a class="btn btn-pl-primary"
               href="/student/game.php?type=<?= urlencode($lesson['game_type']) ?>&amp;lesson_id=<?= (int) $lesson['lesson_id'] ?>">Play the game</a>
        <?php endif; ?>
        <?php if ($quiz_id): ?>
            <a class="btn btn-pl-primary" href="/student/quiz.php?quiz_id=<?= $quiz_id ?>">Take the quiz</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
