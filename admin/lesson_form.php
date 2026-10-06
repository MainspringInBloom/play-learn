<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require_role('admin');

$course_id = (int) ($_GET['course_id'] ?? $_POST['course_id'] ?? 0);
$lesson_id = (int) ($_GET['id'] ?? $_POST['lesson_id'] ?? 0);
$is_edit   = $lesson_id > 0;

$course_stmt = $pdo->prepare('SELECT course_id, title FROM courses WHERE course_id = ?');
$course_stmt->execute([$course_id]);
$course = $course_stmt->fetch();
if (!$course) {
    http_response_code(404);
    die('Course not found.');
}

$lesson = [
    'title'      => '',
    'content'    => '',
    'game_type'  => '',
    'sort_order' => 0,
];
$errors = [];

if ($is_edit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM lessons WHERE lesson_id = ? AND course_id = ?');
    $stmt->execute([$lesson_id, $course_id]);
    $found = $stmt->fetch();
    if (!$found) {
        http_response_code(404);
        die('Lesson not found.');
    }
    $lesson = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lesson['title']      = trim($_POST['title'] ?? '');
    $lesson['content']    = trim($_POST['content'] ?? '');
    $lesson['game_type']  = trim($_POST['game_type'] ?? '') ?: null;
    $lesson['sort_order'] = (int) ($_POST['sort_order'] ?? 0);

    if ($lesson['title'] === '') {
        $errors[] = 'Title is required.';
    }

    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $pdo->prepare('
                UPDATE lessons
                SET title = ?, content = ?, game_type = ?, sort_order = ?
                WHERE lesson_id = ? AND course_id = ?
            ');
            $stmt->execute([
                $lesson['title'], $lesson['content'], $lesson['game_type'], $lesson['sort_order'],
                $lesson_id, $course_id,
            ]);
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO lessons (course_id, title, content, game_type, sort_order)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $course_id, $lesson['title'], $lesson['content'], $lesson['game_type'], $lesson['sort_order'],
            ]);
        }

        header('Location: /admin/lessons.php?course_id=' . $course_id . '&saved=1');
        exit;
    }
}

$page_title = ($is_edit ? 'Edit' : 'Add') . ' lesson';
require __DIR__ . '/../includes/admin_header.php';
?>
<nav class="small mb-2">
    <a href="/admin/index.php">Courses</a> /
    <a href="/admin/lessons.php?course_id=<?= $course_id ?>"><?= htmlspecialchars($course['title']) ?></a> /
    <?= $is_edit ? 'Edit lesson' : 'Add lesson' ?>
</nav>

<h1 class="h3 mb-4"><?= $is_edit ? 'Edit lesson' : 'Add lesson' ?></h1>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<form method="post" class="pl-form" style="max-width: 640px;">
    <input type="hidden" name="course_id" value="<?= $course_id ?>">
    <?php if ($is_edit): ?>
        <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label" for="title">Title</label>
        <input class="form-control" id="title" name="title" required
               value="<?= htmlspecialchars($lesson['title']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label" for="content">Content</label>
        <textarea class="form-control" id="content" name="content" rows="5"><?= htmlspecialchars($lesson['content'] ?? '') ?></textarea>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="game_type">Game type <span class="text-muted small">(optional — built out in week 5)</span></label>
            <input class="form-control" id="game_type" name="game_type"
                   value="<?= htmlspecialchars($lesson['game_type'] ?? '') ?>" placeholder="e.g. matching, drag_drop">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="sort_order">Order</label>
            <input class="form-control" type="number" id="sort_order" name="sort_order"
                   value="<?= (int) $lesson['sort_order'] ?>">
        </div>
    </div>

    <button class="btn btn-pl-primary" type="submit"><?= $is_edit ? 'Save changes' : 'Add lesson' ?></button>
    <a class="btn btn-outline-secondary" href="/admin/lessons.php?course_id=<?= $course_id ?>">Cancel</a>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
