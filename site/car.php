<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$id = (int)($_GET['id'] ?? 0);
$car = getCarById($id);

if (!$car) {
    http_response_code(404);
    $pageTitle = '404 - ' . getSeoTitle();
    require __DIR__ . '/includes/header.php';
    echo '<div class="container-im im-no-results"><h1>404</h1><p>' . __t('no_results') . '</p><a href="' . url() . '" class="im-btn im-btn-primary">' . __t('home') . '</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$listingType = $car['listing_type'] ?? 'car';
$pageTitle = $car['title'] . ' — ' . getSetting('site_name', SITE_NAME);
$pageDescription = mb_substr(strip_tags(getCarDescription($car)), 0, 160) ?: $car['title'] . ', ' . $car['year'] . ', ' . formatPrice((int)$car['price_usd']);
$carId = (int)$car['id'];
$imageFallback = e(listingImageUrl(null, $carId, $listingType));
$mainImage = listingImageUrl($car['images'][0]['filename'] ?? null, $carId, $listingType, 800);
$plateNumber = trim((string)($car['plate_number'] ?? ''));
$pageImage = absoluteUrl($mainImage);
$ogType = 'product';
$crumbs = [
    ['name' => __t('home'), 'url' => absoluteUrl(url())],
];
if ($listingType === 'plate') {
    $crumbs[] = ['name' => __t('plates'), 'url' => absoluteUrl(catalogUrl('plate'))];
} elseif ($listingType === 'special') {
    $crumbs[] = ['name' => __t('special'), 'url' => absoluteUrl(catalogUrl('special'))];
} else {
    $crumbs[] = ['name' => $car['brand_name'], 'url' => absoluteUrl(brandUrl($car['brand_slug']))];
}
$crumbs[] = ['name' => $car['title'], 'url' => absoluteUrl(carUrl((int)$car['id'], $car['slug'] ?? ''))];
$extraSchema = renderCarSchema($car) . renderBreadcrumbSchema($crumbs);

$similar = Database::fetchAll(
    "SELECT c.*, b.name as brand_name, m.name as model_name, d.name as dealer_name, d.phone as dealer_phone,
        (SELECT filename FROM car_images WHERE car_id = c.id AND is_main = 1 LIMIT 1) as main_image
    FROM cars c JOIN brands b ON c.brand_id = b.id JOIN models m ON c.model_id = m.id
    LEFT JOIN dealers d ON c.dealer_id = d.id
    WHERE c.listing_type = ? AND c.brand_id = ? AND c.id != ? AND c.is_active = 1 ORDER BY ABS(c.price_usd - ?) ASC LIMIT 4",
    'siii', [$listingType, $car['brand_id'], $car['id'], $car['price_usd']]
);

require __DIR__ . '/includes/header.php';
?>

<nav class="container-im im-breadcrumb" aria-label="Breadcrumb">
    <a href="<?= url() ?>"><?= __t('home') ?></a> /
    <?php if ($listingType === 'plate'): ?>
    <a href="<?= catalogUrl('plate') ?>"><?= __t('plates') ?></a> /
    <?php elseif ($listingType === 'special'): ?>
    <a href="<?= catalogUrl('special') ?>"><?= __t('special') ?></a> /
    <?php else: ?>
    <a href="<?= e(brandUrl($car['brand_slug'])) ?>"><?= e($car['brand_name']) ?></a> /
    <?php endif; ?>
    <span aria-current="page"><?= e($car['title']) ?></span>
</nav>

<div class="container-im im-detail-grid">
    <div>
        <?php if ($listingType === 'plate'): ?>
        <div class="im-plate-detail-visual">
            <div class="im-plate-frame im-plate-frame-lg">
                <span class="im-plate-number"><?= e($plateNumber ?: $car['title']) ?></span>
                <?php if ($car['region']): ?><span class="im-plate-region"><?= e($car['region']) ?></span><?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="im-gallery-main">
            <img id="mainImage" src="<?= e($mainImage) ?>" alt="<?= e($car['title']) ?>" width="800" height="600" fetchpriority="high" decoding="async" data-fallback="<?= $imageFallback ?>">
        </div>
        <?php endif; ?>
        <?php if ($listingType !== 'plate' && count($car['images']) > 1): ?>
        <div class="im-gallery-thumbs">
            <?php foreach ($car['images'] as $i => $img): ?>
            <?php $thumbUrl = listingImageUrl($img['filename'], (int)$car['id'], $listingType, 200); ?>
            <img src="<?= e($thumbUrl) ?>" class="<?= $i === 0 ? 'active' : '' ?>" data-full="<?= e(listingImageUrl($img['filename'], (int)$car['id'], $listingType, 800)) ?>" alt="<?= e($car['title']) ?>" width="120" height="90" loading="lazy" decoding="async" data-fallback="<?= $imageFallback ?>">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="im-car-badges im-car-badges--static">
            <?php if ($car['vin_verified']): ?><span class="im-badge im-badge-vin"><i class="bi bi-shield-check"></i> <?= __t('vin_verified') ?></span><?php endif; ?>
            <?php if ($car['is_featured']): ?><span class="im-badge im-badge-exclusive"><?= __t('only_here') ?></span><?php endif; ?>
            <?php if ($car['is_leasing']): ?><span class="im-badge im-badge-leasing"><?= __t('leasing') ?></span><?php endif; ?>
            <?php if ($car['is_exchange']): ?><span class="im-badge im-badge-exchange"><?= __t('exchange') ?></span><?php endif; ?>
        </div>
        <?php $desc = getCarDescription($car); if ($desc): ?>
        <div style="margin-top:24px">
            <h2 style="font-size:18px;margin-bottom:12px"><?= __t('description') ?></h2>
            <div style="line-height:1.7;color:var(--im-gray-700)"><?= $desc ?></div>
        </div>
        <?php endif; ?>
    </div>
    <aside class="im-detail-panel">
        <h1 style="font-size:22px;margin:0 0 8px;font-weight:700"><?= e($car['title']) ?></h1>
        <?php if ($car['generation']): ?><p style="color:var(--im-gray-500);margin:0 0 16px;font-size:14px"><?= e($car['generation']) ?></p><?php endif; ?>
        <div class="im-car-price-row">
            <?php if ($car['price_old_usd']): ?><span class="im-price-old"><?= formatPrice((int)$car['price_old_usd']) ?></span><?php endif; ?>
            <span class="im-price-main"><?= formatPrice((int)$car['price_usd']) ?></span>
            <?php $sec = formatSecondaryPrice((int)$car['price_usd']); if ($sec): ?>
            <span class="im-price-secondary"><?= $sec ?></span>
            <?php endif; ?>
        </div>
        <table class="im-specs-table">
            <tr><td><?= __t('year') ?></td><td><?= (int)$car['year'] ?></td></tr>
            <?php if ($listingType === 'plate' && $plateNumber): ?>
            <tr class="im-vin-row">
                <td><?= __t('plate_number') ?></td>
                <td>
                    <button type="button" class="im-btn-reveal im-btn-reveal-inline" data-reveal="plate" data-value="<?= e($plateNumber) ?>">
                        <i class="bi bi-123"></i>
                        <span class="im-reveal-label"><?= __t('show_plate') ?></span>
                    </button>
                </td>
            </tr>
            <?php endif; ?>
            <tr><td><?= $listingType === 'special' ? __t('operating_hours') : __t('mileage') ?></td><td><?= $listingType === 'special'
                ? ($car['mileage'] ? number_format((int)$car['mileage']) . ' ' . __t('hours') : '—')
                : formatMileage($car['mileage'] ? (int)$car['mileage'] : null) ?></td></tr>
            <?php if ($listingType !== 'plate'): ?>
            <tr><td><?= __t('engine') ?></td><td><?= __t($car['fuel_type']) ?><?= $car['engine_volume'] ? ', ' . $car['engine_volume'] . ' ' . __t('liters') : '' ?></td></tr>
            <?php if ($listingType === 'car'): ?>
            <tr><td><?= __t('power') ?></td><td><?= $car['engine_power'] ? (int)$car['engine_power'] . ' ' . __t('hp') : '—' ?></td></tr>
            <tr><td><?= __t('transmission') ?></td><td><?= __t($car['transmission']) ?></td></tr>
            <tr><td><?= __t('drive') ?></td><td><?= __t($car['drive_type']) ?></td></tr>
            <?php endif; ?>
            <tr><td><?= $listingType === 'special' ? __t('equipment_type') : __t('body') ?></td><td><?= __t($car['body_type']) ?></td></tr>
            <?php endif; ?>
            <tr><td><?= __t('color') ?></td><td><?= e($car['color'] ?? '—') ?></td></tr>
            <tr><td><?= __t('location') ?></td><td><?= e($car['city'] ?? '—') ?><?= $car['region'] ? ', ' . e($car['region']) : '' ?></td></tr>
            <?php if ($listingType === 'car' && $car['vin']): ?>
            <tr class="im-vin-row">
                <td>VIN</td>
                <td>
                    <button type="button" class="im-btn-reveal im-btn-reveal-inline" data-reveal="vin" data-value="<?= e($car['vin']) ?>">
                        <i class="bi bi-shield-check"></i>
                        <span class="im-reveal-label"><?= __t('show_vin') ?></span>
                    </button>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php if ($car['dealer_name']): ?>
        <div class="im-dealer-row" style="margin-top:20px;padding-top:16px;border-top:1px solid var(--im-gray-100)">
            <span class="im-dealer-avatar"><?= mb_strtoupper(mb_substr($car['dealer_name'], 0, 1)) ?></span>
            <div>
                <div style="font-weight:600"><?= e($car['dealer_name']) ?></div>
            </div>
        </div>
        <?php endif; ?>
        <?php $listingPhone = getListingPhone($car) ?? trim(getSetting('site_phone')); ?>
        <?php if ($listingPhone): ?>
        <div class="im-detail-actions">
            <button type="button" class="im-btn im-btn-green im-btn-block" data-reveal="phone" data-value="<?= e($listingPhone) ?>">
                <i class="bi bi-telephone"></i>
                <span class="im-reveal-label"><?= __t('show_phone') ?></span>
            </button>
        </div>
        <?php endif; ?>
    </aside>
</div>

<?php if (!empty($similar)): ?>
<section class="container-im im-similar-section">
    <h2 class="im-similar-title"><?= __t('similar_cars') ?></h2>
    <div class="im-listings<?= $listingType === 'plate' ? ' im-listings-plates' : '' ?>">
        <?php foreach ($similar as $cardIndex => $car):
            if ($listingType === 'plate') {
                require __DIR__ . '/includes/plate-card.php';
            } else {
                require __DIR__ . '/includes/car-card.php';
            }
        endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>