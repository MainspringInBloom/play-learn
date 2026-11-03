<?php
require __DIR__ . '/../includes/auth.php';
require_role('student');

// The games this page can run. Each must have an engine in assets/js/game-*.js.
$games = [
    'match_output' => 'Match the Output',
    'memory'       => 'Memory Match',
];

$type = (string) ($_GET['type'] ?? '');
if (!isset($games[$type])) {
    header('Location: /student/games.php');
    exit;
}

// 0 = no lesson attached (sample mode). Real games pass the lesson they belong to.
$lesson_id = max(0, (int) ($_GET['lesson_id'] ?? 0));

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

    <span class="pl-sample-badge" id="game-sample" hidden>Sample data &middot; API not connected</span>

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
<script src="/assets/js/pl-sample-data.js"></script>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/pl-util.js"></script>
<script src="/assets/js/game-runner.js"></script>
<script src="/assets/js/game-match-output.js"></script>
<script src="/assets/js/game-memory.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
