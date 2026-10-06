<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/db.php';

if (is_logged_in()) {
    redirect_for_role($_SESSION['role']);
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Enter both an email and a password.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, role, first_name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']    = $user['user_id'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            redirect_for_role($user['role']);
        } else {
            $error = 'Incorrect email or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in · Play &amp; Learn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="pl-login-body">
<div class="pl-login-card">
    <h1 class="pl-login-title">Play &amp; Learn</h1>
    <p class="text-muted mb-4">Log in to continue</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password" required>
        </div>
        <button class="btn btn-pl-primary w-100" type="submit">Log in</button>
    </form>

    <p class="text-muted small mt-4 mb-0">
        Test accounts: admin@test.com · student@test.com · parent@test.com
    </p>
</div>
</body>
</html>
