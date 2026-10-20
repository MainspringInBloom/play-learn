<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('student');

$page_title = 'Dashboard';
$active_nav = 'dashboard';


// Placeholder: Enrollment tables not yet implemented, currently displays all available courses via a course switcher
$courses = $pdo->query('SELECT course_id, title FROM courses ORDER BY course_id')->fetchAll();
$selected_id = (int) ($_GET['course_id'] ?? 0);
$course = null;
foreach ($courses as $c) {
    if ((int) $c['course_id'] === $selected_id) { $course = $c; }
}
$course = $course ?? ($courses[0] ?? null);

$lessons = [];
if ($course) {
    $stmt = $pdo->prepare('SELECT lesson_id, title, sort_order FROM lessons WHERE course_id = ? ORDER BY sort_order, lesson_id');
    $stmt->execute([$course['course_id']]);
    $lessons = $stmt->fetchAll();
}

$total_lessons = count($lessons);
// Placeholder "current lesson" until lesson_progress exists: just the first one.
$current_lesson = $lessons[0] ?? null;
$current_index  = $total_lessons > 0 ? 1 : 0;

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
        <a class="btn btn-sm <?= (int) $c['course_id'] === (int) $course['course_id'] ? 'btn-pl-primary' : 'btn-outline-secondary' ?>"
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
            <p class="mb-3">
                Lesson <?= $current_index ?> of <?= $total_lessons ?> —
                <?= htmlspecialchars($current_lesson['title']) ?>
            </p>
            <a class="btn btn-pl-primary btn-sm" href="/student/lesson.php?id=<?= (int) $current_lesson['lesson_id'] ?>">
                Continue lesson
            </a>
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
        <p class="text-uppercase small text-muted mb-2">This week</p>
    </div>
    <!--
        Placeholder stats below — these need the `scores` / progress tables
        that are built in weeks 5–6. Structure is wired so swapping in real
        queries later is a drop-in change, not a redesign.
    -->
    <div class="col-md-4">
        <div class="card pl-card text-center">
            <div class="card-body">
                <p class="display-6 mb-0">—</p>
                <p class="text-muted small mb-0">Lessons completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card pl-card text-center">
            <div class="card-body">
                <p class="display-6 mb-0">—</p>
                <p class="text-muted small mb-0">Quiz average</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card pl-card text-center">
            <div class="card-body">
                <p class="display-6 mb-0">—</p>
                <p class="text-muted small mb-0">Games played</p>
            </div>
        </div>
    </div>
</div>

<div>
    <p class="text-uppercase small text-muted mb-2">Recent activity</p>
    <div class="card pl-card">
        <div class="card-body">
            <p class="text-muted mb-0">Activity feed comes online once the scores/progress tables are built (week 5–6).</p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
