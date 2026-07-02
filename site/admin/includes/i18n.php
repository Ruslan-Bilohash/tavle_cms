<?php
declare(strict_types=1);

function getAdminLang(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGS, true)) {
        $_SESSION['admin_lang'] = $_GET['lang'];
    }
    return $_SESSION['admin_lang'] ?? DEFAULT_LANG;
}

function adminTranslations(): array
{
    static $t = null;
    if ($t === null) {
        $lang = getAdminLang();
        $file = BILEN_ROOT . "/admin/languages/{$lang}.php";
        $t = require (file_exists($file) ? $file : BILEN_ROOT . '/admin/languages/uk.php');
    }
    return $t;
}

function __a(string $key): string
{
    $t = adminTranslations();
    return $t[$key] ?? $key;
}

function adminLangUrl(string $lang): string
{
    $params = $_GET;
    $params['lang'] = $lang;
    return '?' . http_build_query($params);
}