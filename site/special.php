<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$filters = buildCarFilters($_GET);
$filters['listing_type'] = 'special';
$catalogAction = catalogUrl('special');
$catalogType = 'special';

$result = getCars($filters);
$cars = $result['cars'];
$total = $result['total'];
$pages = $result['pages'];
$page = $result['page'];
$brands = getBrandsWithCount('special');

$seo = getCatalogSeo('special');
$pageTitle = $seo['title'];
$pageDescription = $seo['desc'];
$extraJs = asset('js/filters.js');

$crumbs = [
    ['name' => __t('home'), 'url' => absoluteUrl(url())],
    ['name' => __t('special'), 'url' => absoluteUrl(catalogUrl('special'))],
];
$extraSchema = renderBreadcrumbSchema($crumbs);
if (!empty($cars)) {
    $extraSchema .= renderItemListSchema($cars, $pageTitle);
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/toolbar.php';
?>

<div class="container-im im-page-head">
    <h1><?= __t('special') ?></h1>
    <p class="im-page-sub"><?= e($pageDescription) ?></p>
    <div class="count"><?= (int)$total ?> <?= __t('results') ?></div>
</div>

<?php if (!empty($brands)): ?>
<section class="im-brands">
    <div class="container-im">
        <div class="im-brands-grid">
            <?php foreach (array_slice($brands, 0, 12) as $brand): ?>
            <a href="<?= e(catalogUrl('special') . '?brand_id=' . (int)$brand['id']) ?>" class="im-brand-item">
                <span><?= e($brand['name']) ?></span>
                <span class="im-brand-count"><?= (int)$brand['car_count'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/filters.php'; ?>

<section class="container-im">
    <div class="im-list-header">
        <h2><?= __t('special') ?></h2>
    </div>
    <div id="carGrid" class="im-listings">
        <?php if (empty($cars)): ?>
        <div class="im-no-results">
            <i class="bi bi-truck im-no-results-icon" aria-hidden="true"></i>
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