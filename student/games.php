<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('student');

$page_title = 'Games';
$active_nav = 'games';

$labels = [
    'match_output' => ['Match the Output', 'Pick a code snippet, then pick the output it prints. Find all the pairs.'],
    'memory'       => ['Memory Match', 'Flip cards to match each programming term with its meaning.'],
];

// Every lesson that has a game, grouped by course, in teaching order.
$rows = $pdo->query('
    SELECT c.course_id, c.title AS course_title,
           l.lesson_id, l.title AS lesson_title, l.game_type
    FROM lessons l
    JOIN courses c ON c.course_id = l.course_id
    WHERE l.game_type IS NOT NULL AND l.game_type <> \'\'
    ORDER BY c.course_id, l.sort_order, l.lesson_id
')->fetchAll();

$courses = [];
foreach ($rows as $row) {
    if (!isset($labels[$row['game_type']])) {
        continue; // a game type we have no engine for yet
    }
    $cid = (int) $row['course_id'];
    if (!isset($courses[$cid])) {
        $courses[$cid] = ['title' => $row['course_title'], 'games' => []];
    }
    $courses[$cid]['games'][] = $row;
}

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<h1 class="pl-page-title mb-1">Games</h1>
<p class="pl-page-sub mb-4">Practice what you've learned. Every match scores points.</p>

<?php foreach ($courses as $course): ?>
    <h2 class="h5 mt-4 mb-3"><?= htmlspecialchars($course['title']) ?></h2>
    <div class="pl-card-grid">
        <?php foreach ($course['games'] as $game): ?>
            <?php [$label, $blurb] = $labels[$game['game_type']]; ?>
            <section class="pl-list-card">
                <p class="pl-quiz-lesson mb-1">Lesson: <?= htmlspecialchars($game['lesson_title']) ?></p>
                <h2><?= htmlspecialchars($label) ?></h2>
                <p><?= htmlspecialchars($blurb) ?></p>
                <div>
                    <a class="btn btn-pl-primary btn-sm"
                       href="/student/game.php?type=<?= urlencode($game['game_type']) ?>&amp;lesson_id=<?= (int) $game['lesson_id'] ?>">Play</a>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<?php if (empty($courses)): ?>
    <div class="pl-panel pl-state"><p class="mb-0">No games are available yet.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
