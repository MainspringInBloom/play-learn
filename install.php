<?php
/**
 * One-click installer: creates the database and loads the demo data.
 * Open http://localhost/play-and-learn/install.php in your browser.
 * WARNING: this deletes and recreates the play_and_learn database.
 * Delete this file before putting the site online.
 */
require_once __DIR__ . '/config.php';

$status = null;
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        foreach (['schema.sql', 'seed.sql'] as $file) {
            $sql = file_get_contents(__DIR__ . '/db/' . $file);
            $stmt = $pdo->prepare($sql);   // runs every statement in the file
            $stmt->execute();
            do { } while ($stmt->nextRowset());
            $stmt->closeCursor();
        }
        $pdo->exec('USE ' . DB_NAME);
        $n = $pdo->query('SELECT (SELECT COUNT(*) FROM courses), (SELECT COUNT(*) FROM users), (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE())')->fetch(PDO::FETCH_NUM);
        $status = 'ok';
        $message = "Installed! {$n[2]} tables, {$n[0]} courses and {$n[1]} demo users are ready.";
    } catch (Throwable $e) {
        $status = 'error';
        $message = $e->getMessage();
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install · Play and Learn</title>
<link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
</head><body class="bg-light">
<div class="container py-5" style="max-width:640px">
  <h1 class="fw-bold">Play and Learn – Install</h1>
  <p class="text-muted">This creates the <code><?= htmlspecialchars(DB_NAME) ?></code> database on <code><?= htmlspecialchars(DB_HOST) ?></code> and creates all 11 tables, and loads the 2 courses and the demo accounts.</p>
  <?php if ($status === 'ok'): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <a class="btn btn-primary" href="login.php">Go to login</a>
    <table class="table table-sm mt-4 bg-white"><thead><tr><th>Role</th><th>Email</th><th>Password</th></tr></thead><tbody>
      <tr><td>Admin</td><td>admin@playlearn.com</td><td>admin123</td></tr>
      <tr><td>Student</td><td>student@playlearn.com</td><td>student123</td></tr>
      <tr><td>Parent</td><td>parent@playlearn.com</td><td>parent123</td></tr>
    </tbody></table>
  <?php else: ?>
    <?php if ($status === 'error'): ?><div class="alert alert-danger"><strong>Install failed:</strong> <?= htmlspecialchars($message) ?><br><small>Is MySQL running? Check the user and password in config.php.</small></div><?php endif; ?>
    <div class="alert alert-warning">This will <strong>delete</strong> any existing <code><?= htmlspecialchars(DB_NAME) ?></code> database and all scores in it.</div>
    <form method="post"><input type="hidden" name="confirm" value="yes"><button class="btn btn-primary btn-lg">Install / Reset database</button></form>
  <?php endif; ?>
</div>
</body></html>
