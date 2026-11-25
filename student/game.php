<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/progress.php';
require __DIR__ . '/../includes/games.php';
require_role('student');

// A game belongs to a lesson: /student/game.php?lesson_id=N. The type comes from the lesson.
$lesson_id = (int) ($_GET['lesson_id'] ?? 0);
$stmt = $pdo->prepare('SELECT l.lesson_id, l.title, l.game_type, l.game_data FROM lessons l WHERE l.lesson_id = ?');
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();
$type = $lesson['game_type'] ?? '';
if (!$lesson || !isset(PL_GAME_TYPES[$type]) || pl_playable_game($type, $lesson['game_data']) === null) {
    header('Location: /student/games.php');
    exit;
}
if (!pl_lesson_unlocked($pdo, $lesson_id, (int) $_SESSION['user_id'])) {
    header('Location: /student/games.php');
    exit;
}
$wanted = (string) ($_GET['type'] ?? '');
if ($wanted !== '' && $wanted !== $type) {
    header('Location: /student/games.php');
    exit;
}
$games = [$type => PL_GAME_TYPES[$type]['label']];

$page_title = $games[$type];
$active_nav = 'games';

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<div id="game-app" data-game-type="<?= htmlspecialchars($type) ?>" data-lesson-id="<?= $lesson_id ?>"
     data-back-url="/student/games.php">
    <a class="pl-back" href="/student/games.php">&larr; Back to games</a>

    <div class="pl-page-head">
        <div>
            <h1 class="pl-page-title" id="game-title"><?= htmlspecialchars($games[$type]) ?></h1>
            <p class="pl-page-sub" id="game-sub"></p>
        </div>
        <div class="pl-stats">
            <div class="pl-stat">
                <span class="pl-stat-label">Score</span>
                <span class="pl-stat-value" id="game-score">0</span>
            </div>
            <div class="pl-stat">
                <span class="pl-stat-label">Matched</span>
                <span class="pl-stat-value" id="game-matched">0/0</span>
            </div>
        </div>
    </div>


    <div id="game-stage">
        <noscript><p class="pl-state">This game needs JavaScript to run. Please turn it on and reload.</p></noscript>
    </div>

    <div class="pl-actionbar pl-game-actionbar" id="game-actionbar" hidden>
        <p class="pl-feedback" id="game-status" role="status" aria-live="polite"></p>
        <div class="pl-actions">
            <button type="button" class="btn btn-outline-secondary" id="game-reset">Reset</button>
            <button type="button" class="btn btn-pl-primary" id="game-finish">Finish game</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/game-runner.js"></script>
<script src="/assets/js/game-match-output.js"></script>
<script src="/assets/js/game-memory.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
