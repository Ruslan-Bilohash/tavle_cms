<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function getLang(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGS, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

function t(): array
{
    static $strings = null;
    if ($strings === null) {
        $lang = getLang();
        $file = LANDING_ROOT . "/languages/{$lang}.php";
        $strings = require (file_exists($file) ? $file : LANDING_ROOT . '/languages/en.php');
    }
    return $strings;
}

function __l(string $key): string
{
    $s = t();
    return $s[$key] ?? $key;
}

function langLink(string $lang): string
{
    $p = $_GET;
    $p['lang'] = $lang;
    return '?' . http_build_query($p);
}

function url(string $path = ''): string
{
    $base = rtrim(BASE_PATH, '/');
    if ($path === '' || $path === '/') return $base . '/';
    if (str_starts_with($path, '?')) return $base . '/' . $path;
    return $base . '/' . ltrim($path, '/');
}

function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function hreflang(): string
{
    $base = rtrim(SITE_URL, '/') . '/';
    $html = '<link rel="alternate" hreflang="en" href="' . e($base) . '">' . "\n";
    foreach (AVAILABLE_LANGS as $l) {
        if ($l === DEFAULT_LANG) {
            continue;
        }
        $html .= '<link rel="alternate" hreflang="' . $l . '" href="' . e($base . '?lang=' . $l) . '">' . "\n";
    }
    return $html;
}