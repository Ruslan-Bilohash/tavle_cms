<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$stats = [
    'cars'   => (int)(Database::fetchOne('SELECT COUNT(*) as c FROM cars WHERE is_active = 1 AND (is_draft = 0 OR is_draft IS NULL)')['c'] ?? 0),
    'drafts' => (int)(Database::fetchOne('SELECT COUNT(*) as c FROM cars WHERE is_draft = 1')['c'] ?? 0),
    'brands' => (int)(Database::fetchOne('SELECT COUNT(*) as c FROM brands WHERE is_active = 1')['c'] ?? 0),
    'users'  => (int)(Database::fetchOne('SELECT COUNT(*) as c FROM users WHERE is_active = 1')['c'] ?? 0),
    'views'  => (int)(Database::fetchOne('SELECT SUM(views) as c FROM cars')['c'] ?? 0),
];

$recentCars = Database::fetchAll(
    "SELECT c.id, c.title, c.price_usd, c.created_at, c.is_active, c.is_draft, b.name as brand_name
    FROM cars c JOIN brands b ON c.brand_id = b.id
    ORDER BY c.created_at DESC LIMIT 8"
);

$adminTitle = __a('dashboard');
require __DIR__ . '/includes/header.php';
?>

<h2 class="mb-4"><?= e(__a('dashboard')) ?></h2>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['cars'] ?></div>
            <div class="stat-label"><?= e(__a('stat_listings')) ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['drafts'] ?></div>
            <div class="stat-label"><?= e(__a('stat_drafts')) ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['brands'] ?></div>
            <div class="stat-label"><?= e(__a('stat_brands')) ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['views']) ?></div>
            <div class="stat-label"><?= e(__a('stat_views')) ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><?= e(__a('recent_listings')) ?></span>
                <a href="<?= url('admin/cars.php?action=add&lang=' . getAdminLang()) ?>" class="btn btn-sm btn-primary"><?= e(__a('add_listing_btn')) ?></a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?= e(__a('id')) ?></th>
                            <th><?= e(__a('title')) ?></th>
                            <th><?= e(__a('brand')) ?></th>
                            <th><?= e(__a('price')) ?></th>
                            <th><?= e(__a('status')) ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentCars as $car): ?>
                        <tr>
                            <td><?= (int)$car['id'] ?></td>
                            <td><?= e($car['title']) ?></td>
                            <td><?= e($car['brand_name']) ?></td>
                            <td><?= formatPrice((int)$car['price_usd']) ?></td>
                            <td>
                                <?php if (!empty($car['is_draft'])): ?>
                                <span class="badge bg-warning text-dark"><?= e(__a('status_draft')) ?></span>
                                <?php elseif ($car['is_active']): ?>
                                <span class="badge bg-success"><?= e(__a('status_active')) ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary"><?= e(__a('status_inactive')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('admin/cars.php?action=edit&id=' . (int)$car['id'] . '&lang=' . getAdminLang()) ?>" class="btn btn-sm btn-outline-primary"><?= e(__a('edit')) ?></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><?= e(__a('quick_links')) ?></div>
            <div class="card-body d-grid gap-2">
                <a href="<?= url('add.php') ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-plus-lg"></i> <?= e(__a('public_add')) ?></a>
                <a href="<?= url('admin/cars.php?lang=' . getAdminLang()) ?>" class="btn btn-outline-primary"><i class="bi bi-car-front"></i> <?= e(__a('listings')) ?></a>
                <a href="<?= url('admin/settings.php?lang=' . getAdminLang()) ?>" class="btn btn-outline-primary"><i class="bi bi-gear"></i> <?= e(__a('settings')) ?></a>
                <a href="<?= e(rtrim(BASE_PATH, '/') . '/readme.md') ?>" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-book"></i> readme.md</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>