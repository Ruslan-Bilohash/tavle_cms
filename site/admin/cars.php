<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$brands = Database::fetchAll('SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name');
$dealers = Database::fetchAll('SELECT id, name FROM dealers WHERE is_active = 1 ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    $data = [
        'brand_id'      => (int)($_POST['brand_id'] ?? 0),
        'model_id'      => (int)($_POST['model_id'] ?? 0),
        'dealer_id'     => !empty($_POST['dealer_id']) ? (int)$_POST['dealer_id'] : null,
        'title'         => sanitize($_POST['title'] ?? ''),
        'slug'          => slugify($_POST['title'] ?? ''),
        'year'          => (int)($_POST['year'] ?? 2020),
        'price_usd'     => (int)($_POST['price_usd'] ?? 0),
        'price_old_usd' => !empty($_POST['price_old_usd']) ? (int)$_POST['price_old_usd'] : null,
        'mileage'       => !empty($_POST['mileage']) ? (int)$_POST['mileage'] : null,
        'body_type'     => sanitize($_POST['body_type'] ?? 'sedan'),
        'transmission'  => sanitize($_POST['transmission'] ?? 'automatic'),
        'fuel_type'     => sanitize($_POST['fuel_type'] ?? 'petrol'),
        'engine_volume' => !empty($_POST['engine_volume']) ? (float)$_POST['engine_volume'] : null,
        'engine_power'  => !empty($_POST['engine_power']) ? (int)$_POST['engine_power'] : null,
        'drive_type'    => sanitize($_POST['drive_type'] ?? 'fwd'),
        'color'         => sanitize($_POST['color'] ?? ''),
        'region'        => sanitize($_POST['region'] ?? ''),
        'city'          => sanitize($_POST['city'] ?? ''),
        'vin'           => sanitize($_POST['vin'] ?? ''),
        'vin_verified'  => isset($_POST['vin_verified']) ? 1 : 0,
        'is_leasing'    => isset($_POST['is_leasing']) ? 1 : 0,
        'is_exchange'   => isset($_POST['is_exchange']) ? 1 : 0,
        'is_new'        => isset($_POST['is_new']) ? 1 : 0,
        'is_featured'   => isset($_POST['is_featured']) ? 1 : 0,
        'condition_type'=> sanitize($_POST['condition_type'] ?? 'used'),
        'generation'    => sanitize($_POST['generation'] ?? ''),
        'description_uk'=> $_POST['description_uk'] ?? '',
        'description_en'=> $_POST['description_en'] ?? '',
        'is_active'     => isset($_POST['is_active']) ? 1 : 0,
        'listing_type'  => in_array($_POST['listing_type'] ?? 'car', ['car', 'plate', 'special'], true)
            ? $_POST['listing_type'] : 'car',
        'plate_number'  => sanitize($_POST['plate_number'] ?? ''),
    ];

    if ($action === 'add' && $data['title'] && $data['brand_id'] && $data['model_id']) {
        $id = Database::insert(
            'INSERT INTO cars (brand_id, model_id, dealer_id, title, slug, year, price_usd, price_old_usd, mileage,
                body_type, transmission, fuel_type, engine_volume, engine_power, drive_type, color, region, city,
                vin, vin_verified, is_leasing, is_exchange, is_new, is_featured, condition_type, generation,
                description_uk, description_en, listing_type, plate_number, is_active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            'iiisssiiisssdisssssiiiiiisssssi',
            [
                $data['brand_id'], $data['model_id'], $data['dealer_id'], $data['title'], $data['slug'],
                $data['year'], $data['price_usd'], $data['price_old_usd'], $data['mileage'],
                $data['body_type'], $data['transmission'], $data['fuel_type'], $data['engine_volume'],
                $data['engine_power'], $data['drive_type'], $data['color'], $data['region'], $data['city'],
                $data['vin'], $data['vin_verified'], $data['is_leasing'], $data['is_exchange'],
                $data['is_new'], $data['is_featured'], $data['condition_type'], $data['generation'],
                $data['description_uk'], $data['description_en'], $data['listing_type'],
                $data['plate_number'] ?: null, $data['is_active'],
            ]
        );
        if (!empty($_FILES['photos'])) {
            processListingPhotoUploads($id, $_FILES['photos']);
        } else {
            Database::insert('INSERT INTO car_images (car_id, filename, is_main) VALUES (?, ?, 1)', 'is', [$id, 'placeholder-1.jpg']);
        }
        adminFlash('success', __a('listing_added'));
        redirect('/admin/cars.php?lang=' . getAdminLang());
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $data['title']) {
            Database::execute(
                'UPDATE cars SET brand_id=?, model_id=?, dealer_id=?, title=?, slug=?, year=?, price_usd=?,
                    price_old_usd=?, mileage=?, body_type=?, transmission=?, fuel_type=?, engine_volume=?,
                    engine_power=?, drive_type=?, color=?, region=?, city=?, vin=?, vin_verified=?,
                    is_leasing=?, is_exchange=?, is_new=?, is_featured=?, condition_type=?, generation=?,
                    description_uk=?, description_en=?, listing_type=?, plate_number=?, is_active=? WHERE id=?',
                'iiisssiiisssdisssssiiiiiisssssii',
                [
                    $data['brand_id'], $data['model_id'], $data['dealer_id'], $data['title'], $data['slug'],
                    $data['year'], $data['price_usd'], $data['price_old_usd'], $data['mileage'],
                    $data['body_type'], $data['transmission'], $data['fuel_type'], $data['engine_volume'],
                    $data['engine_power'], $data['drive_type'], $data['color'], $data['region'], $data['city'],
                    $data['vin'], $data['vin_verified'], $data['is_leasing'], $data['is_exchange'],
                    $data['is_new'], $data['is_featured'], $data['condition_type'], $data['generation'],
                    $data['description_uk'], $data['description_en'], $data['listing_type'],
                    $data['plate_number'] ?: null, $data['is_active'], $id,
                ]
            );
            if (!empty($_FILES['photos'])) {
                processListingPhotoUploads($id, $_FILES['photos']);
            }
            adminFlash('success', __a('listing_updated'));
            redirect('/admin/cars.php?lang=' . getAdminLang());
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    Database::execute('UPDATE cars SET is_active = 0 WHERE id = ?', 'i', [(int)$_GET['id']]);
    adminFlash('success', __a('listing_deactivated'));
    redirect('/admin/cars.php?lang=' . getAdminLang());
}

if ($action === 'delete_image' && isset($_GET['id'])) {
    if (deleteListingImage((int)$_GET['id'])) {
        adminFlash('success', __a('photo_deleted'));
    }
    $carId = (int)($_GET['car_id'] ?? 0);
    redirect($carId ? '/admin/cars.php?action=edit&id=' . $carId . '&lang=' . getAdminLang() : '/admin/cars.php?lang=' . getAdminLang());
}

$cars = Database::fetchAll(
    'SELECT c.id, c.title, c.price_usd, c.year, c.is_active, c.is_draft, c.listing_type, b.name as brand_name
    FROM cars c JOIN brands b ON c.brand_id = b.id ORDER BY c.created_at DESC'
);
$editCar = ($action === 'edit' && isset($_GET['id'])) ? Database::fetchOne('SELECT * FROM cars WHERE id = ?', 'i', [(int)$_GET['id']]) : null;
$editModels = $editCar ? getModelsByBrand((int)$editCar['brand_id']) : [];
$editImages = $editCar ? getListingImages((int)$editCar['id']) : [];

$adminTitle = __a('listings');
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= e(__a('listings')) ?></h2>
    <?php if ($action !== 'add' && !$editCar): ?>
    <a href="?action=add&lang=<?= e(getAdminLang()) ?>" class="btn btn-primary"><?= e(__a('add_listing_btn')) ?></a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $editCar): ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <?= csrfField() ?>
            <?php if ($editCar): ?><input type="hidden" name="id" value="<?= (int)$editCar['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Тип оголошення</label>
                    <select name="listing_type" id="listingTypeSelect" class="form-select">
                        <option value="car"<?= ($editCar['listing_type'] ?? 'car') === 'car' ? ' selected' : '' ?>>Автомобіль</option>
                        <option value="plate"<?= ($editCar['listing_type'] ?? '') === 'plate' ? ' selected' : '' ?>>Номерний знак</option>
                        <option value="special"<?= ($editCar['listing_type'] ?? '') === 'special' ? ' selected' : '' ?>>Спецтехніка</option>
                    </select>
                </div>
                <div class="col-md-3" id="plateNumberField"<?= ($editCar['listing_type'] ?? 'car') !== 'plate' ? ' style="display:none"' : '' ?>>
                    <label class="form-label">Номерний знак</label>
                    <input type="text" name="plate_number" class="form-control" maxlength="20" value="<?= e($editCar['plate_number'] ?? '') ?>" placeholder="AA 1234 BB">
                </div>
                <div class="col-md-6"><label class="form-label">Назва</label><input type="text" name="title" class="form-control" required value="<?= e($editCar['title'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Рік</label><input type="number" name="year" class="form-control" required value="<?= (int)($editCar['year'] ?? 2024) ?>"></div>
                <div class="col-md-3"><label class="form-label">Ціна USD</label><input type="number" name="price_usd" class="form-control" required value="<?= (int)($editCar['price_usd'] ?? 0) ?>"></div>
                <div class="col-md-3"><label class="form-label">Стара ціна</label><input type="number" name="price_old_usd" class="form-control" value="<?= (int)($editCar['price_old_usd'] ?? 0) ?>"></div>
                <div class="col-md-3"><label class="form-label">Пробіг (км)</label><input type="number" name="mileage" class="form-control" value="<?= (int)($editCar['mileage'] ?? 0) ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Бренд</label>
                    <select name="brand_id" id="brandSelect" class="form-select" required>
                        <?php foreach ($brands as $b): ?>
                        <option value="<?= (int)$b['id'] ?>"<?= ($editCar['brand_id'] ?? '') == $b['id'] ? ' selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Модель</label>
                    <select name="model_id" id="modelSelect" class="form-select" required>
                        <?php foreach ($editModels as $m): ?>
                        <option value="<?= (int)$m['id'] ?>"<?= ($editCar['model_id'] ?? '') == $m['id'] ? ' selected' : '' ?>><?= e($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Дилер</label>
                    <select name="dealer_id" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($dealers as $d): ?>
                        <option value="<?= (int)$d['id'] ?>"<?= ($editCar['dealer_id'] ?? '') == $d['id'] ? ' selected' : '' ?>><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Кузов</label>
                    <select name="body_type" class="form-select">
                        <?php foreach (getBodyTypes() as $bt): ?>
                        <option value="<?= $bt ?>"<?= ($editCar['body_type'] ?? '') === $bt ? ' selected' : '' ?>><?= $bt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Коробка</label>
                    <select name="transmission" class="form-select">
                        <?php foreach (getTransmissions() as $t): ?>
                        <option value="<?= $t ?>"<?= ($editCar['transmission'] ?? '') === $t ? ' selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Паливо</label>
                    <select name="fuel_type" class="form-select">
                        <?php foreach (getFuelTypes() as $f): ?>
                        <option value="<?= $f ?>"<?= ($editCar['fuel_type'] ?? '') === $f ? ' selected' : '' ?>><?= $f ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Привід</label>
                    <select name="drive_type" class="form-select">
                        <?php foreach (getDriveTypes() as $d): ?>
                        <option value="<?= $d ?>"<?= ($editCar['drive_type'] ?? '') === $d ? ' selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label">Об'єм</label><input type="number" step="0.1" name="engine_volume" class="form-control" value="<?= e((string)($editCar['engine_volume'] ?? '')) ?>"></div>
                <div class="col-md-2"><label class="form-label">к.с.</label><input type="number" name="engine_power" class="form-control" value="<?= (int)($editCar['engine_power'] ?? 0) ?>"></div>
                <div class="col-md-2"><label class="form-label">Колір</label><input type="text" name="color" class="form-control" value="<?= e($editCar['color'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Область</label><input type="text" name="region" class="form-control" value="<?= e($editCar['region'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Місто</label><input type="text" name="city" class="form-control" value="<?= e($editCar['city'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">VIN</label><input type="text" name="vin" class="form-control" maxlength="17" value="<?= e($editCar['vin'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Покоління</label><input type="text" name="generation" class="form-control" value="<?= e($editCar['generation'] ?? '') ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Стан</label>
                    <select name="condition_type" class="form-select">
                        <?php foreach (getConditionTypes() as $ct): ?>
                        <option value="<?= $ct ?>"<?= ($editCar['condition_type'] ?? '') === $ct ? ' selected' : '' ?>><?= $ct ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Опис (UK)</label><textarea name="description_uk" class="form-control" rows="3"><?= e($editCar['description_uk'] ?? '') ?></textarea></div>
                <div class="col-md-6"><label class="form-label">Опис (EN)</label><textarea name="description_en" class="form-control" rows="3"><?= e($editCar['description_en'] ?? '') ?></textarea></div>
                <div class="col-12">
                    <label class="form-label fw-semibold"><?= e(__a('photos')) ?></label>
                    <?php if ($editImages): ?>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <?php foreach ($editImages as $img): ?>
                        <div class="position-relative border rounded p-1" style="width:100px">
                            <img src="<?= e(getCarImageUrl($img['filename'], (int)$editCar['id'])) ?>" alt="" class="rounded" style="width:92px;height:69px;object-fit:cover">
                            <?php if (!empty($img['is_main'])): ?><span class="badge bg-success position-absolute top-0 start-0 m-1" style="font-size:9px">main</span><?php endif; ?>
                            <a href="?action=delete_image&id=<?= (int)$img['id'] ?>&car_id=<?= (int)$editCar['id'] ?>&lang=<?= e(getAdminLang()) ?>" class="btn btn-sm btn-danger w-100 mt-1" onclick="return confirm('OK?')">×</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="photos[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="form-text"><?= e(__a('upload_photos')) ?></div>
                </div>
                <div class="col-12 d-flex flex-wrap gap-3">
                    <div class="form-check"><input type="checkbox" name="vin_verified" class="form-check-input" id="vin_verified"<?= ($editCar['vin_verified'] ?? 0) ? ' checked' : '' ?>><label class="form-check-label" for="vin_verified">VIN перевірено</label></div>
                    <div class="form-check"><input type="checkbox" name="is_leasing" class="form-check-input" id="is_leasing"<?= ($editCar['is_leasing'] ?? 0) ? ' checked' : '' ?>><label class="form-check-label" for="is_leasing">Лізинг</label></div>
                    <div class="form-check"><input type="checkbox" name="is_exchange" class="form-check-input" id="is_exchange"<?= ($editCar['is_exchange'] ?? 0) ? ' checked' : '' ?>><label class="form-check-label" for="is_exchange">Обмін</label></div>
                    <div class="form-check"><input type="checkbox" name="is_new" class="form-check-input" id="is_new"<?= ($editCar['is_new'] ?? 0) ? ' checked' : '' ?>><label class="form-check-label" for="is_new">Новий</label></div>
                    <div class="form-check"><input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"<?= ($editCar['is_featured'] ?? 0) ? ' checked' : '' ?>><label class="form-check-label" for="is_featured">Рекомендований</label></div>
                    <div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" id="is_active"<?= ($editCar['is_active'] ?? 1) ? ' checked' : '' ?>><label class="form-check-label" for="is_active">Активний</label></div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><?= e(__a('save')) ?></button>
                <a href="<?= url('admin/cars.php?lang=' . getAdminLang()) ?>" class="btn btn-secondary"><?= e(__a('cancel')) ?></a>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('listingTypeSelect')?.addEventListener('change', function() {
    var plateField = document.getElementById('plateNumberField');
    if (plateField) {
        plateField.style.display = this.value === 'plate' ? '' : 'none';
    }
});
document.getElementById('brandSelect')?.addEventListener('change', function() {
    var base = window.BILEN_BASE || '';
    fetch(base + '/api/models.php?brand_id=' + this.value).then(r => r.json()).then(data => {
        const sel = document.getElementById('modelSelect');
        sel.innerHTML = '';
        data.forEach(m => { const o = document.createElement('option'); o.value = m.id; o.textContent = m.name; sel.appendChild(o); });
    });
});
</script>
<?php endif; ?>

<div class="card">
    <table class="table table-hover mb-0">
        <thead><tr><th><?= e(__a('id')) ?></th><th><?= e(__a('type')) ?></th><th><?= e(__a('title')) ?></th><th><?= e(__a('brand')) ?></th><th><?= e(__a('year')) ?></th><th><?= e(__a('price')) ?></th><th><?= e(__a('status')) ?></th><th></th></tr></thead>
        <tbody>
            <?php foreach ($cars as $c): ?>
            <tr>
                <td><?= (int)$c['id'] ?></td>
                <td><?php
                    $lt = $c['listing_type'] ?? 'car';
                    $ltLabel = match ($lt) {
                        'plate' => __a('type_plate'),
                        'special' => __a('type_special'),
                        default => __a('type_car'),
                    };
                    $ltClass = match ($lt) {
                        'plate' => 'bg-info',
                        'special' => 'bg-warning text-dark',
                        default => 'bg-primary',
                    };
                ?><span class="badge <?= $ltClass ?>"><?= $ltLabel ?></span></td>
                <td><?= e($c['title']) ?></td>
                <td><?= e($c['brand_name']) ?></td>
                <td><?= (int)$c['year'] ?></td>
                <td><?= formatPrice((int)$c['price_usd']) ?></td>
                <td><?php
                    if (!empty($c['is_draft'])) {
                        echo '<span class="badge bg-warning text-dark">' . e(__a('status_draft')) . '</span>';
                    } elseif ($c['is_active']) {
                        echo '<span class="badge bg-success">' . e(__a('status_active')) . '</span>';
                    } else {
                        echo '<span class="badge bg-secondary">' . e(__a('status_inactive')) . '</span>';
                    }
                ?></td>
                <td>
                    <a href="<?= url('car.php?id=' . (int)$c['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="<?= e(__a('view')) ?>">↗</a>
                    <a href="?action=edit&id=<?= (int)$c['id'] ?>&lang=<?= e(getAdminLang()) ?>" class="btn btn-sm btn-outline-primary"><?= e(__a('edit')) ?></a>
                    <a href="?action=delete&id=<?= (int)$c['id'] ?>&lang=<?= e(getAdminLang()) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?= e(__a('confirm_deactivate')) ?>')">×</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>