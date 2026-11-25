<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/progress.php';
require __DIR__ . '/../includes/games.php';
require_role('student');

$student_id = (int) $_SESSION['user_id'];
$lesson_id  = (int) ($_GET['id'] ?? 0);

$ctx = pl_lesson_context($pdo, $lesson_id, $student_id);
if (!$ctx) {
    http_response_code(404);
    die('Lesson not found.');
}
$lesson = $ctx['lesson'];
$syllabus_url = '/student/syllabus.php?course_id=' . $lesson['course_id'];

// Lessons open in order: finish the earlier ones first.
if ($lesson['locked']) {
    header('Location: ' . $syllabus_url . '&locked=1');
    exit;
}

$review = $lesson['status'] === 'completed';
if (!$review) {
    pl_touch_lesson($pdo, $student_id, $lesson_id);
}

$quizzes = pl_playable_quizzes($lesson);

$stmt = $pdo->prepare('SELECT game_type, game_data FROM lessons WHERE lesson_id = ?');
$stmt->execute([$lesson_id]);
$g = $stmt->fetch();
$has_game = pl_playable_game($g['game_type'] ?? null, $g['game_data'] ?? null) !== null;

$position = 1;
foreach ($ctx['lessons'] as $i => $l) {
    if ($l['lesson_id'] === $lesson_id) { $position = $i + 1; }
}

$page_title = $lesson['title'];
$active_nav = 'dashboard';

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<a class="small d-inline-block mb-3" href="<?= htmlspecialchars($syllabus_url) ?>">&larr; Syllabus</a>
<p class="text-muted small mb-1"><?= htmlspecialchars($lesson['course_title']) ?> &middot; Lesson <?= $position ?> of <?= count($ctx['lessons']) ?></p>
<h1 class="h3 mb-3"><?= htmlspecialchars($lesson['title']) ?></h1>

<?php if ($review): ?>
    <div class="pl-banner pl-banner--done" role="status">
        You've completed this lesson. Reading it again or practising its quiz won't change your progress or scores.
    </div>
<?php endif; ?>

<div class="card pl-card mb-4">
    <div class="card-body">
        <?= nl2br(htmlspecialchars($lesson['content'] ?? 'No content yet.')) ?>
    </div>
</div>

<section class="pl-panel mb-4" id="lesson-next-step">
    <?php if ($quizzes): ?>
        <p class="pl-eyebrow"><?= $review ? 'Quiz' : 'Next step: the quiz' ?></p>
        <?php if (!$review): ?>
            <p class="mb-3">Score <?= PL_PASS_PERCENT ?>% or more to complete this lesson and open the next one.</p>
        <?php endif; ?>
        <ul class="pl-quiz-list">
            <?php foreach ($quizzes as $q): ?>
                <li>
                    <span>
                        <?= htmlspecialchars($q['title']) ?>
                        <span class="pl-quiz-lesson">
                            <?= $q['best'] !== null ? 'Best score ' . (int) round($q['best']) . '%' . ($q['passed'] ? ' · passed' : '') : 'Not taken yet' ?>
                        </span>
                    </span>
                    <a class="btn <?= $q['passed'] ? 'btn-outline-secondary' : 'btn-pl-primary' ?> btn-sm"
                       href="/student/quiz.php?quiz_id=<?= (int) $q['quiz_id'] ?>"><?= $q['passed'] ? 'Practice' : ($q['best'] !== null ? 'Try again' : 'Take the quiz') ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif (!$review): ?>
        <p class="pl-eyebrow">Next step</p>
        <p class="mb-3">This lesson has no quiz. When you've read it, mark it complete to move on.</p>
        <button type="button" class="btn btn-pl-primary" id="complete-lesson" data-lesson-id="<?= $lesson_id ?>">Mark lesson complete</button>
        <p class="pl-save-status mt-2" id="complete-status" role="status" aria-live="polite"></p>
    <?php endif; ?>

    <?php if ($review): ?>
        <div class="d-flex flex-wrap gap-2 <?= $quizzes ? 'mt-3' : '' ?>">
            <?php if ($ctx['next']): ?>
                <a class="btn btn-pl-primary" href="/student/lesson.php?id=<?= (int) $ctx['next']['lesson_id'] ?>">Next lesson: <?= htmlspecialchars($ctx['next']['title']) ?> &rarr;</a>
            <?php else: ?>
                <span class="pl-status pl-status--done">You've finished this course</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php if ($has_game): ?>
    <section class="pl-panel mb-4">
        <p class="pl-eyebrow">Practice game</p>
        <p class="mb-3">Optional: play this lesson's game for extra practice.</p>
        <a class="btn btn-outline-secondary btn-sm" href="/student/game.php?type=<?= urlencode((string) $g['game_type']) ?>&amp;lesson_id=<?= $lesson_id ?>">Play the game</a>
    </section>
<?php endif; ?>

<div class="d-flex justify-content-between">
    <?php if ($ctx['prev']): ?>
        <a href="/student/lesson.php?id=<?= (int) $ctx['prev']['lesson_id'] ?>">&larr; <?= htmlspecialchars($ctx['prev']['title']) ?></a>
    <?php else: ?><span></span><?php endif; ?>
    <?php if ($ctx['next'] && !$ctx['next']['locked']): ?>
        <a href="/student/lesson.php?id=<?= (int) $ctx['next']['lesson_id'] ?>"><?= htmlspecialchars($ctx['next']['title']) ?> &rarr;</a>
    <?php endif; ?>
</div>

<?php if (!$quizzes && !$review): ?>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="/assets/js/pl-api.js"></script>
<script src="/assets/js/lesson.js"></script>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
