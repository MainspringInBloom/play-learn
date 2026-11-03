<?php
require __DIR__ . '/../includes/auth.php';
require_role('student');

$page_title = 'Games';
$active_nav = 'games';

// Proof-of-concept games. They run on sample data until games are connected to
// lessons (lessons.game_type / lessons.game_data) in a later step.
$games = [
    [
        'type'  => 'match_output',
        'title' => 'Match the Output',
        'blurb' => 'Pick a code snippet, then pick the output it prints. Find all the pairs.',
    ],
    [
        'type'  => 'memory',
        'title' => 'Memory Match',
        'blurb' => 'Flip cards to match each programming term with its meaning.',
    ],
];

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<h1 class="pl-page-title mb-1">Games</h1>
<p class="pl-page-sub mb-4">Practice what you've learned. Every match scores points.</p>

<div class="pl-card-grid">
    <?php foreach ($games as $game): ?>
        <section class="pl-list-card">
            <h2><?= htmlspecialchars($game['title']) ?></h2>
            <p><?= htmlspecialchars($game['blurb']) ?></p>
            <div>
                <a class="btn btn-pl-primary btn-sm"
                   href="/student/game.php?type=<?= htmlspecialchars($game['type']) ?>">Play</a>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
