-- LEGACY: MySQL dump (не використовується на продакшені)
-- Production використовує SQLite: data/bilen.sqlite (авто-створення через database/installer.php)
-- Bilen CMS Database Schema & Demo Data
-- PHP 8+ / MySQL 8+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS bilen_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bilen_cms;

DROP TABLE IF EXISTS car_images;
DROP TABLE IF EXISTS cars;
DROP TABLE IF EXISTS models;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS dealers;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','dealer','user') NOT NULL DEFAULT 'user',
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE brands (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL UNIQUE,
    logo VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE models (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_id INT UNSIGNED NOT NULL,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    UNIQUE KEY brand_model (brand_id, slug)
) ENGINE=InnoDB;

CREATE TABLE dealers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    phone VARCHAR(30) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    region VARCHAR(100) DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE cars (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_id INT UNSIGNED NOT NULL,
    model_id INT UNSIGNED NOT NULL,
    dealer_id INT UNSIGNED DEFAULT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    year SMALLINT UNSIGNED NOT NULL,
    price_usd INT UNSIGNED NOT NULL,
    price_old_usd INT UNSIGNED DEFAULT NULL,
    mileage INT UNSIGNED DEFAULT NULL,
    body_type ENUM('sedan','suv','hatchback','wagon','coupe','minivan','pickup','liftback','crossover') NOT NULL DEFAULT 'sedan',
    transmission ENUM('manual','automatic','robot','cvt') NOT NULL DEFAULT 'automatic',
    fuel_type ENUM('petrol','diesel','electric','hybrid','gas') NOT NULL DEFAULT 'petrol',
    engine_volume DECIMAL(3,1) DEFAULT NULL,
    engine_power INT UNSIGNED DEFAULT NULL,
    drive_type ENUM('fwd','rwd','awd') NOT NULL DEFAULT 'fwd',
    color VARCHAR(50) DEFAULT NULL,
    region VARCHAR(100) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    vin VARCHAR(17) DEFAULT NULL,
    vin_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_leasing TINYINT(1) NOT NULL DEFAULT 0,
    is_exchange TINYINT(1) NOT NULL DEFAULT 0,
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    is_en_route TINYINT(1) NOT NULL DEFAULT 0,
    is_on_order TINYINT(1) NOT NULL DEFAULT 0,
    condition_type ENUM('new','like_new','used') NOT NULL DEFAULT 'used',
    generation VARCHAR(100) DEFAULT NULL,
    description_uk TEXT,
    description_en TEXT,
    description_ru TEXT,
    description_no TEXT,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id),
    FOREIGN KEY (model_id) REFERENCES models(id),
    FOREIGN KEY (dealer_id) REFERENCES dealers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_price (price_usd),
    INDEX idx_year (year),
    INDEX idx_active (is_active),
    INDEX idx_brand_model (brand_id, model_id)
) ENGINE=InnoDB;

CREATE TABLE car_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    car_id INT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_main TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title_uk VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) DEFAULT NULL,
    title_ru VARCHAR(255) DEFAULT NULL,
    title_no VARCHAR(255) DEFAULT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt_uk TEXT,
    excerpt_en TEXT,
    excerpt_ru TEXT,
    excerpt_no TEXT,
    content_uk TEXT,
    content_en TEXT,
    content_ru TEXT,
    content_no TEXT,
    image VARCHAR(255) DEFAULT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Users (password: admin123 / user123)
INSERT INTO users (username, email, password, role, name, phone) VALUES
('admin', 'admin@bilen-cms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Bilen Admin', '+380501234567'),
('dealer1', 'dealer@bilen-cms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dealer', 'Auto Premium', '+380671112233'),
('user1', 'user@bilen-cms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Олександр Коваленко', '+380931234567');

-- Brands
INSERT INTO brands (name, slug, sort_order) VALUES
('Audi', 'audi', 1),
('BMW', 'bmw', 2),
('Ford', 'ford', 3),
('Hyundai', 'hyundai', 4),
('Kia', 'kia', 5),
('Lexus', 'lexus', 6),
('Mazda', 'mazda', 7),
('Mercedes-Benz', 'mercedes-benz', 8),
('Mitsubishi', 'mitsubishi', 9),
('Nissan', 'nissan', 10),
('Porsche', 'porsche', 11),
('Renault', 'renault', 12),
('Skoda', 'skoda', 13),
('Tesla', 'tesla', 14),
('Toyota', 'toyota', 15),
('Volkswagen', 'volkswagen', 16),
('Infiniti', 'infiniti', 17),
('Jeep', 'jeep', 18),
('Subaru', 'subaru', 19),
('Zeekr', 'zeekr', 20);

-- Models
INSERT INTO models (brand_id, name, slug) VALUES
(1, 'A4', 'a4'), (1, 'Q5', 'q5'), (1, 'A6', 'a6'),
(2, '3 Series', '3-series'), (2, 'X3', 'x3'), (2, 'X5', 'x5'), (2, '5 Series', '5-series'),
(3, 'Focus', 'focus'), (3, 'Kuga', 'kuga'), (3, 'Mustang', 'mustang'),
(4, 'Tucson', 'tucson'), (4, 'Santa Fe', 'santa-fe'), (4, 'Elantra', 'elantra'),
(5, 'Sportage', 'sportage'), (5, 'Sorento', 'sorento'), (5, 'K5', 'k5'),
(6, 'RX', 'rx'), (6, 'ES', 'es'), (6, 'NX', 'nx'),
(7, 'CX-5', 'cx-5'), (7, 'Mazda 3', 'mazda-3'), (7, 'CX-30', 'cx-30'),
(8, 'C-Class', 'c-class'), (8, 'E-Class', 'e-class'), (8, 'GLC', 'glc'), (8, 'GLE', 'gle'),
(9, 'Outlander', 'outlander'), (9, 'ASX', 'asx'),
(10, 'Leaf', 'leaf'), (10, 'Qashqai', 'qashqai'), (10, 'X-Trail', 'x-trail'),
(11, '911', '911'), (11, 'Cayenne', 'cayenne'), (11, 'Macan', 'macan'),
(12, 'Duster', 'duster'), (12, 'Megane', 'megane'),
(13, 'Octavia', 'octavia'), (13, 'Superb', 'superb'), (13, 'Kodiaq', 'kodiaq'),
(14, 'Model 3', 'model-3'), (14, 'Model Y', 'model-y'), (14, 'Model S', 'model-s'),
(15, 'Camry', 'camry'), (15, 'RAV4', 'rav4'), (15, 'Corolla', 'corolla'), (15, 'Land Cruiser', 'land-cruiser'),
(16, 'Tiguan', 'tiguan'), (16, 'Passat', 'passat'), (16, 'Golf', 'golf'),
(17, 'QX50', 'qx50'), (17, 'QX60', 'qx60'),
(18, 'Cherokee', 'cherokee'), (18, 'Grand Cherokee', 'grand-cherokee'),
(19, 'Ascent', 'ascent'), (19, 'Forester', 'forester'),
(20, '001', '001'), (20, 'X', 'x');

-- Dealers
INSERT INTO dealers (user_id, name, slug, description, phone, email, address, region, is_verified) VALUES
(2, 'Auto Premium', 'auto-premium', 'Офіційний дилер преміум авто в Україні', '+380671112233', 'dealer@bilen-cms.local', 'вул. Хрещатик 1, Київ', 'Київська', 1),
(NULL, 'Імперія Моторс', 'imperiya-motors', 'Продаж авто з США та Європи', '+380441112233', 'info@imperiya-motors.local', 'вул. Дерибасівська 10, Одеса', 'Одеська', 1),
(NULL, 'CarTrade UA', 'cartrade-ua', 'Автомобілі під замовлення', '+380322223344', 'sales@cartrade.local', 'вул. Свободи 25, Львів', 'Львівська', 0);

-- Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Bilen CMS'),
('site_tagline', 'Продаж автомобілів в Україні'),
('site_email', 'info@bilen-cms.local'),
('site_phone', '+380441234567'),
('usd_rate', '45.05'),
('cookie_consent_enabled', '1'),
('cookie_consent_text_uk', 'Ми використовуємо cookies для покращення вашого досвіду.'),
('cookie_consent_text_en', 'We use cookies to improve your experience.'),
('cookie_consent_text_ru', 'Мы используем cookies для улучшения вашего опыта.'),
('cookie_consent_text_no', 'Vi bruker informasjonskapsler for å forbedre opplevelsen din.'),
('seo_title_uk', 'Bilen CMS — купівля та продаж авто в Україні'),
('seo_title_en', 'Bilen CMS — Buy and Sell Cars in Ukraine'),
('seo_title_ru', 'Bilen CMS — покупка и продажа авто в Украине'),
('seo_title_no', 'Bilen CMS — kjøp og salg av biler i Ukraina'),
('seo_description_uk', 'Платформа для купівлі та продажу автомобілів. Зручний пошук, фільтри, фото та ціни.'),
('seo_description_en', 'Platform for buying and selling cars. Easy search, filters, photos and prices.'),
('seo_description_ru', 'Платформа для покупки и продажи автомобилей. Удобный поиск, фильтры, фото и цены.'),
('seo_description_no', 'Plattform for kjøp og salg av biler. Enkel søk, filtre, bilder og priser.'),
('og_image', '/assets/images/og-default.jpg'),
('google_analytics', ''),
('items_per_page', '12');

-- News
INSERT INTO news (title_uk, title_en, title_ru, title_no, slug, excerpt_uk, excerpt_en, content_uk, content_en, is_published) VALUES
('Нові надходження Tesla Model Y', 'New Tesla Model Y arrivals', 'Новые поступления Tesla Model Y', 'Nye Tesla Model Y ankomster', 'new-tesla-model-y', 'Свіжі електромобілі в наявності', 'Fresh electric vehicles in stock', '<p>У нашому каталозі з\'явилися нові Tesla Model Y 2024 року випуску з пробігом від 5 000 км.</p>', '<p>New Tesla Model Y 2024 with mileage from 5,000 km are now available.</p>', 1),
('Як перевірити VIN перед покупкою', 'How to check VIN before buying', 'Как проверить VIN перед покупкой', 'Slik sjekker du VIN før kjøp', 'how-to-check-vin', 'Поради від експертів Bilen CMS', 'Tips from Bilen CMS experts', '<p>Перевірка VIN-коду допоможе уникнути шахрайства при купівлі авто з США чи Європи.</p>', '<p>VIN verification helps avoid fraud when buying cars from USA or Europe.</p>', 1),
('Тренди авторинку 2026', 'Car market trends 2026', 'Тренды авторынка 2026', 'Biltrend 2026', 'car-market-trends-2026', 'Електромобілі та гібриди лідирують', 'EVs and hybrids lead the market', '<p>У 2026 році попит на електромобілі зріс на 35% порівняно з минулим роком.</p>', '<p>In 2026, demand for electric vehicles grew 35% compared to last year.</p>', 1);

-- Demo Cars (18 listings)
INSERT INTO cars (brand_id, model_id, dealer_id, title, slug, year, price_usd, price_old_usd, mileage, body_type, transmission, fuel_type, engine_volume, engine_power, drive_type, color, region, city, vin, vin_verified, is_leasing, is_exchange, is_new, condition_type, generation, description_uk, description_en, is_featured) VALUES
(17, 44, 2, 'Infiniti QX50 2016', 'infiniti-qx50-2016', 2016, 14000, 15000, 134000, 'suv', 'automatic', 'petrol', 3.7, 325, 'awd', 'Чорний', 'Одеська', 'Одеса', 'JN1BJ1CP0GM123456', 1, 0, 0, 0, 'used', 'I Рестайлинг', 'Infiniti QX50 у відмінному стані, повний привід, бензин 3.7 л.', 'Infiniti QX50 in excellent condition, AWD, 3.7L petrol.', 1),
(18, 46, 1, 'Jeep Cherokee 2015', 'jeep-cherokee-2015', 2015, 13300, 13700, 111000, 'suv', 'automatic', 'petrol', 2.4, 184, 'fwd', 'Сірий', 'Київська', 'Крюківщина', '1C4PJLAB8FW789012', 1, 0, 0, 0, 'used', 'V (KL)', 'Jeep Cherokee 2015, автомат, передній привід.', 'Jeep Cherokee 2015, automatic, FWD.', 0),
(2, 4, 2, 'BMW 3 Series 2020', 'bmw-3-series-2020', 2020, 43000, NULL, 72000, 'sedan', 'automatic', 'petrol', 3.0, 387, 'rwd', 'Білий', 'Житомирська', 'Житомир', 'WBA5R1C05LFA12345', 1, 0, 0, 0, 'used', 'VII (G2x)', 'BMW 3 Series M340i, потужний седан у ідеальному стані.', 'BMW 3 Series M340i, powerful sedan in perfect condition.', 1),
(20, 52, 3, 'Zeekr 001 2024', 'zeekr-001-2024', 2024, 42999, NULL, 18000, 'liftback', 'automatic', 'electric', NULL, 789, 'awd', 'Срібний', 'Дніпропетровська', 'Дніпро', 'L6T78CEE0RE123456', 1, 0, 0, 1, 'new', 'I рестайлінг', 'Zeekr 001 електро, запас ходу ~705 км.', 'Zeekr 001 electric, range ~705 km.', 1),
(19, 50, 3, 'Subaru Ascent 2023', 'subaru-ascent-2023', 2023, 33999, NULL, 37000, 'suv', 'cvt', 'petrol', 2.4, 260, 'awd', 'Синій', 'Дніпропетровська', 'Дніпро', '4S4WMARD8P3123456', 1, 0, 0, 0, 'like_new', 'I Рестайлинг', 'Subaru Ascent 7-місний SUV, повний привід.', 'Subaru Ascent 7-seat SUV, AWD.', 0),
(6, 16, 2, 'Lexus RX 2021', 'lexus-rx-2021', 2021, 37000, NULL, 56000, 'suv', 'automatic', 'petrol', 3.5, 300, 'fwd', 'Білий', 'Одеська', 'Одеса', '2T2BZMCA0MC123456', 1, 0, 0, 0, 'used', 'IV (AL20) Рестайлинг', 'Lexus RX 350, преміум комплектація.', 'Lexus RX 350, premium trim.', 1),
(10, 30, 1, 'Nissan Leaf 2016', 'nissan-leaf-2016', 2016, 6800, NULL, 223000, 'hatchback', 'automatic', 'electric', NULL, 109, 'fwd', 'Білий', 'Рівненська', 'Рівне', '1N4AZ0CP8GC123456', 1, 0, 0, 0, 'used', 'I (ZE0)', 'Nissan Leaf електро, економічний міський авто.', 'Nissan Leaf electric, economical city car.', 0),
(13, 37, 1, 'Skoda Superb 2019', 'skoda-superb-2019', 2019, 23500, 27900, 246000, 'liftback', 'robot', 'diesel', 2.0, 190, 'awd', 'Сірий', 'Львівська', 'Львів', 'TMBJJ7NP0K7123456', 1, 0, 1, 0, 'used', 'III Рестайлинг', 'Skoda Superb дизель, обмін можливий.', 'Skoda Superb diesel, exchange possible.', 0),
(6, 16, 2, 'Lexus RX 2015', 'lexus-rx-2015', 2015, 19000, NULL, 148000, 'suv', 'automatic', 'petrol', 2.7, 188, 'fwd', 'Чорний', 'Львівська', 'Червоноград', '2T2BK1BA0FC123456', 1, 0, 0, 0, 'used', 'III (AL10) Рестайлинг', 'Lexus RX 270, ГБО, надійний кросовер.', 'Lexus RX 270, LPG, reliable crossover.', 0),
(16, 43, 3, 'Volkswagen Tiguan 2017', 'volkswagen-tiguan-2017', 2017, 13300, NULL, 135000, 'suv', 'automatic', 'petrol', 2.0, 180, 'awd', 'Червоний', 'Тернопільська', 'Тернопіль', 'WVGZZZ5NZHW123456', 1, 0, 0, 0, 'used', 'I Рестайлинг', 'VW Tiguan 4Motion, повний привід.', 'VW Tiguan 4Motion, AWD.', 0),
(2, 5, 1, 'BMW X3 2015', 'bmw-x3-2015', 2015, 17000, NULL, 299000, 'suv', 'automatic', 'diesel', 2.0, 190, 'awd', 'Сірий', 'Рівненська', 'Рівне', 'WBAWZ3C55G1234567', 1, 1, 1, 0, 'used', 'II (F25) Рестайлинг', 'BMW X3 дизель, лізинг та обмін.', 'BMW X3 diesel, leasing and exchange.', 0),
(14, 39, 2, 'Tesla Model 3 2022', 'tesla-model-3-2022', 2022, 28500, NULL, 45000, 'sedan', 'automatic', 'electric', NULL, 283, 'rwd', 'Білий', 'Київська', 'Київ', '5YJ3E1EA0NF123456', 1, 0, 0, 0, 'like_new', 'I', 'Tesla Model 3 Long Range, Autopilot.', 'Tesla Model 3 Long Range, Autopilot.', 1),
(8, 22, 2, 'Mercedes-Benz E-Class 2019', 'mercedes-e-class-2019', 2019, 38500, NULL, 89000, 'sedan', 'automatic', 'diesel', 2.0, 194, 'rwd', 'Чорний', 'Київська', 'Київ', 'WDDZF4JB0KA123456', 1, 0, 0, 0, 'used', 'W213', 'Mercedes E220d, бізнес-клас.', 'Mercedes E220d, business class.', 0),
(15, 41, 1, 'Toyota RAV4 2021', 'toyota-rav4-2021', 2021, 26500, NULL, 67000, 'crossover', 'automatic', 'hybrid', 2.5, 219, 'awd', 'Срібний', 'Київська', 'Бровари', 'JTMB1RFV0MD123456', 1, 0, 0, 0, 'used', 'V (XA50)', 'Toyota RAV4 Hybrid AWD, економічний SUV.', 'Toyota RAV4 Hybrid AWD, economical SUV.', 1),
(11, 33, 2, 'Porsche Cayenne 2018', 'porsche-cayenne-2018', 2018, 52000, NULL, 112000, 'suv', 'automatic', 'petrol', 3.0, 340, 'awd', 'Білий', 'Одеська', 'Одеса', 'WP1ZZZ9YZKDA12345', 1, 0, 0, 0, 'used', 'III (9YA)', 'Porsche Cayenne S, повна комплектація.', 'Porsche Cayenne S, full trim.', 1),
(4, 10, 3, 'Hyundai Tucson 2023', 'hyundai-tucson-2023', 2023, 24500, NULL, 28000, 'crossover', 'automatic', 'petrol', 1.6, 180, 'awd', 'Сірий', 'Львівська', 'Львів', 'KM8JCCA18PU123456', 1, 0, 0, 1, 'new', 'IV (NX4)', 'Hyundai Tucson новий, гарантія дилера.', 'Hyundai Tucson new, dealer warranty.', 0),
(5, 13, 1, 'Kia Sportage 2022', 'kia-sportage-2022', 2022, 22000, NULL, 52000, 'crossover', 'automatic', 'petrol', 2.0, 150, 'awd', 'Зелений', 'Харківська', 'Харків', 'U5YHMC815NL123456', 1, 0, 0, 0, 'used', 'V (NQ5)', 'Kia Sportage AWD, сучасний дизайн.', 'Kia Sportage AWD, modern design.', 0),
(1, 2, 2, 'Audi Q5 2020', 'audi-q5-2020', 2020, 35000, NULL, 78000, 'suv', 'automatic', 'diesel', 2.0, 190, 'awd', 'Чорний', 'Київська', 'Київ', 'WA1BNAFY0L2123456', 1, 0, 0, 0, 'used', 'FY', 'Audi Q5 40 TDI quattro, преміум SUV.', 'Audi Q5 40 TDI quattro, premium SUV.', 1);

-- Car images (placeholder paths)
INSERT INTO car_images (car_id, filename, sort_order, is_main) VALUES
(1, 'placeholder-1.jpg', 0, 1), (1, 'placeholder-2.jpg', 1, 0),
(2, 'placeholder-1.jpg', 0, 1),
(3, 'placeholder-1.jpg', 0, 1), (3, 'placeholder-2.jpg', 1, 0),
(4, 'placeholder-1.jpg', 0, 1),
(5, 'placeholder-1.jpg', 0, 1),
(6, 'placeholder-1.jpg', 0, 1),
(7, 'placeholder-1.jpg', 0, 1),
(8, 'placeholder-1.jpg', 0, 1),
(9, 'placeholder-1.jpg', 0, 1),
(10, 'placeholder-1.jpg', 0, 1),
(11, 'placeholder-1.jpg', 0, 1),
(12, 'placeholder-1.jpg', 0, 1),
(13, 'placeholder-1.jpg', 0, 1),
(14, 'placeholder-1.jpg', 0, 1),
(15, 'placeholder-1.jpg', 0, 1),
(16, 'placeholder-1.jpg', 0, 1),
(17, 'placeholder-1.jpg', 0, 1),
(18, 'placeholder-1.jpg', 0, 1);

SET FOREIGN_KEY_CHECKS = 1;