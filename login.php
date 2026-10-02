<?php
require_once __DIR__ . '/includes/auth.php';

if ($u = current_user()) redirect(home_for($u['role']));

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif ($user = attempt_login($email, $password)) {
        redirect(home_for($user['role']));
    } else {
        $error = 'Wrong email or password. Please try again.';
    }
}
$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="login-wrap">
  <div class="row g-0 login-card shadow-lg">
    <div class="col-lg-6 login-hero">
      <div class="brand-mark big"><i class="bi bi-controller"></i></div>
      <h1>Play <span class="text-accent">&amp;</span> Learn</h1>
      <p class="lead">Learn to code by playing games, taking quizzes and watching your progress grow.</p>
      <div class="hero-courses">
        <div class="hero-course"><i class="bi bi-filetype-py"></i> Python 101 <small>v2.0</small></div>
        <div class="hero-course"><i class="bi bi-cup-hot"></i> Java 101 <small>v2.0</small></div>
      </div>
      <pre class="hero-code"><code><span class="tok-k">for</span> day <span class="tok-k">in</span> range(<span class="tok-n">7</span>):
    play()
    learn()
print(<span class="tok-s">"Level up!"</span>)</code></pre>
    </div>
    <div class="col-lg-6 p-4 p-md-5 bg-white">
      <h2 class="h3 fw-bold mb-1">Welcome back</h2>
      <p class="text-muted mb-4">One login for students, parents and admins.</p>
      <?php if ($f = flash()): ?><div class="alert alert-<?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= e($error) ?></div><?php endif; ?>
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="email">Email</label>
          <input class="form-control form-control-lg" type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold" for="password">Password</label>
          <input class="form-control form-control-lg" type="password" id="password" name="password" required>
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">Log in <i class="bi bi-arrow-right"></i></button>
      </form>
      <div class="demo-accounts mt-4">
        <div class="small text-muted mb-2">Demo accounts (click to fill in):</div>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-sm btn-light demo-fill" data-email="student@playlearn.com" data-pass="student123"><i class="bi bi-mortarboard"></i> Student</button>
          <button type="button" class="btn btn-sm btn-light demo-fill" data-email="parent@playlearn.com" data-pass="parent123"><i class="bi bi-people"></i> Parent</button>
          <button type="button" class="btn btn-sm btn-light demo-fill" data-email="admin@playlearn.com" data-pass="admin123"><i class="bi bi-shield-lock"></i> Admin</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$inlineScript = "<script>$('.demo-fill').on('click', function(){ $('#email').val($(this).data('email')); $('#password').val($(this).data('pass')); });</script>";
require __DIR__ . '/includes/footer.php';
