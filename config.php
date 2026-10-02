<?php
// ============================================================
//  Play and Learn - settings
//  Change these to match your MySQL setup.
//  XAMPP default: user "root" with an empty password.
// ============================================================
define('DB_HOST', getenv('PL_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('PL_DB_NAME') ?: 'play_and_learn');
define('DB_USER', getenv('PL_DB_USER') ?: 'root');
define('DB_PASS', getenv('PL_DB_PASS') !== false ? getenv('PL_DB_PASS') : '');

define('APP_NAME', 'Play and Learn');

// Web path to this folder, e.g. "/play-and-learn". Worked out automatically;
// set it by hand here if links break on your server.
$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$here    = realpath(__DIR__);
$base    = ($docRoot !== '' && strpos($here, $docRoot) === 0) ? substr($here, strlen($docRoot)) : '';
define('BASE_URL', rtrim(str_replace('\\', '/', $base), '/'));
