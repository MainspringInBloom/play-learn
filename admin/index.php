<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('admin');

$page_title = 'Courses';

$courses = $pdo->query('
    SELECT c.course_id, c.title, c.description,
           COUNT(DISTINCT l.lesson_id) AS lesson_count,
           COUNT(DISTINCT q.quiz_id)   AS quiz_count
    FROM courses c
    LEFT JOIN lessons l ON l.course_id = c.course_id
    LEFT JOIN quizzes q ON q.lesson_id = l.lesson_id
    GROUP BY c.course_id, c.title, c.description
    ORDER BY c.course_id
')->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Courses</h1>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Deleted successfully.</div>
<?php endif; ?>

<div class="row g-3">
    <?php foreach ($courses as $course): ?>
        <div class="col-md-6">
            <div class="card pl-card h-100">
                <div class="card-body">
                    <h2 class="h5"><?= htmlspecialchars($course['title']) ?></h2>
                    <p class="text-muted small"><?= htmlspecialchars($course['description'] ?? '') ?></p>
                    <p class="mb-3">
                        <?= (int) $course['lesson_count'] ?> lesson<?= $course['lesson_count'] == 1 ? '' : 's' ?>
                        &middot;
                        <?= (int) $course['quiz_count'] ?> quiz<?= $course['quiz_count'] == 1 ? '' : 'zes' ?>
                    </p>
                    <a class="btn btn-pl-primary btn-sm"
                       href="/admin/lessons.php?course_id=<?= (int) $course['course_id'] ?>">
                        Manage lessons &amp; quizzes
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($courses)): ?>
        <p class="text-muted">No courses yet. Courses are seeded directly in the database for now (Python 101 / Java 101).</p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
