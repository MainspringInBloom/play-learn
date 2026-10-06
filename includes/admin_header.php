<?php
$user = function_exists('current_user') ? current_user() : ['first_name' => null];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' · ' : '' ?>Play &amp; Learn Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="pl-header">
    <div class="pl-header-inner">
        <a class="pl-logo" href="/admin/index.php">Play &amp; Learn <span class="pl-admin-tag">Admin</span></a>
        <nav class="pl-nav">
            <a href="/admin/index.php">Courses</a>
        </nav>
        <div class="pl-user">
            <span class="pl-avatar"><?= htmlspecialchars(strtoupper(substr($user['first_name'] ?? '?', 0, 1))) ?></span>
            <span><?= htmlspecialchars($user['first_name'] ?? 'Admin') ?></span>
            <a href="/logout.php" class="btn btn-sm btn-outline-secondary ms-3">Log out</a>
        </div>
    </div>
</header>
<main class="pl-main container py-4">
