<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/progress.php';
require __DIR__ . '/../includes/games.php';
require_role('student');

$page_title = 'Games';
$active_nav = 'games';
$student_id = (int) $_SESSION['user_id'];

// Each lesson can have one game (set by an admin). Games open with their lesson.
$data = $pdo->query('SELECT lesson_id, game_type, game_data FROM lessons WHERE game_type IS NOT NULL')->fetchAll();
$playable = [];
foreach ($data as $row) {
    if (pl_playable_game($row['game_type'], $row['game_data']) !== null) {
        $playable[(int) $row['lesson_id']] = $row['game_type'];
    }
}

$courses = [];
foreach ($pdo->query('SELECT course_id, title FROM courses ORDER BY course_id')->fetchAll() as $c) {
    $games = [];
    foreach (pl_course_lessons($pdo, (int) $c['course_id'], $student_id) as $lesson) {
        if (isset($playable[$lesson['lesson_id']])) {
            $games[] = ['lesson' => $lesson, 'type' => $playable[$lesson['lesson_id']]];
        }
    }
    if ($games) {
        $courses[] = ['title' => $c['title'], 'games' => $games];
    }
}

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<h1 class="pl-page-title mb-1">Games</h1>
<p class="pl-page-sub mb-4">Practice what you've learned. Every match scores points. A game opens once its lesson is unlocked.</p>

<?php foreach ($courses as $course): ?>
    <p class="pl-eyebrow"><?= htmlspecialchars($course['title']) ?></p>
    <div class="pl-card-grid mb-4">
        <?php foreach ($course['games'] as $g): ?>
            <section class="pl-list-card">
                <h2><?= htmlspecialchars(PL_GAME_TYPES[$g['type']]['label']) ?></h2>
                <p><?= htmlspecialchars($g['lesson']['title']) ?></p>
                <div>
                    <?php if ($g['lesson']['locked']): ?>
                        <span class="pl-status pl-status--locked">Locked</span>
                    <?php else: ?>
                        <a class="btn btn-pl-primary btn-sm" href="/student/game.php?type=<?= urlencode($g['type']) ?>&amp;lesson_id=<?= (int) $g['lesson']['lesson_id'] ?>">Play</a>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<?php if (empty($courses)): ?>
    <div class="pl-panel pl-state"><p class="mb-0">No games have been added yet. Check back soon.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
