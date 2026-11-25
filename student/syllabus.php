<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/progress.php';
require_role('student');

$student_id = (int) $_SESSION['user_id'];
$course_id  = (int) ($_GET['course_id'] ?? 0);

$course_stmt = $pdo->prepare('SELECT course_id, title, description FROM courses WHERE course_id = ?');
$course_stmt->execute([$course_id]);
$course = $course_stmt->fetch();
if (!$course) {
    http_response_code(404);
    die('Course not found.');
}

$page_title = $course['title'] . ' — Syllabus';
$active_nav = 'dashboard';

$lessons = pl_course_lessons($pdo, $course_id, $student_id);
$done = count(array_filter($lessons, function ($l) { return $l['status'] === 'completed'; }));
$total = count($lessons);
$percent = $total ? (int) round($done / $total * 100) : 0;

require __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/play.css">

<a class="small d-inline-block mb-3" href="/student/dashboard.php?course_id=<?= $course_id ?>">&larr; Back to dashboard</a>
<h1 class="h3 mb-1"><?= htmlspecialchars($course['title']) ?></h1>
<p class="text-muted mb-3"><?= htmlspecialchars($course['description'] ?? '') ?></p>

<?php if (isset($_GET['locked'])): ?>
    <div class="pl-banner" role="status">That lesson is still locked. Finish the lesson and quiz before it first.</div>
<?php endif; ?>

<div class="mb-4">
    <div class="d-flex justify-content-between small text-muted mb-1">
        <span><?= $done ?> of <?= $total ?> lessons complete</span><span><?= $percent ?>%</span>
    </div>
    <div class="pl-progress" role="progressbar" aria-label="Course progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $percent ?>">
        <div class="pl-progress-bar" style="width: <?= $percent ?>%"></div>
    </div>
</div>

<p class="small text-muted">Finish each lesson, then pass its quiz (<?= PL_PASS_PERCENT ?>% or more) to open the next one. Lessons you've completed can be revisited any time without changing your progress.</p>

<ol class="pl-syllabus list-unstyled">
    <?php foreach ($lessons as $i => $lesson): ?>
        <?php
            $quizzes = pl_playable_quizzes($lesson);
            if ($lesson['locked']) {
                $action = null;
                $status = ['Locked', 'locked'];
            } elseif ($lesson['status'] === 'completed') {
                $action = ['Review', 'btn-outline-secondary'];
                $status = ['Completed', 'done'];
            } elseif ($lesson['status'] === 'in_progress') {
                $action = ['Continue', 'btn-pl-primary'];
                $status = ['In progress', 'active'];
            } else {
                $action = [$lesson['is_current'] ? 'Start' : 'Open', 'btn-pl-primary'];
                $status = ['Not started', 'idle'];
            }
        ?>
        <li class="pl-syllabus-item<?= $lesson['is_current'] ? ' is-current' : '' ?><?= $lesson['locked'] ? ' is-locked' : '' ?>">
            <span class="pl-syllabus-num"><?= $i + 1 ?></span>
            <div class="pl-syllabus-main">
                <p class="pl-syllabus-title"><?= htmlspecialchars($lesson['title']) ?></p>
                <p class="pl-syllabus-meta">
                    <span class="pl-status pl-status--<?= $status[1] ?>"><?= $status[0] ?></span>
                    <?php foreach ($quizzes as $q): ?>
                        <span class="pl-syllabus-quiz">
                            Quiz: <?= $q['best'] !== null ? (int) round($q['best']) . '%' . ($q['passed'] ? ' passed' : ' (not yet passed)') : 'not taken' ?>
                            <?php if (!$lesson['locked']): ?>
                                &middot; <a href="/student/quiz.php?quiz_id=<?= (int) $q['quiz_id'] ?>"><?= $q['passed'] ? 'Practice' : 'Take it' ?></a>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </p>
            </div>
            <?php if ($action): ?>
                <a class="btn btn-sm <?= $action[1] ?>" href="/student/lesson.php?id=<?= (int) $lesson['lesson_id'] ?>"><?= $action[0] ?></a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
    <?php if (!$lessons): ?>
        <li class="text-muted">No lessons in this course yet.</li>
    <?php endif; ?>
</ol>

<?php require __DIR__ . '/../includes/footer.php'; ?>
