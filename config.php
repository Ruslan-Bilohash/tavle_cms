<?php
declare(strict_types=1);

define('LANDING_ROOT', __DIR__);
define('SITE_ORIGIN', 'https://bilohash.com');
define('SITE_URL', 'https://bilohash.com/tavle');
define('BASE_PATH', '/tavle');
define('DEMO_URL', 'https://bilohash.com/tavle/site/');
define('ADMIN_DEMO_URL', 'https://bilohash.com/tavle/site/admin/');
define('PRODUCT_NAME', 'Bilen CMS');
define('DEFAULT_LANG', 'en');
define('AVAILABLE_LANGS', ['uk', 'en', 'ru', 'no']);

date_default_timezone_set('Europe/Kyiv');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => BASE_PATH . '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}