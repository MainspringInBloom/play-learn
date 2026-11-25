<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/progress.php';
require __DIR__ . '/../includes/scores.php';
require_role('student');

$page_title = 'Dashboard';
$active_nav = 'dashboard';

$student_id = (int) $_SESSION['user_id'];

// There is no enrollment table yet, so every course is offered through the course switcher.
$courses = $pdo->query('SELECT course_id, title FROM courses ORDER BY course_id')->fetchAll();
$selected_id = (int) ($_GET['course_id'] ?? 0);
$course = null;
foreach ($courses as $c) {
    if ((int) $c['course_id'] === $selected_id) { $course = $c; }
}
$course = $course ?? ($courses[0] ?? null);

$lessons = $course ? pl_course_lessons($pdo, (int) $course['course_id'], $student_id) : [];
$total_lessons = count($lessons);
$completed_here = count(array_filter($lessons, function ($l) { return $l['status'] === 'completed'; }));
$current_lesson = pl_current_lesson($lessons);
$course_done = $total_lessons > 0 && $completed_here === $total_lessons;
$current_index = 1;
foreach ($lessons as $i => $l) {
    if ($current_lesson && $l['lesson_id'] === $current_lesson['lesson_id']) { $current_index = $i + 1; }
}

$summary = pl_summary($pdo, $student_id);

// Recent activity: the latest quiz attempts, games and completed lessons, newest first.
$activity = [];
$stmt = $pdo->prepare('SELECT q.title, a.score, a.finished_at AS at FROM quiz_attempts a JOIN quizzes q ON q.quiz_id = a.quiz_id
                       WHERE a.student_id = ? AND a.score IS NOT NULL ORDER BY a.finished_at DESC, a.attempt_id DESC LIMIT 5');
$stmt->execute([$student_id]);
foreach ($stmt->fetchAll() as $r) {
    $activity[] = ['at' => $r['at'], 'text' => 'Scored ' . (int) round((float) $r['score']) . '% on ' . $r['title']];
}
$stmt = $pdo->prepare('SELECT l.title, g.score, g.max_score, g.completed_at AS at FROM game_attempts g JOIN lessons l ON l.lesson_id = g.lesson_id
                       WHERE g.student_id = ? ORDER BY g.completed_at DESC, g.game_attempt_id DESC LIMIT 5');
$stmt->execute([$student_id]);
foreach ($stmt->fetchAll() as $r) {
    $activity[] = ['at' => $r['at'], 'text' => 'Played the ' . $r['title'] . ' game (' . (int) $r['score'] . '/' . (int) $r['max_score'] . ' points)'];
}
$stmt = $pdo->prepare("SELECT l.title, p.completed_at AS at FROM lesson_progress p JOIN lessons l ON l.lesson_id = p.lesson_id
                       WHERE p.student_id = ? AND p.status = 'completed' ORDER BY p.completed_at DESC LIMIT 5");
$stmt->execute([$student_id]);
foreach ($stmt->fetchAll() as $r) {
    $activity[] = ['at' => $r['at'], 'text' => 'Completed the lesson ' . $r['title']];
}
usort($activity, function ($a, $b) { return strcmp((string) $b['at'], (string) $a['at']); });
$activity = array_slice($activity, 0, 6);

require __DIR__ . '/../includes/header.php';
?>
<div class="pl-welcome mb-4">
    <h1 class="h3 mb-1">Welcome back, <?= htmlspecialchars(current_user()['first_name'] ?? 'Student') ?></h1>
    <?php if ($course): ?>
        <p class="text-muted mb-0">Pick up where you left off in <?= htmlspecialchars($course['title']) ?>.</p>
    <?php endif; ?>
</div>
<div class="mb-3">
    <?php foreach ($courses as $c): ?>
        <a class="btn btn-sm <?= $course && (int) $c['course_id'] === (int) $course['course_id'] ? 'btn-pl-primary' : 'btn-outline-secondary' ?>"
           href="/student/dashboard.php?course_id=<?= (int) $c['course_id'] ?>">
            <?= htmlspecialchars($c['title']) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($course): ?>
<div class="card pl-card mb-4">
    <div class="card-body">
        <p class="text-uppercase small text-muted mb-1">Course</p>
        <h2 class="h5"><?= htmlspecialchars($course['title']) ?></h2>

        <?php if ($current_lesson): ?>
            <?php if ($course_done): ?>
                <p class="mb-3">You've completed all <?= $total_lessons ?> lessons. Nice work! You can revisit any of them from the syllabus.</p>
            <?php else: ?>
                <p class="mb-3">
                    Lesson <?= $current_index ?> of <?= $total_lessons ?> &mdash;
                    <?= htmlspecialchars($current_lesson['title']) ?>
                    <span class="text-muted small">(<?= $completed_here ?> complete)</span>
                </p>
                <a class="btn btn-pl-primary btn-sm" href="/student/lesson.php?id=<?= (int) $current_lesson['lesson_id'] ?>">
                    <?= $current_lesson['status'] === 'not_started' ? 'Start lesson' : 'Continue lesson' ?>
                </a>
            <?php endif; ?>
            <a class="btn btn-outline-secondary btn-sm" href="/student/syllabus.php?course_id=<?= (int) $course['course_id'] ?>">
                View syllabus
            </a>
        <?php else: ?>
            <p class="text-muted mb-0">No lessons loaded for this course yet.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-12">
        <p class="text-uppercase small text-muted mb-2">Your totals</p>
    </div>
    <div class="col-md-4">
        <div class="card pl-card text-center">
            <div class="card-body">
                <p class="display-6 mb-0"><?= $summary['lessons_completed'] ?><span class="fs-5 text-muted"> / <?= $summary['total_lessons'] ?></span></p>
                <p class="text-muted small mb-0">Lessons completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card pl-card text-center">
            <div class="card-body">
                <p class="display-6 mb-0"><?= $summary['quiz_average'] === null ? '—' : (int) round($summary['quiz_average']) . '%' ?></p>
                <p class="text-muted small mb-0">Quiz average</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card pl-card text-center">
            <div class="card-body">
                <p class="display-6 mb-0"><?= $summary['games_played'] ?></p>
                <p class="text-muted small mb-0">Games played</p>
            </div>
        </div>
    </div>
</div>

<div>
    <p class="text-uppercase small text-muted mb-2">Recent activity</p>
    <div class="card pl-card">
        <div class="card-body">
            <?php if ($activity): ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($activity as $a): ?>
                        <li class="d-flex justify-content-between gap-3 py-1">
                            <span><?= htmlspecialchars($a['text']) ?></span>
                            <span class="text-muted small text-nowrap"><?= htmlspecialchars(date('M j', strtotime($a['at']))) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0">Nothing yet. Start your first lesson and it will show up here.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
