<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/games.php';
require_role('admin');

$course_id = (int) ($_GET['course_id'] ?? 0);

$course_stmt = $pdo->prepare('SELECT course_id, title FROM courses WHERE course_id = ?');
$course_stmt->execute([$course_id]);
$course = $course_stmt->fetch();

if (!$course) {
    http_response_code(404);
    die('Course not found.');
}

$page_title = $course['title'] . ' — Lessons';

$lessons_stmt = $pdo->prepare('
    SELECT l.lesson_id, l.title, l.sort_order, l.game_type,
           COUNT(q.quiz_id) AS quiz_count
    FROM lessons l
    LEFT JOIN quizzes q ON q.lesson_id = l.lesson_id
    WHERE l.course_id = ?
    GROUP BY l.lesson_id, l.title, l.sort_order, l.game_type
    ORDER BY l.sort_order, l.lesson_id
');
$lessons_stmt->execute([$course_id]);
$lessons = $lessons_stmt->fetchAll();

require __DIR__ . '/../includes/admin_header.php';
?>
<nav class="small mb-2"><a href="/admin/index.php">Courses</a> / <?= htmlspecialchars($course['title']) ?></nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= htmlspecialchars($course['title']) ?> — Lessons</h1>
    <a class="btn btn-pl-primary" href="/admin/lesson_form.php?course_id=<?= $course_id ?>">+ Add lesson</a>
</div>

<?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Lesson saved.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Lesson deleted.</div>
<?php endif; ?>
<?php if (isset($_GET['game_deleted'])): ?>
    <div class="alert alert-success">Game removed from the lesson.</div>
<?php endif; ?>

<table class="table pl-table align-middle">
    <thead>
        <tr>
            <th>#</th>
            <th>Title</th>
            <th>Game</th>
            <th>Quizzes</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lessons as $lesson): ?>
            <tr>
                <td><?= (int) $lesson['sort_order'] ?></td>
                <td><?= htmlspecialchars($lesson['title']) ?></td>
                <td>
                    <?php if (!empty($lesson['game_type'])): ?>
                        <?= htmlspecialchars(PL_GAME_TYPES[$lesson['game_type']]['label'] ?? $lesson['game_type']) ?>
                        <a class="small ms-1" href="/admin/game_form.php?lesson_id=<?= (int) $lesson['lesson_id'] ?>">Edit</a>
                        <form class="d-inline" method="post" action="/admin/delete_game.php"
                              onsubmit="return confirm('Remove this game from the lesson? Students\' past game scores are kept.');">
                            <input type="hidden" name="lesson_id" value="<?= (int) $lesson['lesson_id'] ?>">
                            <input type="hidden" name="course_id" value="<?= $course_id ?>">
                            <button class="btn btn-link btn-sm p-0 ms-1 text-danger" type="submit">Remove</button>
                        </form>
                    <?php else: ?>
                        <a href="/admin/game_form.php?lesson_id=<?= (int) $lesson['lesson_id'] ?>">+ Add game</a>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/admin/quizzes.php?lesson_id=<?= (int) $lesson['lesson_id'] ?>">
                        <?= (int) $lesson['quiz_count'] ?> quiz<?= $lesson['quiz_count'] == 1 ? '' : 'zes' ?>
                    </a>
                </td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary"
                       href="/admin/lesson_form.php?course_id=<?= $course_id ?>&amp;id=<?= (int) $lesson['lesson_id'] ?>">Edit</a>
                    <form class="d-inline" method="post" action="/admin/delete_lesson.php"
                          onsubmit="return confirm('Delete this lesson? Its quizzes will be deleted too.');">
                        <input type="hidden" name="lesson_id" value="<?= (int) $lesson['lesson_id'] ?>">
                        <input type="hidden" name="course_id" value="<?= $course_id ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($lessons)): ?>
            <tr><td colspan="5" class="text-muted">No lessons yet for this course.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>
