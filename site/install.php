<?php
/**
 * Bilen CMS - Installation / Health Check
 * DELETE THIS FILE AFTER DEPLOYMENT!
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? 'check';

        if ($action === 'reinstall') {
            require_once __DIR__ . '/database/installer.php';
            DatabaseInstaller::reinstall(Database::getInstance());
            $messages[] = 'База даних перестворена з демо-даними.';
        }

        if ($action === 'seed_plates_special') {
            require_once __DIR__ . '/database/installer.php';
            DatabaseInstaller::seedPlatesAndSpecial(Database::getInstance());
            $db = Database::getInstance();
            $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('plates_special_seed_version', '2')
                ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value")->execute();
            $messages[] = 'Демо-дані номерних знаків та спецтехніки додано (існуючі slug пропущено).';
        }

        $count = Database::fetchOne('SELECT COUNT(*) as c FROM cars');
        $plates = Database::fetchOne("SELECT COUNT(*) as c FROM cars WHERE listing_type = 'plate'");
        $special = Database::fetchOne("SELECT COUNT(*) as c FROM cars WHERE listing_type = 'special'");
        $messages[] = 'SQLite підключено: ' . DB_PATH;
        $messages[] = 'Оголошень у базі: ' . ($count['c'] ?? 0)
            . ' (номери: ' . ($plates['c'] ?? 0) . ', спецтехніка: ' . ($special['c'] ?? 0) . ')';

        if (!is_writable(dirname(DB_PATH))) {
            $errors[] = 'Папка data/ не доступна для запису. chmod 755 data/';
        } else {
            $messages[] = 'Папка data/ доступна для запису.';
        }

        if (!is_writable(UPLOAD_PATH)) {
            $errors[] = 'Папка uploads/cars/ не доступна для запису. chmod 755 uploads/cars/';
        } else {
            $messages[] = 'Папка uploads/cars/ доступна для запису.';
        }

        if (!extension_loaded('pdo_sqlite')) {
            $errors[] = 'Розширення PHP pdo_sqlite не встановлено!';
        } else {
            $messages[] = 'PHP pdo_sqlite — OK';
        }

        $messages[] = 'Готово! Видаліть install.php після деплою.';
    } catch (Throwable $e) {
        $errors[] = 'Помилка: ' . $e->getMessage();
    }
}

// Trigger auto-install on page load
try {
    Database::getInstance();
} catch (Throwable $e) {
    $errors[] = 'DB init: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Bilen CMS — Встановлення</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:640px">
    <h1 class="mb-4">Bilen CMS — Production Setup</h1>
    <p class="text-muted">SQLite — без MySQL. База: <code>data/bilen.sqlite</code></p>

    <?php foreach ($messages as $msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Деплой на продакшен:</h5>
            <ol>
                <li>Завантажте файли на сервер</li>
                <li>Вкажіть <code>SITE_URL</code> у <code>config.php</code></li>
                <li>Переконайтесь що <code>data/</code> і <code>uploads/cars/</code> доступні для запису</li>
                <li>Відкрийте сайт — база створиться автоматично</li>
                <li>Видаліть <code>install.php</code></li>
            </ol>
            <form method="post" class="d-flex flex-wrap gap-2">
                <button type="submit" name="action" value="check" class="btn btn-primary">Перевірити систему</button>
                <button type="submit" name="action" value="seed_plates_special" class="btn btn-success">Додати номери + спецтехніку</button>
                <button type="submit" name="action" value="reinstall" class="btn btn-outline-danger" onclick="return confirm('Перестворити БД? Всі дані будуть втрачені.')">Перестворити БД</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Демо-доступ</h5>
            <ul class="mb-0">
                <li>Адмін: <code>admin</code> / <code>admin123</code></li>
                <li>Дилер: <code>dealer1</code> / <code>user123</code></li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>