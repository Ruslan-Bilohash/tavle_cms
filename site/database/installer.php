<?php
/**
 * Bilen CMS - SQLite schema & demo data installer
 */

declare(strict_types=1);

class DatabaseInstaller
{
    public static function ensureInstalled(PDO $db): void
    {
        $exists = $db->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name='users'"
        )->fetch();

        if (!$exists) {
            self::createSchema($db);
            self::seedData($db);
        }

        self::migrate($db);
    }

    public static function migrate(PDO $db): void
    {
        $defaults = [
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'show_secondary_price' => '1',
            'secondary_currency_code' => 'EUR',
            'secondary_currency_rate' => '0.92',
            'nav_top' => json_encode(self::defaultNavTop(), JSON_UNESCAPED_UNICODE),
            'nav_header' => json_encode(self::defaultNavHeader(), JSON_UNESCAPED_UNICODE),
            'seo_keywords_uk' => 'авто норвегія, авто європа, авто україна, оголошення авто, купити авто',
            'seo_keywords_en' => 'car listings norway, car europe, car ukraine, buy car, auto marketplace',
            'seo_keywords_ru' => 'авто норвегия, авто европа, авто украина, объявления авто, купить авто',
            'seo_keywords_no' => 'bilannonser norge, bil europa, bil ukraina, kjøp bil, bilmarkedsplass',
        ];
        $ins = $db->prepare('INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?,?)');
        foreach ($defaults as $k => $v) {
            $ins->execute([$k, $v]);
        }

        $db->exec("UPDATE dealers SET name = 'Global Motors', slug = 'global-motors', description = 'International car dealer' WHERE slug = 'imperiya-motors'");
        $seoUpdates = [
            'site_tagline' => 'Car listings — Norway, Europe, Ukraine',
            'seo_title_uk' => 'Bilen Auto — оголошення авто Норвегія, Європа, Україна',
            'seo_title_en' => 'Bilen Auto — Car Listings Norway, Europe, Ukraine',
            'seo_title_ru' => 'Bilen Auto — объявления авто Норвегия, Европа, Украина',
            'seo_title_no' => 'Bilen Auto — Bilannonser Norge, Europa, Ukraina',
            'seo_description_uk' => 'Система управління оголошеннями авто для Норвегії, Європи та України. Повне SEO, 4 мови.',
            'seo_description_en' => 'Car listings management for Norway, Europe and Ukraine. Full SEO, 4 languages.',
            'seo_description_ru' => 'Система управления объявлениями авто для Норвегии, Европы и Украины. Полное SEO, 4 языка.',
            'seo_description_no' => 'Bilannonser for Norge, Europa og Ukraina. Full SEO, 4 språk.',
        ];
        $upd = $db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        foreach ($seoUpdates as $k => $v) {
            $upd->execute([$v, $k]);
        }
        $db->exec("UPDATE settings SET setting_value = REPLACE(setting_value, 'в Україні', '') WHERE setting_key LIKE 'seo_%'");
        $db->exec("UPDATE settings SET setting_value = REPLACE(setting_value, 'in Ukraine', '') WHERE setting_key LIKE 'seo_%'");

        self::migrateDemoPhotos($db);
        self::migrateListingTypes($db);
        self::migrateNavUrls($db);
        self::migrateUserOwnership($db);
        self::migrateDemoDealer($db);
        self::migrateListingDrafts($db);
    }

    private static function migrateListingDrafts(PDO $db): void
    {
        $cols = $db->query('PRAGMA table_info(cars)')->fetchAll(PDO::FETCH_ASSOC);
        $names = array_column($cols, 'name');
        if (!in_array('is_draft', $names, true)) {
            $db->exec('ALTER TABLE cars ADD COLUMN is_draft INTEGER NOT NULL DEFAULT 0');
        }
    }

    private static function migrateUserOwnership(PDO $db): void
    {
        $cols = $db->query('PRAGMA table_info(cars)')->fetchAll(PDO::FETCH_ASSOC);
        $names = array_column($cols, 'name');
        if (!in_array('user_id', $names, true)) {
            $db->exec('ALTER TABLE cars ADD COLUMN user_id INTEGER REFERENCES users(id) ON DELETE SET NULL');
        }
    }

    private static function migrateDemoDealer(PDO $db): void
    {
        $db->exec("UPDATE dealers SET
            name = 'Global Motors',
            slug = 'global-motors',
            description = 'Demo car dealership — Norway & Europe. Import, trade-in, leasing.',
            phone = '+47 12 34 56 78',
            email = 'sales@global-motors.demo',
            address = 'Strømsø torg 8, 3044 Drammen, Norway',
            region = 'Viken',
            is_verified = 1
            WHERE slug = 'global-motors' OR slug = 'imperiya-motors'");
        $ins = $db->prepare('INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?,?)');
        $ins->execute(['demo_company_hours', 'Mon–Fri 09:00–18:00 · Sat 10:00–15:00']);
    }

    private static function migrateNavUrls(PDO $db): void
    {
        $row = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'nav_top'")->fetchColumn();
        if (!$row) {
            return;
        }
        $updated = str_replace(
            ['plates.php', 'special.php'],
            ['plates/', 'special/'],
            (string)$row
        );
        if ($updated !== $row) {
            $db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?')
                ->execute([$updated, 'nav_top']);
        }
    }

    private static function migrateListingTypes(PDO $db): void
    {
        $cols = $db->query('PRAGMA table_info(cars)')->fetchAll(PDO::FETCH_ASSOC);
        $names = array_column($cols, 'name');
        if (!in_array('listing_type', $names, true)) {
            $db->exec("ALTER TABLE cars ADD COLUMN listing_type TEXT NOT NULL DEFAULT 'car'");
        }
        if (!in_array('plate_number', $names, true)) {
            $db->exec('ALTER TABLE cars ADD COLUMN plate_number TEXT');
        }
        $db->exec("UPDATE cars SET listing_type = 'car' WHERE listing_type IS NULL OR listing_type = ''");
        $db->exec('CREATE INDEX IF NOT EXISTS idx_cars_listing_type ON cars(listing_type)');

        $navTop = json_encode(self::defaultNavTop(), JSON_UNESCAPED_UNICODE);
        $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('nav_top', ?)
            ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value")
            ->execute([$navTop]);

        self::migratePlatesSpecialSeed($db);
    }

    private static function migratePlatesSpecialSeed(PDO $db): void
    {
        $targetVersion = 2;
        $row = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'plates_special_seed_version'")->fetchColumn();
        $version = (int)($row ?: 0);
        if ($version >= $targetVersion) {
            return;
        }

        self::seedPlatesAndSpecial($db);
        $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('plates_special_seed_version', ?)
            ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value")
            ->execute([(string)$targetVersion]);
    }

    public static function seedPlatesAndSpecial(PDO $db): void
    {
        $seed = require __DIR__ . '/seed-plates-special.php';
        $dealerId = (int)($db->query('SELECT id FROM dealers ORDER BY id LIMIT 1')->fetchColumn() ?: 1);

        $brandId = $db->query("SELECT id FROM brands WHERE slug = 'license-plates'")->fetchColumn();
        if (!$brandId) {
            $db->prepare('INSERT INTO brands (name, slug, sort_order) VALUES (?,?,?)')
                ->execute([$seed['brand_plates']['name'], $seed['brand_plates']['slug'], 99]);
            $brandId = (int)$db->lastInsertId();
        } else {
            $brandId = (int)$brandId;
        }

        $modelIds = [];
        $modelStmt = $db->prepare('INSERT OR IGNORE INTO models (brand_id, name, slug) VALUES (?,?,?)');
        $findModel = $db->prepare('SELECT id FROM models WHERE brand_id = ? AND slug = ?');
        foreach ($seed['plate_models'] as $modelName) {
            $slug = strtolower(str_replace(' ', '-', $modelName));
            $modelStmt->execute([$brandId, $modelName, $slug]);
            $findModel->execute([$brandId, $slug]);
            $modelIds[$modelName] = (int)$findModel->fetchColumn();
        }

        $plateStmt = $db->prepare(
            'INSERT INTO cars (brand_id, model_id, dealer_id, title, slug, year, price_usd, mileage,
                body_type, transmission, fuel_type, drive_type, color, region, city, plate_number,
                listing_type, description_uk, description_en, description_ru, description_no, is_featured, is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)'
        );

        foreach ($seed['plates'] as $p) {
            $exists = $db->prepare('SELECT id FROM cars WHERE slug = ?');
            $exists->execute([$p['slug']]);
            if ($exists->fetchColumn()) {
                continue;
            }
            $modelId = $modelIds[$p['model']] ?? reset($modelIds);
            $title = $p['plate'] . ' — ' . $p['model'];
            $plateStmt->execute([
                $brandId, $modelId, $dealerId, $title, $p['slug'], $p['year'], $p['price'], 0,
                'sedan', 'automatic', 'petrol', 'fwd', 'White', $p['region'], $p['city'], $p['plate'],
                'plate', $p['desc']['uk'], $p['desc']['en'], $p['desc']['ru'], $p['desc']['no'], $p['featured'],
            ]);
            $carId = (int)$db->lastInsertId();
            $db->prepare('INSERT INTO car_images (car_id, filename, is_main) VALUES (?, ?, 1)')
                ->execute([$carId, '/assets/images/plates/plate-default.svg']);
        }

        $brandMap = [];
        $brandStmt = $db->prepare('INSERT OR IGNORE INTO brands (name, slug, sort_order) VALUES (?,?,?)');
        $findBrand = $db->prepare('SELECT id FROM brands WHERE slug = ?');
        foreach ($seed['special_brands'] as $i => [$name, $slug]) {
            $brandStmt->execute([$name, $slug, 100 + $i]);
            $findBrand->execute([$slug]);
            $brandMap[$name] = (int)$findBrand->fetchColumn();
        }

        $specStmt = $db->prepare(
            'INSERT INTO cars (brand_id, model_id, dealer_id, title, slug, year, price_usd, mileage,
                body_type, transmission, fuel_type, engine_power, drive_type, color, region, city,
                listing_type, description_uk, description_en, description_ru, description_no, is_featured, is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)'
        );
        $modelIns = $db->prepare('INSERT OR IGNORE INTO models (brand_id, name, slug) VALUES (?,?,?)');
        $findSpecModel = $db->prepare('SELECT id FROM models WHERE brand_id = ? AND slug = ?');

        foreach ($seed['special'] as $s) {
            $exists = $db->prepare('SELECT id FROM cars WHERE slug = ?');
            $exists->execute([$s['slug']]);
            if ($exists->fetchColumn()) {
                continue;
            }
            $bId = $brandMap[$s['brand']] ?? 1;
            $mSlug = strtolower(str_replace([' ', '.'], ['-', ''], $s['model']));
            $modelIns->execute([$bId, $s['model'], $mSlug]);
            $findSpecModel->execute([$bId, $mSlug]);
            $mId = (int)$findSpecModel->fetchColumn();
            $title = $s['brand'] . ' ' . $s['model'] . ' ' . $s['year'];
            $specStmt->execute([
                $bId, $mId, $dealerId, $title, $s['slug'], $s['year'], $s['price'], $s['hours'],
                $s['body'], 'automatic', 'diesel', 0, 'awd', 'Yellow', $s['region'], $s['city'],
                'special', $s['desc']['uk'], $s['desc']['en'], $s['desc']['ru'], $s['desc']['no'], $s['featured'],
            ]);
            $carId = (int)$db->lastInsertId();
            $db->prepare('INSERT INTO car_images (car_id, filename, is_main) VALUES (?, ?, 1)')
                ->execute([$carId, $s['img']]);
        }
    }

    private static function migrateDemoPhotos(PDO $db): void
    {
        $row = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'demo_photos_version'")->fetch();
        $version = (int)($row['setting_value'] ?? 0);
        if ($version >= 2) {
            return;
        }

        $carCount = (int)$db->query('SELECT COUNT(*) FROM cars')->fetchColumn();
        if ($carCount < 1) {
            return;
        }

        self::assignDemoPhotos($db);
        $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('demo_photos_version', '2')
            ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value")->execute();
    }

    private static function assignDemoPhotos(PDO $db): void
    {
        $photoMap = require __DIR__ . '/demo-photos.php';
        $findCar = $db->prepare('SELECT id FROM cars WHERE slug = ? LIMIT 1');
        $deleteImages = $db->prepare('DELETE FROM car_images WHERE car_id = ?');
        $imgStmt = $db->prepare('INSERT INTO car_images (car_id, filename, sort_order, is_main) VALUES (?,?,?,?)');

        foreach ($photoMap as $slug => $urls) {
            $findCar->execute([$slug]);
            $car = $findCar->fetch(PDO::FETCH_ASSOC);
            if (!$car) {
                continue;
            }
            $carId = (int)$car['id'];
            $deleteImages->execute([$carId]);
            foreach ($urls as $j => $url) {
                $imgStmt->execute([$carId, $url, $j, $j === 0 ? 1 : 0]);
            }
        }
    }

    private static function defaultNavTop(): array
    {
        return [
            ['url' => '', 'label' => ['uk' => 'Автомобілі', 'en' => 'Cars', 'ru' => 'Автомобили', 'no' => 'Biler']],
            ['url' => 'plates/', 'label' => ['uk' => 'Номерні знаки', 'en' => 'Plates', 'ru' => 'Номера', 'no' => 'Skilt']],
            ['url' => 'special/', 'label' => ['uk' => 'Спецтехніка', 'en' => 'Special', 'ru' => 'Спецтехника', 'no' => 'Spesial']],
            ['url' => 'news.php', 'label' => ['uk' => 'Новини', 'en' => 'News', 'ru' => 'Новости', 'no' => 'Nyheter']],
        ];
    }

    private static function defaultNavHeader(): array
    {
        return [
            ['url' => 'news.php', 'label' => ['uk' => 'Новини', 'en' => 'News', 'ru' => 'Новости', 'no' => 'Nyheter']],
        ];
    }

    public static function reinstall(PDO $db): void
    {
        $tables = ['car_images', 'cars', 'models', 'brands', 'news', 'dealers', 'settings', 'users'];
        foreach ($tables as $table) {
            $db->exec("DROP TABLE IF EXISTS {$table}");
        }
        self::createSchema($db);
        self::seedData($db);
    }

    private static function createSchema(PDO $db): void
    {
        $db->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT "user" CHECK(role IN ("admin","dealer","user")),
                name TEXT NOT NULL,
                phone TEXT,
                avatar TEXT,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE brands (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                logo TEXT,
                sort_order INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE models (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                brand_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                slug TEXT NOT NULL,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
                UNIQUE(brand_id, slug)
            );

            CREATE TABLE dealers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                description TEXT,
                phone TEXT,
                email TEXT,
                address TEXT,
                region TEXT,
                logo TEXT,
                is_verified INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            );

            CREATE TABLE cars (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                brand_id INTEGER NOT NULL,
                model_id INTEGER NOT NULL,
                dealer_id INTEGER,
                user_id INTEGER,
                title TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                year INTEGER NOT NULL,
                price_usd INTEGER NOT NULL,
                price_old_usd INTEGER,
                mileage INTEGER,
                body_type TEXT NOT NULL DEFAULT "sedan",
                transmission TEXT NOT NULL DEFAULT "automatic",
                fuel_type TEXT NOT NULL DEFAULT "petrol",
                engine_volume REAL,
                engine_power INTEGER,
                drive_type TEXT NOT NULL DEFAULT "fwd",
                color TEXT,
                region TEXT,
                city TEXT,
                vin TEXT,
                vin_verified INTEGER NOT NULL DEFAULT 0,
                is_leasing INTEGER NOT NULL DEFAULT 0,
                is_exchange INTEGER NOT NULL DEFAULT 0,
                is_new INTEGER NOT NULL DEFAULT 0,
                is_en_route INTEGER NOT NULL DEFAULT 0,
                is_on_order INTEGER NOT NULL DEFAULT 0,
                condition_type TEXT NOT NULL DEFAULT "used",
                generation TEXT,
                description_uk TEXT,
                description_en TEXT,
                description_ru TEXT,
                description_no TEXT,
                views INTEGER NOT NULL DEFAULT 0,
                is_featured INTEGER NOT NULL DEFAULT 0,
                listing_type TEXT NOT NULL DEFAULT "car",
                plate_number TEXT,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (brand_id) REFERENCES brands(id),
                FOREIGN KEY (model_id) REFERENCES models(id),
                FOREIGN KEY (dealer_id) REFERENCES dealers(id) ON DELETE SET NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            );

            CREATE INDEX idx_cars_price ON cars(price_usd);
            CREATE INDEX idx_cars_year ON cars(year);
            CREATE INDEX idx_cars_active ON cars(is_active);
            CREATE INDEX idx_cars_brand_model ON cars(brand_id, model_id);
            CREATE INDEX idx_cars_listing_type ON cars(listing_type);

            CREATE TABLE car_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                car_id INTEGER NOT NULL,
                filename TEXT NOT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                is_main INTEGER NOT NULL DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
            );

            CREATE TABLE news (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title_uk TEXT NOT NULL,
                title_en TEXT,
                title_ru TEXT,
                title_no TEXT,
                slug TEXT NOT NULL UNIQUE,
                excerpt_uk TEXT,
                excerpt_en TEXT,
                excerpt_ru TEXT,
                excerpt_no TEXT,
                content_uk TEXT,
                content_en TEXT,
                content_ru TEXT,
                content_no TEXT,
                image TEXT,
                is_published INTEGER NOT NULL DEFAULT 1,
                views INTEGER NOT NULL DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT NOT NULL UNIQUE,
                setting_value TEXT,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ');
    }

    private static function seedData(PDO $db): void
    {
        $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
        $userHash = password_hash('user123', PASSWORD_BCRYPT);

        $db->prepare('INSERT INTO users (username, email, password, role, name, phone) VALUES (?,?,?,?,?,?)')
            ->execute(['admin', 'admin@bilen-cms.local', $adminHash, 'admin', 'Bilen Admin', '+380501234567']);
        $db->prepare('INSERT INTO users (username, email, password, role, name, phone) VALUES (?,?,?,?,?,?)')
            ->execute(['dealer1', 'dealer@bilen-cms.local', $userHash, 'dealer', 'Auto Premium', '+380671112233']);
        $db->prepare('INSERT INTO users (username, email, password, role, name, phone) VALUES (?,?,?,?,?,?)')
            ->execute(['user1', 'user@bilen-cms.local', $userHash, 'user', 'Олександр Коваленко', '+380931234567']);

        $brands = [
            ['Audi', 'audi', 1], ['BMW', 'bmw', 2], ['Ford', 'ford', 3], ['Hyundai', 'hyundai', 4],
            ['Kia', 'kia', 5], ['Lexus', 'lexus', 6], ['Mazda', 'mazda', 7], ['Mercedes-Benz', 'mercedes-benz', 8],
            ['Mitsubishi', 'mitsubishi', 9], ['Nissan', 'nissan', 10], ['Porsche', 'porsche', 11], ['Renault', 'renault', 12],
            ['Skoda', 'skoda', 13], ['Tesla', 'tesla', 14], ['Toyota', 'toyota', 15], ['Volkswagen', 'volkswagen', 16],
            ['Infiniti', 'infiniti', 17], ['Jeep', 'jeep', 18], ['Subaru', 'subaru', 19], ['Zeekr', 'zeekr', 20],
        ];
        $stmt = $db->prepare('INSERT INTO brands (name, slug, sort_order) VALUES (?,?,?)');
        foreach ($brands as $b) {
            $stmt->execute($b);
        }

        $models = [
            [1,'A4','a4'],[1,'Q5','q5'],[1,'A6','a6'],
            [2,'3 Series','3-series'],[2,'X3','x3'],[2,'X5','x5'],[2,'5 Series','5-series'],
            [3,'Focus','focus'],[3,'Kuga','kuga'],[3,'Mustang','mustang'],
            [4,'Tucson','tucson'],[4,'Santa Fe','santa-fe'],[4,'Elantra','elantra'],
            [5,'Sportage','sportage'],[5,'Sorento','sorento'],[5,'K5','k5'],
            [6,'RX','rx'],[6,'ES','es'],[6,'NX','nx'],
            [7,'CX-5','cx-5'],[7,'Mazda 3','mazda-3'],[7,'CX-30','cx-30'],
            [8,'C-Class','c-class'],[8,'E-Class','e-class'],[8,'GLC','glc'],[8,'GLE','gle'],
            [9,'Outlander','outlander'],[9,'ASX','asx'],
            [10,'Leaf','leaf'],[10,'Qashqai','qashqai'],[10,'X-Trail','x-trail'],
            [11,'911','911'],[11,'Cayenne','cayenne'],[11,'Macan','macan'],
            [12,'Duster','duster'],[12,'Megane','megane'],
            [13,'Octavia','octavia'],[13,'Superb','superb'],[13,'Kodiaq','kodiaq'],
            [14,'Model 3','model-3'],[14,'Model Y','model-y'],[14,'Model S','model-s'],
            [15,'Camry','camry'],[15,'RAV4','rav4'],[15,'Corolla','corolla'],[15,'Land Cruiser','land-cruiser'],
            [16,'Tiguan','tiguan'],[16,'Passat','passat'],[16,'Golf','golf'],
            [17,'QX50','qx50'],[17,'QX60','qx60'],
            [18,'Cherokee','cherokee'],[18,'Grand Cherokee','grand-cherokee'],
            [19,'Ascent','ascent'],[19,'Forester','forester'],
            [20,'001','001'],[20,'X','x'],
        ];
        $stmt = $db->prepare('INSERT INTO models (brand_id, name, slug) VALUES (?,?,?)');
        foreach ($models as $m) {
            $stmt->execute($m);
        }

        $db->prepare('INSERT INTO dealers (user_id, name, slug, description, phone, email, address, region, is_verified) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([2, 'Auto Premium', 'auto-premium', 'Premium car dealer', '+4712345678', 'dealer@bilen-cms.local', 'Karl Johans gate 1, Oslo', 'Oslo', 1]);
        $db->prepare('INSERT INTO dealers (user_id, name, slug, description, phone, email, address, region, is_verified) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([null, 'Global Motors', 'global-motors', 'Import cars from USA and Europe', '+4930123456', 'info@global-motors.local', 'Friedrichstrasse 10, Berlin', 'Berlin', 1]);
        $db->prepare('INSERT INTO dealers (user_id, name, slug, description, phone, email, address, region, is_verified) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([null, 'CarTrade Europe', 'cartrade-eu', 'Cars on order', '+48221234567', 'sales@cartrade.local', 'Marszałkowska 25, Warsaw', 'Warsaw', 0]);

        $settings = [
            ['site_name', 'Bilen Auto'],
            ['site_tagline', 'Car listings — Norway, Europe, Ukraine'],
            ['site_email', 'info@bilen-cms.local'],
            ['site_phone', '+4712345678'],
            ['usd_rate', '45.05'],
            ['currency_code', 'USD'],
            ['currency_symbol', '$'],
            ['show_secondary_price', '1'],
            ['secondary_currency_code', 'EUR'],
            ['secondary_currency_rate', '0.92'],
            ['nav_top', json_encode(self::defaultNavTop(), JSON_UNESCAPED_UNICODE)],
            ['nav_header', json_encode(self::defaultNavHeader(), JSON_UNESCAPED_UNICODE)],
            ['cookie_consent_enabled', '1'],
            ['cookie_consent_text_uk', 'Ми використовуємо cookies для покращення вашого досвіду.'],
            ['cookie_consent_text_en', 'We use cookies to improve your experience.'],
            ['cookie_consent_text_ru', 'Мы используем cookies для улучшения вашего опыта.'],
            ['cookie_consent_text_no', 'Vi bruker informasjonskapsler for å forbedre opplevelsen din.'],
            ['seo_title_uk', 'Bilen Auto — оголошення авто Норвегія, Європа, Україна'],
            ['seo_title_en', 'Bilen Auto — Car Listings Norway, Europe, Ukraine'],
            ['seo_title_ru', 'Bilen Auto — объявления авто Норвегия, Европа, Украина'],
            ['seo_title_no', 'Bilen Auto — Bilannonser Norge, Europa, Ukraina'],
            ['seo_description_uk', 'Система управління оголошеннями авто для Норвегії, Європи та України. Повне SEO, 4 мови, фільтри, фото, ціни.'],
            ['seo_description_en', 'Car listings management for Norway, Europe and Ukraine. Full SEO, 4 languages, filters, photos, prices.'],
            ['seo_description_ru', 'Система управления объявлениями авто для Норвегии, Европы и Украины. Полное SEO, 4 языка, фильтры, фото, цены.'],
            ['seo_description_no', 'Bilannonser for Norge, Europa og Ukraina. Full SEO, 4 språk, filtre, bilder, priser.'],
            ['seo_keywords_uk', 'авто норвегія, авто європа, авто україна, оголошення авто, купити авто'],
            ['seo_keywords_en', 'car listings norway, car europe, car ukraine, buy car, auto marketplace'],
            ['seo_keywords_ru', 'авто норвегия, авто европа, авто украина, объявления авто, купить авто'],
            ['seo_keywords_no', 'bilannonser norge, bil europa, bil ukraina, kjøp bil, bilmarkedsplass'],
            ['og_image', '/tavle/site/assets/images/og-default.jpg'],
            ['google_analytics', ''],
            ['items_per_page', '12'],
        ];
        $stmt = $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?)');
        foreach ($settings as $s) {
            $stmt->execute($s);
        }

        $news = [
            ['Нові надходження Tesla Model Y', 'New Tesla Model Y arrivals', 'Новые поступления Tesla Model Y', 'Nye Tesla Model Y ankomster', 'new-tesla-model-y', 'Свіжі електромобілі в наявності', 'Fresh electric vehicles in stock', '<p>У нашому каталозі з\'явилися нові Tesla Model Y 2024 року випуску з пробігом від 5 000 км.</p>', '<p>New Tesla Model Y 2024 with mileage from 5,000 km are now available.</p>', 1],
            ['Як перевірити VIN перед покупкою', 'How to check VIN before buying', 'Как проверить VIN перед покупкой', 'Slik sjekker du VIN før kjøp', 'how-to-check-vin', 'Поради від експертів Bilen CMS', 'Tips from Bilen CMS experts', '<p>Перевірка VIN-коду допоможе уникнути шахрайства при купівлі авто з США чи Європи.</p>', '<p>VIN verification helps avoid fraud when buying cars from USA or Europe.</p>', 1],
            ['Тренди авторинку 2026', 'Car market trends 2026', 'Тренды авторынка 2026', 'Biltrend 2026', 'car-market-trends-2026', 'Електромобілі та гібриди лідирують', 'EVs and hybrids lead the market', '<p>У 2026 році попит на електромобілі зріс на 35% порівняно з минулим роком.</p>', '<p>In 2026, demand for electric vehicles grew 35% compared to last year.</p>', 1],
        ];
        $stmt = $db->prepare('INSERT INTO news (title_uk, title_en, title_ru, title_no, slug, excerpt_uk, excerpt_en, content_uk, content_en, is_published) VALUES (?,?,?,?,?,?,?,?,?,?)');
        foreach ($news as $n) {
            $stmt->execute($n);
        }

        $cars = [
            [17,50,2,'Infiniti QX50 2016','infiniti-qx50-2016',2016,14000,15000,134000,'suv','automatic','petrol',3.7,325,'awd','Чорний','Одеська','Одеса','JN1BJ1CP0GM123456',1,0,0,0,'used','I Рестайлинг',1],
            [18,52,1,'Jeep Cherokee 2015','jeep-cherokee-2015',2015,13300,13700,111000,'suv','automatic','petrol',2.4,184,'fwd','Сірий','Київська','Крюківщина','1C4PJLAB8FW789012',1,0,0,0,'used','V (KL)',0],
            [2,4,2,'BMW 3 Series 2020','bmw-3-series-2020',2020,43000,null,72000,'sedan','automatic','petrol',3.0,387,'rwd','Білий','Житомирська','Житомир','WBA5R1C05LFA12345',1,0,0,0,'used','VII (G2x)',1],
            [20,56,3,'Zeekr 001 2024','zeekr-001-2024',2024,42999,null,18000,'liftback','automatic','electric',null,789,'awd','Срібний','Дніпропетровська','Дніпро','L6T78CEE0RE123456',1,0,0,1,'new','I рестайлінг',1],
            [19,54,3,'Subaru Ascent 2023','subaru-ascent-2023',2023,33999,null,37000,'suv','cvt','petrol',2.4,260,'awd','Синій','Дніпропетровська','Дніпро','4S4WMARD8P3123456',1,0,0,0,'like_new','I Рестайлинг',0],
            [6,17,2,'Lexus RX 2021','lexus-rx-2021',2021,37000,null,56000,'suv','automatic','petrol',3.5,300,'fwd','Білий','Одеська','Одеса','2T2BZMCA0MC123456',1,0,0,0,'used','IV Рестайлинг',1],
            [10,29,1,'Nissan Leaf 2016','nissan-leaf-2016',2016,6800,null,223000,'hatchback','automatic','electric',null,109,'fwd','Білий','Рівненська','Рівне','1N4AZ0CP8GC123456',1,0,0,0,'used','I (ZE0)',0],
            [13,38,1,'Skoda Superb 2019','skoda-superb-2019',2019,23500,27900,246000,'liftback','robot','diesel',2.0,190,'awd','Сірий','Львівська','Львів','TMBJJ7NP0K7123456',1,0,1,0,'used','III Рестайлинг',0],
            [6,17,2,'Lexus RX 2015','lexus-rx-2015',2015,19000,null,148000,'suv','automatic','petrol',2.7,188,'fwd','Чорний','Львівська','Червоноград','2T2BK1BA0FC123456',1,0,0,0,'used','III Рестайлинг',0],
            [16,47,3,'Volkswagen Tiguan 2017','volkswagen-tiguan-2017',2017,13300,null,135000,'suv','automatic','petrol',2.0,180,'awd','Червоний','Тернопільська','Тернопіль','WVGZZZ5NZHW123456',1,0,0,0,'used','I Рестайлинг',0],
            [2,5,1,'BMW X3 2015','bmw-x3-2015',2015,17000,null,299000,'suv','automatic','diesel',2.0,190,'awd','Сірий','Рівненська','Рівне','WBAWZ3C55G1234567',1,1,1,0,'used','II Рестайлинг',0],
            [14,40,2,'Tesla Model 3 2022','tesla-model-3-2022',2022,28500,null,45000,'sedan','automatic','electric',null,283,'rwd','Білий','Київська','Київ','5YJ3E1EA0NF123456',1,0,0,0,'like_new','I',1],
            [8,24,2,'Mercedes-Benz E-Class 2019','mercedes-e-class-2019',2019,38500,null,89000,'sedan','automatic','diesel',2.0,194,'rwd','Чорний','Київська','Київ','WDDZF4JB0KA123456',1,0,0,0,'used','W213',0],
            [15,44,1,'Toyota RAV4 2021','toyota-rav4-2021',2021,26500,null,67000,'crossover','automatic','hybrid',2.5,219,'awd','Срібний','Київська','Бровари','JTMB1RFV0MD123456',1,0,0,0,'used','V (XA50)',1],
            [11,33,2,'Porsche Cayenne 2018','porsche-cayenne-2018',2018,52000,null,112000,'suv','automatic','petrol',3.0,340,'awd','Білий','Одеська','Одеса','WP1ZZZ9YZKDA12345',1,0,0,0,'used','III (9YA)',1],
            [4,11,3,'Hyundai Tucson 2023','hyundai-tucson-2023',2023,24500,null,28000,'crossover','automatic','petrol',1.6,180,'awd','Сірий','Львівська','Львів','KM8JCCA18PU123456',1,0,0,1,'new','IV (NX4)',0],
            [5,14,1,'Kia Sportage 2022','kia-sportage-2022',2022,22000,null,52000,'crossover','automatic','petrol',2.0,150,'awd','Зелений','Харківська','Харків','U5YHMC815NL123456',1,0,0,0,'used','V (NQ5)',0],
            [1,2,2,'Audi Q5 2020','audi-q5-2020',2020,35000,null,78000,'suv','automatic','diesel',2.0,190,'awd','Чорний','Київська','Київ','WA1BNAFY0L2123456',1,0,0,0,'used','FY',1],
        ];

        $descriptions = require __DIR__ . '/demo-descriptions.php';
        $stmt = $db->prepare('INSERT INTO cars (brand_id, model_id, dealer_id, title, slug, year, price_usd, price_old_usd, mileage, body_type, transmission, fuel_type, engine_volume, engine_power, drive_type, color, region, city, vin, vin_verified, is_leasing, is_exchange, is_new, condition_type, generation, description_uk, description_en, description_ru, description_no, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($cars as $c) {
            $slug = $c[4];
            $desc = $descriptions[$slug] ?? ['uk' => '', 'en' => '', 'ru' => '', 'no' => ''];
            $stmt->execute([
                ...array_slice($c, 0, 25),
                $desc['uk'],
                $desc['en'],
                $desc['ru'],
                $desc['no'],
                $c[25],
            ]);
        }

        self::assignDemoPhotos($db);
        self::seedPlatesAndSpecial($db);
        $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('demo_photos_version', '2')
            ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value")->execute();
        $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('plates_special_seed_version', '2')
            ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value")->execute();
    }
}