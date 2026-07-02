<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/vertical-lib.php';

$lang = getLang();
$strings = t();
$t = $strings;
$site_url = rtrim(SITE_URL, '/');