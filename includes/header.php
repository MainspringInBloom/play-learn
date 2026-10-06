<?php
/**
 * Shared site header/nav.
 * Expects (optionally) $active_nav = 'dashboard' | 'quizzes' | 'games' | 'progress'
 * set by the including page, and an active session for the user initial/name.
 */
$user = function_exists('current_user') ? current_user() : ['first_name' => null];
$active_nav = $active_nav ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' · ' : '' ?>Play &amp; Learn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Georgia&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="pl-header">
    <div class="pl-header-inner">
        <a class="pl-logo" href="/student/dashboard.php">Play &amp; Learn</a>
        <nav class="pl-nav">
            <a href="/student/dashboard.php" class="<?= $active_nav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/student/quizzes.php" class="<?= $active_nav === 'quizzes' ? 'active' : '' ?>">Quizzes</a>
            <a href="/student/games.php" class="<?= $active_nav === 'games' ? 'active' : '' ?>">Games</a>
            <a href="/student/progress.php" class="<?= $active_nav === 'progress' ? 'active' : '' ?>">Progress</a>
        </nav>
        <div class="pl-user">
            <span class="pl-avatar"><?= htmlspecialchars(strtoupper(substr($user['first_name'] ?? '?', 0, 1))) ?></span>
            <span><?= htmlspecialchars($user['first_name'] ?? 'Guest') ?></span>
        </div>
    </div>
</header>
<main class="pl-main">
