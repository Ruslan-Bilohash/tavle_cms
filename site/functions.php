<?php
/**
 * Bilen CMS - Helper Functions
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

// ─── Language ───────────────────────────────────────────────

function getCurrentLang(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGS, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

function loadTranslations(): array
{
    static $translations = null;
    if ($translations === null) {
        $lang = getCurrentLang();
        $file = BILEN_ROOT . "/languages/{$lang}.php";
        $translations = file_exists($file) ? require $file : require BILEN_ROOT . '/languages/uk.php';
    }
    return $translations;
}

function __t(string $key, ?string $fallback = null): string
{
    $t = loadTranslations();
    return $t[$key] ?? $fallback ?? $key;
}

function getEcosystemItems(): array
{
    static $items = null;
    if ($items === null) {
        $ecoFile = dirname(BILEN_ROOT, 2) . '/includes/ecosystem-i18n.php';
        if (!is_file($ecoFile)) {
            $items = [];
        } else {
            require_once $ecoFile;
            $items = bh_ecosystem_merge_labels(
                bh_ecosystem_product_labels(getCurrentLang()),
                'tavle'
            );
        }
    }
    return $items;
}

function getListingPhone(array $car): ?string
{
    $phone = trim((string)($car['dealer_phone'] ?? ''));
    if ($phone !== '') {
        return $phone;
    }
    $site = trim(getSetting('site_phone'));
    return $site !== '' ? $site : null;
}

function langUrl(string $lang): string
{
    $params = $_GET;
    $params['lang'] = $lang;
    return '?' . http_build_query($params);
}

// ─── Security ───────────────────────────────────────────────

function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrfToken(?string $token): bool
{
    return $token !== null
        && isset($_SESSION[CSRF_TOKEN_NAME])
        && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e(generateCsrfToken()) . '">';
}

function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

function isNewsNavItem(array $item): bool
{
    $url = trim($item['url'] ?? '');
    return $url === 'news.php' || str_ends_with($url, '/news.php');
}

function catalogNavActiveLabel(array $q): string
{
    if (!empty($q['is_leasing'])) {
        return __t('leasing');
    }
    if (!empty($q['is_en_route'])) {
        return __t('en_route');
    }
    if (!empty($q['is_on_order'])) {
        return __t('on_order');
    }
    $condition = $q['condition_type'] ?? '';
    if ($condition === 'new') {
        return __t('new_cars');
    }
    if ($condition === 'like_new') {
        return __t('like_new');
    }
    if ($condition === 'used') {
        return __t('used');
    }
    if (empty($q['is_leasing']) && empty($q['is_en_route']) && empty($q['is_on_order']) && $condition === '') {
        return __t('buy');
    }
    return __t('nav_catalog');
}

function filterUrl(array $set = [], array $unset = []): string
{
    $params = $_GET;
    foreach ($unset as $key) {
        unset($params[$key]);
    }
    foreach ($set as $key => $value) {
        if ($value === null) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    unset($params['page']);
    $query = http_build_query($params);
    return url($query !== '' ? '?' . $query : '');
}

function url(string $path = ''): string
{
    $base = rtrim(BASE_PATH, '/');
    if ($path === '' || $path === '/') {
        return $base . '/';
    }
    if (str_starts_with($path, '?')) {
        return $base . '/' . $path;
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function absoluteUrl(string $path): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return rtrim(SITE_ORIGIN, '/') . (str_starts_with($path, '/') ? $path : '/' . $path);
}

function currentUrl(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (BASE_PATH !== '' && str_starts_with($uri, BASE_PATH)) {
        $uri = substr($uri, strlen(BASE_PATH)) ?: '/';
    }
    $query = '';
    if (str_contains($uri, '?')) {
        [$uri, $query] = explode('?', $uri, 2);
        $query = '?' . $query;
    }
    $uri = $uri === '/' ? '' : $uri;
    return rtrim(SITE_URL, '/') . $uri . $query;
}

function redirect(string $url): void
{
    if (str_starts_with($url, '/') && !str_starts_with($url, 'http')) {
        $base = rtrim(BASE_PATH, '/');
        if ($base !== '' && !str_starts_with($url, $base)) {
            $url = $base . $url;
        }
    }
    header('Location: ' . $url);
    exit;
}

// ─── Settings ───────────────────────────────────────────────

function getSetting(string $key, string $default = ''): string
{
    static $cache = [];
    if (!isset($cache[$key])) {
        $row = Database::fetchOne('SELECT setting_value FROM settings WHERE setting_key = ?', 's', [$key]);
        $cache[$key] = $row['setting_value'] ?? $default;
    }
    return $cache[$key];
}

function setSetting(string $key, string $value): void
{
    Database::execute(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value',
        'ss',
        [$key, $value]
    );
}

// ─── Formatting ───────────────────────────────────────────────

function getCurrencyCode(): string
{
    return getSetting('currency_code', 'USD');
}

function getCurrencySymbol(): string
{
    return getSetting('currency_symbol', '$');
}

function formatPrice(int $amount): string
{
    $symbol = getCurrencySymbol();
    $formatted = number_format($amount, 0, '.', ' ');
    return $formatted . ' ' . $symbol;
}

function formatSecondaryPrice(int $amount): string
{
    if (getSetting('show_secondary_price', '1') !== '1') {
        return '';
    }
    $code = getSetting('secondary_currency_code', 'EUR');
    $symbols = ['EUR' => '€', 'USD' => '$', 'UAH' => 'грн', 'NOK' => 'kr', 'GBP' => '£'];
    $symbol = $symbols[$code] ?? $code;
    if ($code === 'UAH') {
        $converted = (int) round($amount * (float) getSetting('usd_rate', '45.05'));
    } else {
        $rate = (float) getSetting('secondary_currency_rate', '0.92');
        $converted = (int) round($amount * $rate);
    }
    return '≈' . number_format($converted, 0, '.', ' ') . ' ' . $symbol;
}

/** @deprecated use formatSecondaryPrice */
function formatPriceUah(int $usd): string
{
    return formatSecondaryPrice($usd);
}

function getItemsPerPage(): int
{
    return max(1, (int) getSetting('items_per_page', (string) ITEMS_PER_PAGE));
}

function getNavMenu(string $menu): array
{
    $json = getSetting('nav_' . $menu, '');
    $items = json_decode($json, true);
    if (!is_array($items) || empty($items)) {
        return $menu === 'header'
            ? [['url' => '', 'label' => ['uk' => 'Купівля', 'en' => 'Buy', 'ru' => 'Покупка', 'no' => 'Kjøp']]]
            : [['url' => '', 'label' => ['uk' => 'Автомобілі', 'en' => 'Cars', 'ru' => 'Автомобили', 'no' => 'Biler']]];
    }
    return $items;
}

function getNavLabel(array $item): string
{
    $lang = getCurrentLang();
    return $item['label'][$lang] ?? $item['label']['uk'] ?? $item['label']['en'] ?? '';
}

function navItemUrl(string $path): string
{
    if ($path === '' || $path === '/') {
        return url();
    }
    if (str_starts_with($path, 'http')) {
        return $path;
    }
    if (str_starts_with($path, '?')) {
        return url($path);
    }
    return url($path);
}

function isNavTopActive(array $item, string $currentPath): bool
{
    $raw = trim($item['url'] ?? '');
    if ($raw === '' || $raw === '/') {
        return $currentPath === '/' || $currentPath === '/index.php';
    }
    if (str_starts_with($raw, '?')) {
        return false;
    }
    $target = basename(parse_url(navItemUrl($raw), PHP_URL_PATH) ?: '');
    $current = trim($currentPath, '/');
    if ($current === '') {
        return false;
    }
    $currentBase = basename($current);
    if ($target !== '' && $target === $currentBase) {
        return true;
    }
    $targetStem = preg_replace('/\.php$/', '', $target) ?? $target;
    $currentStem = preg_replace('/\.php$/', '', $currentBase) ?? $currentBase;
    return $targetStem !== '' && $targetStem === $currentStem;
}

function getListingTypes(): array
{
    return ['car', 'plate', 'special'];
}

function getSpecialBodyTypes(): array
{
    return ['excavator', 'loader', 'backhoe', 'crane', 'truck_heavy', 'bulldozer', 'tractor'];
}

function getCatalogSeo(string $type): array
{
    $lang = getCurrentLang();
    $map = [
        'plate' => [
            'title' => [
                'uk' => 'Номерні знаки — купівля та продаж | Bilen CMS',
                'en' => 'License Plates — Buy & Sell | Bilen CMS',
                'ru' => 'Номерные знаки — покупка и продажа | Bilen CMS',
                'no' => 'Bilskilt — kjøp og salg | Bilen CMS',
            ],
            'desc' => [
                'uk' => 'Оголошення номерних знаків України та Норвегії — VIP, стандартні, колекційні та дипломатичні.',
                'en' => 'License plate listings for Ukraine and Norway — VIP, standard, collectible and diplomatic.',
                'ru' => 'Объявления номерных знаков Украины и Норвегии — VIP, стандартные, коллекционные.',
                'no' => 'Bilskilt-annonser for Ukraina og Norge — VIP, standard og samler.',
            ],
        ],
        'special' => [
            'title' => [
                'uk' => 'Спецтехніка — екскаватори, навантажувачі, крани | Bilen CMS',
                'en' => 'Special Equipment — excavators, loaders, cranes | Bilen CMS',
                'ru' => 'Спецтехника — экскаваторы, погрузчики, краны | Bilen CMS',
                'no' => 'Spesialutstyr — gravemaskiner, lastere, kraner | Bilen CMS',
            ],
            'desc' => [
                'uk' => 'Оголошення спецтехніки — екскаватори, JCB, навантажувачі, автокрани та важкі вантажівки.',
                'en' => 'Special equipment listings — excavators, JCB, loaders, mobile cranes and heavy trucks.',
                'ru' => 'Объявления спецтехники — экскаваторы, JCB, погрузчики, автокраны и тяжёлые грузовики.',
                'no' => 'Spesialutstyr-annonser — gravemaskiner, JCB, lastere, mobilkraner og tung lastebil.',
            ],
        ],
    ];
    $entry = $map[$type] ?? ['title' => [], 'desc' => []];
    return [
        'title' => $entry['title'][$lang] ?? $entry['title']['en'] ?? SITE_NAME,
        'desc'  => $entry['desc'][$lang] ?? $entry['desc']['en'] ?? '',
    ];
}

function catalogUrl(string $type = 'car'): string
{
    return match ($type) {
        'plate' => url('plates/'),
        'special' => url('special/'),
        default => url(),
    };
}

function brandUrl(string $slug): string
{
    return url('brand/' . $slug);
}

function modelUrl(string $brandSlug, string $modelSlug): string
{
    return url('brand/' . $brandSlug . '/' . $modelSlug);
}

function carUrl(int $id, string $slug = ''): string
{
    if ($slug !== '') {
        return url('car/' . $slug . '-' . $id);
    }
    return url('car.php?id=' . $id);
}

function getModelsWithCount(int $brandId): array
{
    return Database::fetchAll(
        "SELECT m.*, COUNT(c.id) as car_count
        FROM models m
        LEFT JOIN cars c ON c.model_id = m.id AND c.is_active = 1
        WHERE m.brand_id = ? AND m.is_active = 1
        GROUP BY m.id
        HAVING car_count > 0
        ORDER BY car_count DESC, m.name ASC",
        'i',
        [$brandId]
    );
}

function getModelSeoTitle(array $brand, array $model): string
{
    return $brand['name'] . ' ' . $model['name'] . ' — ' . getSetting('site_name', SITE_NAME);
}

function getModelSeoDescription(array $brand, array $model, int $count): string
{
    $lang = getCurrentLang();
    $templates = [
        'uk' => 'Купити %s %s. %d оголошень з фото, ціною та характеристиками.',
        'en' => 'Buy %s %s. %d listings with photos, prices and specifications.',
        'ru' => 'Купить %s %s. %d объявлений с фото, ценой и характеристиками.',
        'no' => 'Kjøp %s %s. %d annonser med bilder, priser og spesifikasjoner.',
    ];
    $tpl = $templates[$lang] ?? $templates['en'];
    return sprintf($tpl, $brand['name'], $model['name'], $count);
}

function formatMileage(?int $km): string
{
    if ($km === null) return '—';
    return number_format($km, 0, '.', ' ') . ' ' . __t('km');
}

function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ─── Auth ───────────────────────────────────────────────────

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function currentUser(): ?array
{
    if (!isLoggedIn()) return null;
    return Database::fetchOne('SELECT * FROM users WHERE id = ? AND is_active = 1', 'i', [$_SESSION['user_id']]);
}

function loginUser(array $user): void
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];
}

function logoutUser(): void
{
    unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
}

function isListingOwner(?array $car, ?array $user = null): bool
{
    if (!$car || empty($car['user_id'])) {
        return false;
    }
    $user = $user ?? currentUser();
    return $user && (int)$user['id'] === (int)$car['user_id'];
}

function uniqueUsernameFromEmail(string $email): string
{
    $base = preg_replace('/[^a-z0-9]/', '', strtolower(explode('@', $email)[0] ?? 'user'));
    $base = $base !== '' ? $base : 'user';
    $username = $base;
    $i = 0;
    while (Database::fetchOne('SELECT id FROM users WHERE username = ?', 's', [$username])) {
        $i++;
        $username = $base . $i;
    }
    return $username;
}

/** @return array{user: array, password: ?string, is_new: bool} */
function findOrCreateListingUser(string $email, string $name, string $phone): array
{
    $email = strtolower(trim($email));
    $name = trim($name);
    $phone = trim($phone);

    $existing = Database::fetchOne('SELECT * FROM users WHERE email = ? AND is_active = 1', 's', [$email]);
    if ($existing) {
        if ($phone !== '' && empty($existing['phone'])) {
            Database::execute('UPDATE users SET phone = ?, name = ? WHERE id = ?', 'ssi', [$phone, $name, (int)$existing['id']]);
            $existing['phone'] = $phone;
            $existing['name'] = $name;
        }
        return ['user' => $existing, 'password' => null, 'is_new' => false];
    }

    $plain = substr(bin2hex(random_bytes(4)), 0, 8);
    $username = uniqueUsernameFromEmail($email);
    $hash = password_hash($plain, PASSWORD_DEFAULT);
    $id = Database::insert(
        'INSERT INTO users (username, email, password, role, name, phone, is_active) VALUES (?,?,?,?,?,?,1)',
        'ssssss',
        [$username, $email, $hash, 'user', $name, $phone]
    );
    $user = Database::fetchOne('SELECT * FROM users WHERE id = ?', 'i', [$id]);
    return ['user' => $user, 'password' => $plain, 'is_new' => true];
}

/** @return array{brand_id: int, model_id: int, year: int, price_usd: int} */
function listingDraftDefaults(): array
{
    $brand = Database::fetchOne('SELECT id FROM brands WHERE is_active = 1 ORDER BY name LIMIT 1');
    $brandId = (int)($brand['id'] ?? 1);
    $model = Database::fetchOne(
        'SELECT id FROM models WHERE brand_id = ? AND is_active = 1 ORDER BY name LIMIT 1',
        'i',
        [$brandId]
    );
    return [
        'brand_id' => $brandId,
        'model_id' => (int)($model['id'] ?? 1),
        'year' => (int)date('Y'),
        'price_usd' => 1,
    ];
}

function savePublicListing(array $data, int $userId, ?int $carId = null): int
{
    $lang = getCurrentLang();
    $desc = trim($data['description'] ?? '');
    $descriptions = [
        'description_uk' => $lang === 'uk' ? $desc : ($data['description_uk'] ?? ''),
        'description_en' => $lang === 'en' ? $desc : ($data['description_en'] ?? ''),
        'description_ru' => $lang === 'ru' ? $desc : ($data['description_ru'] ?? ''),
        'description_no' => $lang === 'no' ? $desc : ($data['description_no'] ?? ''),
    ];
    $isDraft = !empty($data['is_draft']);
    $isActive = $isDraft ? 0 : 1;
    $isDraftInt = $isDraft ? 1 : 0;
    $defaults = listingDraftDefaults();
    $brandId = (int)($data['brand_id'] ?? 0);
    $modelId = (int)($data['model_id'] ?? 0);
    $year = (int)($data['year'] ?? 0);
    $price = (int)($data['price_usd'] ?? 0);
    $title = trim($data['title'] ?? '');
    if ($title === '') {
        $title = $isDraft ? (__t('draft_listing') . ($carId ? ' #' . $carId : '')) : 'Listing';
    }
    $slug = trim($data['slug'] ?? '') ?: slugify($title);

    if ($carId) {
        $existing = Database::fetchOne('SELECT * FROM cars WHERE id = ? AND user_id = ?', 'ii', [$carId, $userId]);
        if ($brandId <= 0) {
            $brandId = (int)($existing['brand_id'] ?? $defaults['brand_id']);
        }
        if ($modelId <= 0) {
            $modelId = (int)($existing['model_id'] ?? $defaults['model_id']);
        }
        if ($year <= 0) {
            $year = (int)($existing['year'] ?? $defaults['year']);
        }
        if ($price <= 0) {
            $price = (int)($existing['price_usd'] ?? $defaults['price_usd']);
        }
        Database::execute(
            'UPDATE cars SET brand_id=?, model_id=?, title=?, slug=?, year=?, price_usd=?, mileage=?,
                body_type=?, transmission=?, fuel_type=?, drive_type=?, color=?, region=?, city=?,
                description_uk=?, description_en=?, description_ru=?, description_no=?,
                is_active=?, is_draft=?, updated_at=CURRENT_TIMESTAMP
                WHERE id=? AND user_id=?',
            'iissiiisssssssssiiii',
            [
                $brandId, $modelId, $title, $slug, $year, $price,
                !empty($data['mileage']) ? (int)$data['mileage'] : null,
                $data['body_type'] ?? 'sedan', $data['transmission'] ?? 'automatic',
                $data['fuel_type'] ?? 'petrol', $data['drive_type'] ?? 'fwd',
                $data['color'] ?? '', $data['region'] ?? '', $data['city'] ?? '',
                $descriptions['description_uk'], $descriptions['description_en'],
                $descriptions['description_ru'], $descriptions['description_no'],
                $isActive, $isDraftInt, $carId, $userId,
            ]
        );
        return $carId;
    }

    if ($brandId <= 0) {
        $brandId = $defaults['brand_id'];
    }
    if ($modelId <= 0) {
        $modelId = $defaults['model_id'];
    }
    if ($year <= 0) {
        $year = $isDraft ? $defaults['year'] : (int)date('Y');
    }
    if ($price <= 0) {
        $price = $isDraft ? $defaults['price_usd'] : 1;
    }

    $uniqueSlug = $slug;
    $n = 0;
    while (Database::fetchOne('SELECT id FROM cars WHERE slug = ?', 's', [$uniqueSlug])) {
        $n++;
        $uniqueSlug = $slug . '-' . $n;
    }

    return Database::insert(
        'INSERT INTO cars (brand_id, model_id, user_id, title, slug, year, price_usd, mileage,
            body_type, transmission, fuel_type, drive_type, color, region, city,
            description_uk, description_en, description_ru, description_no, listing_type, is_active, is_draft)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        'iiissiiisssssssssssii',
        [
            $brandId, $modelId, $userId, $title, $uniqueSlug, $year, $price,
            !empty($data['mileage']) ? (int)$data['mileage'] : null,
            $data['body_type'] ?? 'sedan', $data['transmission'] ?? 'automatic',
            $data['fuel_type'] ?? 'petrol', $data['drive_type'] ?? 'fwd',
            $data['color'] ?? '', $data['region'] ?? '', $data['city'] ?? '',
            $descriptions['description_uk'], $descriptions['description_en'],
            $descriptions['description_ru'], $descriptions['description_no'],
            $data['listing_type'] ?? 'car', $isActive, $isDraftInt,
        ]
    );
}

function ensureUploadDir(): void
{
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
}

function getListingImages(int $carId): array
{
    return Database::fetchAll(
        'SELECT * FROM car_images WHERE car_id = ? ORDER BY is_main DESC, sort_order ASC, id ASC',
        'i',
        [$carId]
    );
}

function canManageListingImages(?array $car, ?array $user = null): bool
{
    return isListingOwner($car, $user) || isAdmin();
}

/** @return array{uploaded: int, errors: string[]} */
function processListingPhotoUploads(int $carId, array $files): array
{
    ensureUploadDir();
    $uploaded = 0;
    $errors = [];
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $maxSize = 5 * 1024 * 1024;
    $maxPhotos = 20;

    $existing = Database::fetchOne('SELECT COUNT(*) AS cnt FROM car_images WHERE car_id = ?', 'i', [$carId]);
    $currentCount = (int)($existing['cnt'] ?? 0);

    $names = $files['name'] ?? [];
    if (!is_array($names)) {
        $names = [$names];
        $files = [
            'name' => $names,
            'type' => [$files['type'] ?? ''],
            'tmp_name' => [$files['tmp_name'] ?? ''],
            'error' => [$files['error'] ?? UPLOAD_ERR_NO_FILE],
            'size' => [$files['size'] ?? 0],
        ];
    }

    $sortBase = (int)(Database::fetchOne(
        'SELECT COALESCE(MAX(sort_order), -1) AS m FROM car_images WHERE car_id = ?',
        'i',
        [$carId]
    )['m'] ?? -1);

    foreach ($names as $i => $name) {
        if ($currentCount + $uploaded >= $maxPhotos) {
            $errors[] = __t('max_photos_reached');
            break;
        }
        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if (($files['error'][$i] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $errors[] = __t('photo_upload_failed') . ': ' . e((string)$name);
            continue;
        }
        $tmp = $files['tmp_name'][$i] ?? '';
        $size = (int)($files['size'][$i] ?? 0);
        $mime = mime_content_type($tmp) ?: ($files['type'][$i] ?? '');
        if ($size > $maxSize || !isset($allowed[$mime])) {
            $errors[] = __t('photo_invalid') . ': ' . e((string)$name);
            continue;
        }
        $ext = $allowed[$mime];
        $filename = $carId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($tmp, UPLOAD_PATH . $filename)) {
            $errors[] = __t('photo_upload_failed') . ': ' . e((string)$name);
            continue;
        }
        $sortBase++;
        $isMain = ($currentCount === 0 && $uploaded === 0) ? 1 : 0;
        Database::insert(
            'INSERT INTO car_images (car_id, filename, sort_order, is_main) VALUES (?,?,?,?)',
            'isii',
            [$carId, $filename, $sortBase, $isMain]
        );
        $uploaded++;
    }

    return ['uploaded' => $uploaded, 'errors' => $errors];
}

function deleteListingImage(int $imageId, ?int $userId = null): bool
{
    $row = Database::fetchOne(
        'SELECT ci.*, c.user_id, c.id AS car_id FROM car_images ci JOIN cars c ON c.id = ci.car_id WHERE ci.id = ?',
        'i',
        [$imageId]
    );
    if (!$row || !canManageListingImages(['user_id' => $row['user_id'], 'id' => $row['car_id']])) {
        return false;
    }
    $path = UPLOAD_PATH . $row['filename'];
    if (is_file($path) && !str_starts_with($row['filename'], 'http')) {
        @unlink($path);
    }
    Database::execute('DELETE FROM car_images WHERE id = ?', 'i', [$imageId]);
    if (!empty($row['is_main'])) {
        $next = Database::fetchOne(
            'SELECT id FROM car_images WHERE car_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1',
            'i',
            [(int)$row['car_id']]
        );
        if ($next) {
            Database::execute('UPDATE car_images SET is_main = 1 WHERE id = ?', 'i', [(int)$next['id']]);
        }
    }
    return true;
}

function setListingMainImage(int $imageId, ?int $userId = null): bool
{
    $row = Database::fetchOne(
        'SELECT ci.car_id, c.user_id, c.id AS car_id FROM car_images ci JOIN cars c ON c.id = ci.car_id WHERE ci.id = ?',
        'i',
        [$imageId]
    );
    if (!$row || !canManageListingImages(['user_id' => $row['user_id'], 'id' => $row['car_id']])) {
        return false;
    }
    Database::execute('UPDATE car_images SET is_main = 0 WHERE car_id = ?', 'i', [(int)$row['car_id']]);
    Database::execute('UPDATE car_images SET is_main = 1 WHERE id = ?', 'i', [$imageId]);
    return true;
}

function getDemoDealer(): ?array
{
    return Database::fetchOne(
        'SELECT * FROM dealers WHERE is_active = 1 ORDER BY is_verified DESC, id ASC LIMIT 1'
    );
}

function getUserListings(int $userId): array
{
    return Database::fetchAll(
        'SELECT c.*, b.name AS brand_name FROM cars c
         JOIN brands b ON c.brand_id = b.id
         WHERE c.user_id = ? ORDER BY c.created_at DESC',
        'i',
        [$userId]
    );
}

// ─── Cars ───────────────────────────────────────────────────

function getBodyTypes(): array
{
    return ['sedan','suv','hatchback','wagon','coupe','minivan','pickup','liftback','crossover'];
}

function getTransmissions(): array
{
    return ['manual','automatic','robot','cvt'];
}

function getFuelTypes(): array
{
    return ['petrol','diesel','electric','hybrid','gas'];
}

function getDriveTypes(): array
{
    return ['fwd','rwd','awd'];
}

function getConditionTypes(): array
{
    return ['new','like_new','used'];
}

function getCarImageUrl(?string $filename, int $carId = 0, string $listingType = 'car'): string
{
    if ($filename && (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://'))) {
        return $filename;
    }
    if ($filename && str_starts_with($filename, '/assets/')) {
        return rtrim(BASE_PATH, '/') . $filename;
    }
    if ($filename && file_exists(UPLOAD_PATH . $filename)) {
        return UPLOAD_URL . $filename;
    }
    if ($listingType === 'plate') {
        return rtrim(BASE_PATH, '/') . '/assets/images/plates/plate-default.svg';
    }
    $colors = ['1a1a2e', 'c0392b', '2c3e50', '34495e', 'e74c3c'];
    $color = $colors[$carId % count($colors)];
    $seed = $carId . ($filename ?? '');
    $variant = abs(crc32($seed)) % 5 + 1;
    return "https://placehold.co/640x480/{$color}/ffffff?text=Photo+{$variant}";
}

function listingImageUrl(?string $filename, int $carId = 0, string $listingType = 'car', int $width = 480): string
{
    $url = getCarImageUrl($filename, $carId, $listingType);
    $height = (int) round($width * 0.75);
    if (str_contains($url, 'images.unsplash.com')) {
        $base = preg_replace('/\?.*$/', '', $url);
        return $base . '?auto=format&fit=crop&w=' . $width . '&h=' . $height . '&q=75';
    }
    if (str_contains($url, 'placehold.co')) {
        return preg_replace('/\d+x\d+/', $width . 'x' . $height, $url);
    }
    return $url;
}

function listingImageSrcset(?string $filename, int $carId = 0, string $listingType = 'car'): string
{
    $small = listingImageUrl($filename, $carId, $listingType, 400);
    $large = listingImageUrl($filename, $carId, $listingType, 800);
    if ($small === $large) {
        return '';
    }
    return $small . ' 400w, ' . $large . ' 800w';
}

function getCarPreviewImages(int $carId, int $limit = 5): array
{
    static $cache = [];
    if (!isset($cache[$carId])) {
        $rows = Database::fetchAll(
            'SELECT filename FROM car_images WHERE car_id = ? ORDER BY is_main DESC, sort_order ASC LIMIT ?',
            'ii',
            [$carId, $limit]
        );
        $cache[$carId] = array_column($rows, 'filename');
    }
    return $cache[$carId];
}

function buildCarFilters(array $get): array
{
    $brandId = !empty($get['brand_id']) ? (int)$get['brand_id'] : null;
    $modelId = !empty($get['model_id']) ? (int)$get['model_id'] : null;

    if (!$brandId && !empty($get['brand_slug'])) {
        $brand = Database::fetchOne('SELECT id FROM brands WHERE slug = ? AND is_active = 1', 's', [sanitize($get['brand_slug'])]);
        $brandId = $brand ? (int)$brand['id'] : null;
    }
    if (!$modelId && !empty($get['model_slug']) && $brandId) {
        $model = Database::fetchOne(
            'SELECT id FROM models WHERE brand_id = ? AND slug = ? AND is_active = 1',
            'is',
            [$brandId, sanitize($get['model_slug'])]
        );
        $modelId = $model ? (int)$model['id'] : null;
    }

    return [
        'brand_id'      => $brandId,
        'model_id'      => $modelId,
        'brand_slug'    => !empty($get['brand_slug']) ? sanitize($get['brand_slug']) : null,
        'model_slug'    => !empty($get['model_slug']) ? sanitize($get['model_slug']) : null,
        'year_from'     => !empty($get['year_from']) ? (int)$get['year_from'] : null,
        'year_to'       => !empty($get['year_to']) ? (int)$get['year_to'] : null,
        'price_from'    => !empty($get['price_from']) ? (int)$get['price_from'] : null,
        'price_to'      => !empty($get['price_to']) ? (int)$get['price_to'] : null,
        'body_type'     => !empty($get['body_type']) ? sanitize($get['body_type']) : null,
        'transmission'  => !empty($get['transmission']) ? sanitize($get['transmission']) : null,
        'fuel_type'     => !empty($get['fuel_type']) ? sanitize($get['fuel_type']) : null,
        'drive_type'    => !empty($get['drive_type']) ? sanitize($get['drive_type']) : null,
        'color'         => !empty($get['color']) ? sanitize($get['color']) : null,
        'region'        => !empty($get['region']) ? sanitize($get['region']) : null,
        'condition_type'=> !empty($get['condition_type']) ? sanitize($get['condition_type']) : null,
        'is_leasing'    => isset($get['is_leasing']) ? 1 : null,
        'is_exchange'   => isset($get['is_exchange']) ? 1 : null,
        'is_new'        => isset($get['is_new']) ? 1 : null,
        'is_en_route'   => isset($get['is_en_route']) ? 1 : null,
        'is_on_order'   => isset($get['is_on_order']) ? 1 : null,
        'period'        => !empty($get['period']) ? sanitize($get['period']) : null,
        'vin'           => !empty($get['vin']) ? sanitize($get['vin']) : null,
        'q'             => !empty($get['q']) ? sanitize($get['q']) : null,
        'sort'          => !empty($get['sort']) ? sanitize($get['sort']) : 'newest',
        'page'          => max(1, (int)($get['page'] ?? 1)),
        'listing_type'  => !empty($get['listing_type']) && in_array($get['listing_type'], getListingTypes(), true)
            ? $get['listing_type'] : null,
        'plate_number'  => !empty($get['plate_number']) ? sanitize($get['plate_number']) : null,
    ];
}

function getCars(array $filters, ?int $perPage = null): array
{
    $perPage = $perPage ?? getItemsPerPage();
    $where = ['c.is_active = 1'];
    $types = '';
    $params = [];

    if (!empty($filters['listing_type'])) {
        $where[] = 'c.listing_type = ?';
        $types .= 's';
        $params[] = $filters['listing_type'];
    }
    if ($filters['brand_id']) {
        $where[] = 'c.brand_id = ?';
        $types .= 'i';
        $params[] = $filters['brand_id'];
    }
    if ($filters['model_id']) {
        $where[] = 'c.model_id = ?';
        $types .= 'i';
        $params[] = $filters['model_id'];
    }
    if ($filters['year_from']) {
        $where[] = 'c.year >= ?';
        $types .= 'i';
        $params[] = $filters['year_from'];
    }
    if ($filters['year_to']) {
        $where[] = 'c.year <= ?';
        $types .= 'i';
        $params[] = $filters['year_to'];
    }
    if ($filters['price_from']) {
        $where[] = 'c.price_usd >= ?';
        $types .= 'i';
        $params[] = $filters['price_from'];
    }
    if ($filters['price_to']) {
        $where[] = 'c.price_usd <= ?';
        $types .= 'i';
        $params[] = $filters['price_to'];
    }
    if ($filters['body_type']) {
        $where[] = 'c.body_type = ?';
        $types .= 's';
        $params[] = $filters['body_type'];
    }
    if ($filters['transmission']) {
        $where[] = 'c.transmission = ?';
        $types .= 's';
        $params[] = $filters['transmission'];
    }
    if ($filters['fuel_type']) {
        $where[] = 'c.fuel_type = ?';
        $types .= 's';
        $params[] = $filters['fuel_type'];
    }
    if ($filters['drive_type']) {
        $where[] = 'c.drive_type = ?';
        $types .= 's';
        $params[] = $filters['drive_type'];
    }
    if ($filters['color']) {
        $where[] = 'c.color LIKE ?';
        $types .= 's';
        $params[] = '%' . $filters['color'] . '%';
    }
    if ($filters['region']) {
        $where[] = 'c.region LIKE ?';
        $types .= 's';
        $params[] = '%' . $filters['region'] . '%';
    }
    if ($filters['condition_type']) {
        $where[] = 'c.condition_type = ?';
        $types .= 's';
        $params[] = $filters['condition_type'];
    }
    if ($filters['is_leasing']) {
        $where[] = 'c.is_leasing = 1';
    }
    if ($filters['is_exchange']) {
        $where[] = 'c.is_exchange = 1';
    }
    if ($filters['is_new']) {
        $where[] = 'c.is_new = 1';
    }
    if ($filters['is_en_route']) {
        $where[] = 'c.is_en_route = 1';
    }
    if ($filters['is_on_order']) {
        $where[] = 'c.is_on_order = 1';
    }
    if ($filters['period']) {
        $periodMap = [
            'day'   => '-1 day',
            'week'  => '-7 days',
            'month' => '-30 days',
            'year'  => '-365 days',
        ];
        if (isset($periodMap[$filters['period']])) {
            $where[] = 'c.created_at >= ?';
            $types .= 's';
            $params[] = date('Y-m-d H:i:s', strtotime($periodMap[$filters['period']]));
        }
    }
    if ($filters['vin']) {
        $where[] = 'c.vin LIKE ?';
        $types .= 's';
        $params[] = '%' . $filters['vin'] . '%';
    }
    if ($filters['plate_number']) {
        $where[] = '(c.plate_number LIKE ? OR c.title LIKE ?)';
        $types .= 'ss';
        $pn = '%' . $filters['plate_number'] . '%';
        $params[] = $pn;
        $params[] = $pn;
    }
    if ($filters['q']) {
        $where[] = '(c.title LIKE ? OR b.name LIKE ? OR m.name LIKE ?)';
        $types .= 'sss';
        $q = '%' . $filters['q'] . '%';
        $params[] = $q;
        $params[] = $q;
        $params[] = $q;
    }

    $whereClause = implode(' AND ', $where);

    $orderMap = [
        'newest'    => 'c.created_at DESC',
        'oldest'    => 'c.created_at ASC',
        'price_asc' => 'c.price_usd ASC',
        'price_desc'=> 'c.price_usd DESC',
        'year_desc' => 'c.year DESC',
        'year_asc'  => 'c.year ASC',
        'mileage'   => 'c.mileage ASC',
    ];
    $order = $orderMap[$filters['sort']] ?? $orderMap['newest'];

    $countSql = "SELECT COUNT(*) as total FROM cars c
        JOIN brands b ON c.brand_id = b.id
        JOIN models m ON c.model_id = m.id
        WHERE {$whereClause}";
    $countRow = Database::fetchOne($countSql, $types, $params);
    $total = (int)($countRow['total'] ?? 0);

    $offset = ($filters['page'] - 1) * $perPage;
    $sql = "SELECT c.*, b.name as brand_name, b.slug as brand_slug,
            m.name as model_name, d.name as dealer_name, d.phone as dealer_phone,
            (SELECT filename FROM car_images WHERE car_id = c.id AND is_main = 1 LIMIT 1) as main_image
        FROM cars c
        JOIN brands b ON c.brand_id = b.id
        JOIN models m ON c.model_id = m.id
        LEFT JOIN dealers d ON c.dealer_id = d.id
        WHERE {$whereClause}
        ORDER BY {$order}
        LIMIT ? OFFSET ?";

    $types .= 'ii';
    $params[] = $perPage;
    $params[] = $offset;

    $cars = Database::fetchAll($sql, $types, $params);

    return [
        'cars'  => $cars,
        'total' => $total,
        'pages' => (int) ceil($total / $perPage),
        'page'  => $filters['page'],
    ];
}

function getCarById(int $id): ?array
{
    $car = Database::fetchOne(
        "SELECT c.*, b.name as brand_name, b.slug as brand_slug,
            m.name as model_name, d.name as dealer_name, d.phone as dealer_phone,
            d.email as dealer_email, d.address as dealer_address
        FROM cars c
        JOIN brands b ON c.brand_id = b.id
        JOIN models m ON c.model_id = m.id
        LEFT JOIN dealers d ON c.dealer_id = d.id
        WHERE c.id = ? AND c.is_active = 1",
        'i', [$id]
    );

    if ($car) {
        Database::execute('UPDATE cars SET views = views + 1 WHERE id = ?', 'i', [$id]);
        $car['images'] = Database::fetchAll(
            'SELECT * FROM car_images WHERE car_id = ? ORDER BY sort_order',
            'i', [$id]
        );
    }

    return $car;
}

function getBrandsWithCount(?string $listingType = null): array
{
    $join = 'c.brand_id = b.id AND c.is_active = 1';
    $types = '';
    $params = [];
    if ($listingType) {
        $join .= ' AND c.listing_type = ?';
        $types = 's';
        $params[] = $listingType;
    }
    $sql = "SELECT b.*, COUNT(c.id) as car_count
        FROM brands b
        LEFT JOIN cars c ON {$join}
        WHERE b.is_active = 1
        GROUP BY b.id
        HAVING car_count > 0
        ORDER BY car_count DESC, b.sort_order ASC";
    return $types ? Database::fetchAll($sql, $types, $params) : Database::fetchAll($sql);
}

function getModelsByBrand(int $brandId): array
{
    return Database::fetchAll(
        'SELECT * FROM models WHERE brand_id = ? AND is_active = 1 ORDER BY name',
        'i', [$brandId]
    );
}

function getCarDescription(array $car): string
{
    $lang = getCurrentLang();
    $key = 'description_' . $lang;
    return $car[$key] ?? $car['description_en'] ?? $car['description_uk'] ?? '';
}

// ─── SEO ────────────────────────────────────────────────────

function getSeoTitle(): string
{
    $lang = getCurrentLang();
    return getSetting("seo_title_{$lang}", SITE_NAME);
}

function getSeoDescription(): string
{
    $lang = getCurrentLang();
    return getSetting("seo_description_{$lang}", '');
}

function getSeoKeywords(): string
{
    $lang = getCurrentLang();
    return getSetting("seo_keywords_{$lang}", '');
}

function langCanonicalUrl(string $lang): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (BASE_PATH !== '' && str_starts_with($uri, BASE_PATH)) {
        $uri = substr($uri, strlen(BASE_PATH)) ?: '/';
    }
    $path = strtok($uri, '?') ?: '/';
    $query = [];
    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
    if ($lang !== DEFAULT_LANG) {
        $query['lang'] = $lang;
    } else {
        unset($query['lang']);
    }
    $qs = http_build_query($query);
    return rtrim(SITE_URL, '/') . ($path === '/' ? '/' : $path) . ($qs ? '?' . $qs : '');
}

function renderJsonLd(array $data): string
{
    return '<script type="application/ld+json">'
        . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>';
}

function renderSchemaGraph(array $nodes): string
{
    return renderJsonLd(['@context' => 'https://schema.org', '@graph' => $nodes]);
}

function renderWebSiteSchema(): string
{
    return renderJsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => getSetting('site_name', SITE_NAME),
        'url' => rtrim(SITE_URL, '/') . '/',
        'description' => getSeoDescription(),
        'inLanguage' => AVAILABLE_LANGS,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => rtrim(SITE_URL, '/') . '/?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ]);
}

function renderBreadcrumbSchema(array $items): string
{
    $list = [];
    foreach ($items as $i => $item) {
        $list[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => $item['url'] ?? null,
        ];
    }
    return renderJsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list,
    ]);
}

function renderItemListSchema(array $cars, string $name = ''): string
{
    $elements = [];
    foreach (array_slice($cars, 0, 20) as $i => $car) {
        $elements[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => absoluteUrl(carUrl((int)$car['id'], $car['slug'] ?? '')),
            'name' => $car['title'],
        ];
    }
    return renderJsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $name ?: getSeoTitle(),
        'numberOfItems' => count($elements),
        'itemListElement' => $elements,
    ]);
}

function renderNewsArticleSchema(array $article): string
{
    $lang = getCurrentLang();
    return renderJsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => getNewsTitle($article),
        'description' => getNewsExcerpt($article),
        'datePublished' => $article['created_at'],
        'dateModified' => $article['updated_at'] ?? $article['created_at'],
        'author' => ['@type' => 'Organization', 'name' => getSetting('site_name', SITE_NAME)],
        'publisher' => [
            '@type' => 'Organization',
            'name' => getSetting('site_name', SITE_NAME),
            'logo' => ['@type' => 'ImageObject', 'url' => absoluteUrl(BASE_PATH . '/assets/images/logo.png')],
        ],
        'inLanguage' => $lang,
        'mainEntityOfPage' => currentUrl(),
    ]);
}

function renderCarSchema(array $car): string
{
    $images = array_map(fn($img) => absoluteUrl(getCarImageUrl($img['filename'], (int)$car['id'])), $car['images'] ?? []);
    if (empty($images)) {
        $images[] = absoluteUrl(getCarImageUrl(null, (int)$car['id']));
    }
    $carUrl = absoluteUrl(carUrl((int)$car['id'], $car['slug'] ?? ''));

    $vehicle = [
        '@type' => ['Car', 'Product', 'Vehicle'],
        '@id' => $carUrl . '#vehicle',
        'name' => $car['title'],
        'description' => mb_substr(strip_tags(getCarDescription($car)), 0, 300),
        'url' => $carUrl,
        'image' => $images,
        'brand' => ['@type' => 'Brand', 'name' => $car['brand_name']],
        'model' => $car['model_name'],
        'vehicleModelDate' => (string)$car['year'],
        'color' => $car['color'] ?? null,
        'fuelType' => $car['fuel_type'],
        'vehicleTransmission' => $car['transmission'],
        'driveWheelConfiguration' => $car['drive_type'],
        'bodyType' => $car['body_type'],
        'sku' => 'car-' . $car['id'],
        'offers' => [
            '@type' => 'Offer',
            'url' => $carUrl,
            'price' => (int)$car['price_usd'],
            'priceCurrency' => getCurrencyCode(),
            'availability' => 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/UsedCondition',
            'seller' => [
                '@type' => 'AutoDealer',
                'name' => $car['dealer_name'] ?? getSetting('site_name', SITE_NAME),
            ],
        ],
    ];
    if ($car['mileage']) {
        $vehicle['mileageFromOdometer'] = [
            '@type' => 'QuantitativeValue',
            'value' => (int)$car['mileage'],
            'unitCode' => 'KMT',
        ];
    }
    if ($car['vin']) {
        $vehicle['vehicleIdentificationNumber'] = $car['vin'];
    }
    if ($car['city']) {
        $vehicle['offers']['areaServed'] = $car['city'];
    }

    return renderSchemaGraph([$vehicle]);
}

function renderOrganizationSchema(): string
{
    return renderJsonLd([
        '@context' => 'https://schema.org',
        '@type' => ['Organization', 'AutoDealer'],
        '@id' => rtrim(SITE_URL, '/') . '/#organization',
        'name' => getSetting('site_name', SITE_NAME),
        'url' => rtrim(SITE_URL, '/') . '/',
        'logo' => absoluteUrl(BASE_PATH . '/assets/images/logo.png'),
        'description' => getSeoDescription(),
        'areaServed' => [
            ['@type' => 'Country', 'name' => 'Norway'],
            ['@type' => 'Country', 'name' => 'Ukraine'],
            ['@type' => 'Place', 'name' => 'Europe'],
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => getSetting('site_phone'),
            'contactType' => 'customer service',
            'availableLanguage' => AVAILABLE_LANGS,
        ],
        'sameAs' => [
            'https://bilohash.com/',
            'https://bilohash.com/tavle/',
        ],
    ]);
}

function renderSoftwareApplicationSchema(): string
{
    $url = rtrim(SITE_URL, '/') . '/';
    return renderJsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        '@id' => $url . '#software',
        'name' => 'Bilen Auto CMS',
        'applicationCategory' => 'BusinessApplication',
        'applicationSubCategory' => 'Automotive classifieds software',
        'operatingSystem' => 'Web',
        'description' => getSeoDescription(),
        'url' => $url,
        'softwareVersion' => '2026',
        'inLanguage' => ['uk-UA', 'en-US', 'ru-RU', 'nb-NO'],
        'author' => [
            '@type' => 'Person',
            'name' => 'Ruslan Bilohash',
            'url' => 'https://bilohash.com/resume.php',
        ],
        'publisher' => ['@id' => rtrim(SITE_URL, '/') . '/#organization'],
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'EUR',
            'availability' => 'https://schema.org/InStock',
            'url' => 'https://bilohash.com/tavle/',
        ],
        'featureList' => 'Car listings, license plates, special equipment, VIN search, filters, multilingual SEO, public listing submission, llms.txt',
    ]);
}

function llmsTxtUrl(): string
{
    return rtrim(SITE_URL, '/') . '/llms.txt';
}

function rootLlmsTxtUrl(): string
{
    return 'https://bilohash.com/llms.txt';
}

function renderHreflangTags(): string
{
    $html = '';
    foreach (AVAILABLE_LANGS as $lang) {
        $html .= '<link rel="alternate" hreflang="' . $lang . '" href="' . e(langCanonicalUrl($lang)) . '">' . "\n";
    }
    return $html;
}

// ─── Pagination ───────────────────────────────────────────────

function renderPagination(int $currentPage, int $totalPages, array $params = []): string
{
    if ($totalPages <= 1) return '';

    $html = '<nav class="im-pagination" aria-label="Pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $params['page'] = $i;
        if ($i === $currentPage) {
            $html .= '<span class="active">' . $i . '</span>';
        } else {
            $html .= '<a href="?' . http_build_query($params) . '">' . $i . '</a>';
        }
    }
    $html .= '</nav>';
    return $html;
}

// ─── News ───────────────────────────────────────────────────

function getNews(int $limit = 3): array
{
    return Database::fetchAll(
        'SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC LIMIT ?',
        'i', [$limit]
    );
}

function getNewsTitle(array $item): string
{
    $lang = getCurrentLang();
    $key = 'title_' . $lang;
    return $item[$key] ?? $item['title_uk'];
}

function getNewsExcerpt(array $item): string
{
    $lang = getCurrentLang();
    $key = 'excerpt_' . $lang;
    return $item[$key] ?? $item['excerpt_uk'] ?? '';
}

function getRegions(): array
{
    return [
        'Oslo', 'Bergen', 'Trondheim', 'Stavanger',
        'Kyiv', 'Lviv', 'Odesa', 'Kharkiv', 'Dnipro',
        'Berlin', 'Warsaw', 'Stockholm', 'London', 'Hamburg', 'Paris', 'Madrid', 'Rome', 'Vienna', 'Prague', 'Copenhagen', 'Amsterdam',
    ];
}