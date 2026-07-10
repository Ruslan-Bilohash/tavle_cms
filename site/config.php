<?php
/**
 * Bilen CMS - Main Configuration
 */

declare(strict_types=1);

define('BILEN_ROOT', __DIR__);
define('BILEN_VERSION', '1.1.1');

// Environment: production | development
define('APP_ENV', 'production');

// SQLite database (no MySQL required)
define('DB_PATH', BILEN_ROOT . '/data/bilen.sqlite');

// Site URL — https://bilohash.com/tavle/site
define('SITE_ORIGIN', 'https://bilohash.com');
define('SITE_URL', 'https://bilohash.com/tavle/site');
define('BASE_PATH', '/tavle/site');
define('SITE_NAME', 'Bilen CMS');

// Show top demo disclaimer bar (disable on client production sites)
define('DEMO_BANNER', true);

// Paths
define('UPLOAD_PATH', BILEN_ROOT . '/uploads/cars/');
define('UPLOAD_URL', BASE_PATH . '/uploads/cars/');

// Security
define('CSRF_TOKEN_NAME', 'bilen_csrf_token');
define('SESSION_NAME', 'bilen_session');

// Defaults
define('DEFAULT_LANG', 'en');
define('AVAILABLE_LANGS', ['uk', 'en', 'ru', 'no']);
define('ITEMS_PER_PAGE', 12);

// Timezone
date_default_timezone_set('Europe/Kyiv');

// Error reporting
if (APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Session (cookie scoped to subdirectory)
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_PATH . '/',
        'secure'   => APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}