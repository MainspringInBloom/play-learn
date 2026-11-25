<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/text.php';
require __DIR__ . '/../includes/games.php';
require_role('admin');

$lesson_id = (int) ($_GET['lesson_id'] ?? $_POST['lesson_id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT l.lesson_id, l.title, l.course_id, l.game_type, l.game_data, c.title AS course_title
    FROM lessons l JOIN courses c ON c.course_id = l.course_id
    WHERE l.lesson_id = ?
');
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();
if (!$lesson) {
    http_response_code(404);
    die('Lesson not found.');
}

$has_game = !empty($lesson['game_type']);
$game = ['type' => 'match_output', 'instructions' => '', 'pairs' => []];
$errors = [];

// Editing: load what is stored. (A lesson with an unknown or broken game just starts a fresh form.)
if ($has_game && isset(PL_GAME_TYPES[$lesson['game_type']])) {
    $stored = json_decode((string) $lesson['game_data'], true);
    $game['type'] = $lesson['game_type'];
    if (is_array($stored)) {
        $game['instructions'] = (string) ($stored['instructions'] ?? '');
        $keys = PL_GAME_TYPES[$game['type']]['keys'];
        foreach ((array) ($stored['pairs'] ?? []) as $p) {
            $game['pairs'][] = ['p1' => (string) ($p[$keys[0]] ?? ''), 'p2' => (string) ($p[$keys[1]] ?? '')];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = (string) ($_POST['game_type'] ?? '');
    $game['type'] = isset(PL_GAME_TYPES[$type]) ? $type : $game['type'];
    $game['instructions'] = trim($_POST['instructions'] ?? '');

    $keys = PL_GAME_TYPES[$game['type']]['keys'];
    $rows = [];
    $game['pairs'] = [];
    foreach (is_array($_POST['pairs'] ?? null) ? array_values($_POST['pairs']) : [] as $row) {
        $game['pairs'][] = ['p1' => (string) ($row['p1'] ?? ''), 'p2' => (string) ($row['p2'] ?? '')];
        $rows[] = [$keys[0] => $row['p1'] ?? '', $keys[1] => $row['p2'] ?? ''];
    }

    if (!isset(PL_GAME_TYPES[$type])) {
        $errors[] = 'Choose a game type.';
    }
    [$pairs, $pair_errors] = pl_clean_game_pairs($game['type'], $rows);
    $errors = array_merge($errors, $pair_errors);
    if (pl_strlen($game['instructions']) > 200) {
        $errors[] = 'Instructions can be at most 200 characters.';
    }

    if (empty($errors)) {
        // The game page shows these instructions; use the type's default when left blank.
        $data = [
            'instructions' => $game['instructions'] !== '' ? $game['instructions'] : PL_GAME_TYPES[$game['type']]['instructions'],
            'pairs'        => $pairs,
        ];
        $pdo->prepare('UPDATE lessons SET game_type = ?, game_data = ? WHERE lesson_id = ?')
            ->execute([$game['type'], json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $lesson_id]);
        header('Location: /admin/lessons.php?course_id=' . (int) $lesson['course_id'] . '&saved=1');
        exit;
    }
}

while (count($game['pairs']) < PL_GAME_MIN_PAIRS) {
    $game['pairs'][] = ['p1' => '', 'p2' => ''];
}

$page_title = ($has_game ? 'Edit' : 'Add') . ' game';
require __DIR__ . '/../includes/admin_header.php';

function pair_row(array $p, bool $code): void { ?>
    <div class="row g-2 mb-2 js-row align-items-start">
        <div class="col-md-5">
            <textarea class="form-control<?= $code ? ' font-monospace' : '' ?>" rows="2" data-name="pairs[{i}][p1]"
                      aria-label="First side"><?= htmlspecialchars($p['p1']) ?></textarea>
        </div>
        <div class="col-md-5">
            <textarea class="form-control<?= $code ? ' font-monospace' : '' ?>" rows="2" data-name="pairs[{i}][p2]"
                      aria-label="Second side"><?= htmlspecialchars($p['p2']) ?></textarea>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger js-remove" aria-label="Remove pair">&times; Remove</button>
        </div>
    </div>
<?php } ?>
<nav class="small mb-2">
    <a href="/admin/index.php">Courses</a> /
    <a href="/admin/lessons.php?course_id=<?= (int) $lesson['course_id'] ?>"><?= htmlspecialchars($lesson['course_title']) ?></a> /
    Game for <?= htmlspecialchars($lesson['title']) ?>
</nav>

<h1 class="h3 mb-1"><?= $has_game ? 'Edit game' : 'Add a game' ?></h1>
<p class="text-muted mb-4">Lesson: <?= htmlspecialchars($lesson['title']) ?>. Each lesson can have one game.</p>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<form method="post" class="pl-form" style="max-width: 820px;">
    <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">

    <div class="row">
        <div class="col-md-5 mb-3">
            <label class="form-label" for="game_type">Game type</label>
            <select class="form-select" id="game_type" name="game_type">
                <?php foreach (PL_GAME_TYPES as $key => $def): ?>
                    <option value="<?= $key ?>"<?= $game['type'] === $key ? ' selected' : '' ?>><?= htmlspecialchars($def['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="instructions">Instructions <span class="text-muted small">(optional, a default is used for each type)</span></label>
        <input class="form-control" id="instructions" name="instructions" maxlength="200" value="<?= htmlspecialchars($game['instructions']) ?>">
    </div>

    <div class="mb-3" data-repeater data-min="<?= PL_GAME_MIN_PAIRS ?>" data-max="<?= PL_GAME_MAX_PAIRS ?>">
        <label class="form-label">Pairs <span class="text-muted small">(<?= PL_GAME_MIN_PAIRS ?>&ndash;<?= PL_GAME_MAX_PAIRS ?>; every pair is a match to find)</span></label>
        <div class="row g-2 mb-1 small text-muted">
            <div class="col-md-5" id="heading-1"><?= htmlspecialchars(PL_GAME_TYPES[$game['type']]['headings'][0]) ?></div>
            <div class="col-md-5" id="heading-2"><?= htmlspecialchars(PL_GAME_TYPES[$game['type']]['headings'][1]) ?></div>
        </div>
        <div data-repeater-rows>
            <?php foreach ($game['pairs'] as $p) { pair_row($p, $game['type'] === 'match_output'); } ?>
        </div>
        <template data-repeater-template><?php pair_row(['p1' => '', 'p2' => ''], $game['type'] === 'match_output'); ?></template>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-repeater-add>+ Add pair</button>
    </div>

    <button class="btn btn-pl-primary" type="submit">Save game</button>
    <a class="btn btn-outline-secondary" href="/admin/lessons.php?course_id=<?= (int) $lesson['course_id'] ?>">Cancel</a>
</form>

<script>
    // Column headings follow the chosen game type.
    (function () {
        var headings = <?= json_encode(array_map(function ($d) { return $d['headings']; }, PL_GAME_TYPES)) ?>;
        document.getElementById('game_type').addEventListener('change', function () {
            var h = headings[this.value];
            document.getElementById('heading-1').textContent = h[0];
            document.getElementById('heading-2').textContent = h[1];
        });
    })();
</script>
<script src="/assets/js/admin-repeater.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
