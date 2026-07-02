<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$filters = buildCarFilters($_GET);
$filters['listing_type'] = 'plate';
$catalogAction = catalogUrl('plate');
$catalogType = 'plate';

$result = getCars($filters);
$cars = $result['cars'];
$total = $result['total'];
$pages = $result['pages'];
$page = $result['page'];

$seo = getCatalogSeo('plate');
$pageTitle = $seo['title'];
$pageDescription = $seo['desc'];
$brands = [];
$extraJs = asset('js/filters.js');

$crumbs = [
    ['name' => __t('home'), 'url' => absoluteUrl(url())],
    ['name' => __t('plates'), 'url' => absoluteUrl(catalogUrl('plate'))],
];
$extraSchema = renderBreadcrumbSchema($crumbs);
if (!empty($cars)) {
    $extraSchema .= renderItemListSchema($cars, $pageTitle);
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/toolbar.php';
?>

<?php require __DIR__ . '/includes/filters.php'; ?>

<div class="container-im im-page-head">
    <h1><?= __t('plates') ?></h1>
    <p class="im-page-sub"><?= e($pageDescription) ?></p>
    <div class="count"><?= (int)$total ?> <?= __t('results') ?></div>
</div>

<section class="container-im">
    <div class="im-list-header">
        <h2><?= __t('plates') ?></h2>
    </div>
    <div id="carGrid" class="im-listings im-listings-plates">
        <?php if (empty($cars)): ?>
        <div class="im-no-results">
            <i class="bi bi-123 im-no-results-icon" aria-hidden="true"></i>
            <p><?= __t('no_results') ?></p>
        </div>
        <?php else: ?>
        <?php foreach ($cars as $car): ?>
            <?php require __DIR__ . '/includes/plate-card.php'; ?>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?= renderPagination($page, $pages, array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)) ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>