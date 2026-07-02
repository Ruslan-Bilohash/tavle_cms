<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$filters = buildCarFilters($_GET);
$filters['listing_type'] = 'car';
$result = getCars($filters);
$cars = $result['cars'];
$total = $result['total'];
$pages = $result['pages'];
$page = $result['page'];
$brands = getBrandsWithCount();

$activeBrand = null;
$activeModel = null;
if (!empty($filters['brand_id'])) {
    $activeBrand = Database::fetchOne('SELECT * FROM brands WHERE id = ?', 'i', [$filters['brand_id']]);
}
if (!empty($filters['model_id']) && $activeBrand) {
    $activeModel = Database::fetchOne('SELECT * FROM models WHERE id = ?', 'i', [$filters['model_id']]);
}

$pageTitle = getSeoTitle();
$pageDescription = getSeoDescription();
if ($activeModel && $activeBrand) {
    $pageTitle = getModelSeoTitle($activeBrand, $activeModel);
    $pageDescription = getModelSeoDescription($activeBrand, $activeModel, $total);
} elseif ($activeBrand) {
    $pageTitle = $activeBrand['name'] . ' — ' . getSetting('site_name', SITE_NAME);
    $pageDescription = $activeBrand['name'] . ' — ' . (int)$total . ' ' . __t('results');
}

$brandModels = $activeBrand ? getModelsWithCount((int)$activeBrand['id']) : [];
$extraJs = asset('js/filters.js');

$crumbs = [['name' => __t('home'), 'url' => absoluteUrl(url())]];
if ($activeBrand) {
    $crumbs[] = ['name' => $activeBrand['name'], 'url' => absoluteUrl(brandUrl($activeBrand['slug']))];
}
if ($activeModel && $activeBrand) {
    $crumbs[] = ['name' => $activeModel['name'], 'url' => absoluteUrl(modelUrl($activeBrand['slug'], $activeModel['slug']))];
}
$extraSchema = renderBreadcrumbSchema($crumbs);
if (!empty($cars)) {
    $extraSchema .= renderItemListSchema($cars, $pageTitle);
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/toolbar.php';
?>

<div class="container-im im-page-head">
    <h1><?= e(getSetting('site_name', SITE_NAME)) ?>. <?= e(getSetting('site_tagline', __t('tagline'))) ?></h1>
    <p class="im-page-sub"><?= __t('footer_about') ?></p>
    <?php if ($activeModel && $activeBrand): ?>
    <div class="count"><?= e($activeBrand['name'] . ' ' . $activeModel['name']) ?> — <?= (int)$total ?> <?= __t('results') ?></div>
    <?php elseif ($activeBrand): ?>
    <div class="count"><?= e($activeBrand['name']) ?> — <?= (int)$total ?> <?= __t('results') ?></div>
    <?php else: ?>
    <div class="count"><?= (int)$total ?> <?= __t('results') ?></div>
    <?php endif; ?>
</div>

<?php if ($activeBrand && !empty($brandModels)): ?>
<section class="im-models-bar">
    <div class="container-im">
        <div class="im-models-scroll">
            <?php foreach ($brandModels as $m): ?>
            <a href="<?= e(modelUrl($activeBrand['slug'], $m['slug'])) ?>" class="im-model-chip<?= ($activeModel && (int)$activeModel['id'] === (int)$m['id']) ? ' active' : '' ?>">
                <?= e($m['name']) ?> <span><?= (int)$m['car_count'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="im-brands">
    <div class="container-im">
        <div class="im-brands-grid">
            <?php foreach (array_slice($brands, 0, 12) as $brand): ?>
            <a href="<?= e(brandUrl($brand['slug'])) ?>" class="im-brand-item">
                <span><?= e($brand['name']) ?></span>
                <span class="im-brand-count"><?= (int)$brand['car_count'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <a href="<?= url() ?>" class="im-brands-all"><?= __t('all_brands') ?> — <?= count($brands) ?> <i class="bi bi-arrow-right"></i></a>
    </div>
</section>

<?php require __DIR__ . '/includes/filters.php'; ?>

<section class="container-im">
    <div class="im-list-header">
        <h2><?= __t('sort') ?>: <?= __t('sort_newest') ?></h2>
    </div>
    <div id="carGrid" class="im-listings">
        <?php if (empty($cars)): ?>
        <div class="im-no-results">
            <i class="bi bi-search im-no-results-icon" aria-hidden="true"></i>
            <p><?= __t('no_results') ?></p>
        </div>
        <?php else: ?>
        <?php foreach ($cars as $cardIndex => $car): ?>
            <?php require __DIR__ . '/includes/car-card.php'; ?>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?= renderPagination($page, $pages, array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)) ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>