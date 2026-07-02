<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = url('my-listings.php');
    redirect(url('admin/login.php'));
}

$user = currentUser();
$listings = getUserListings((int)$user['id']);

$pageTitle = __t('my_listings');
$pageDescription = __t('my_listings_intro');
$crumbs = [
    ['name' => __t('home'), 'url' => absoluteUrl(url())],
    ['name' => $pageTitle, 'url' => absoluteUrl(url('my-listings.php'))],
];
$extraSchema = renderBreadcrumbSchema($crumbs);

require __DIR__ . '/includes/header.php';
?>

<div class="container-im im-page-head">
    <h1><?= e($pageTitle) ?></h1>
    <p class="im-page-sub"><?= e(__t('my_listings_intro')) ?> — <?= e($user['name']) ?> (<?= e($user['email']) ?>)</p>
</div>

<section class="container-im im-my-listings">
    <div class="im-my-listings-actions">
        <a href="<?= url('add.php') ?>" class="im-btn im-btn-primary"><i class="bi bi-plus-lg"></i> <?= e(__t('add_listing')) ?></a>
        <a href="<?= url('admin/logout.php') ?>" class="im-btn"><?= e(__t('logout')) ?></a>
    </div>

    <?php if (empty($listings)): ?>
    <div class="im-no-results">
        <i class="bi bi-inbox" style="font-size:48px"></i>
        <p><?= e(__t('my_listings_empty')) ?></p>
        <a href="<?= url('add.php') ?>" class="im-btn im-btn-primary"><?= e(__t('publish_listing')) ?></a>
    </div>
    <?php else: ?>
    <div class="im-my-listings-grid">
        <?php foreach ($listings as $car): ?>
        <article class="im-my-listing-card">
            <div class="im-my-listing-head">
                <h3><a href="<?= e(carUrl((int)$car['id'], $car['slug'] ?? '')) ?>"><?= e($car['title']) ?></a></h3>
                <?php if (!empty($car['is_draft'])): ?>
                <span class="im-badge im-badge-draft"><?= e(__t('draft')) ?></span>
                <?php else: ?>
                <span class="im-badge<?= $car['is_active'] ? ' im-badge-active' : '' ?>"><?= $car['is_active'] ? e(__t('active')) : e(__t('inactive')) ?></span>
                <?php endif; ?>
            </div>
            <p class="im-my-listing-meta"><?= e($car['brand_name']) ?> · <?= (int)$car['year'] ?> · <?= formatPrice((int)$car['price_usd']) ?></p>
            <div class="im-my-listing-actions">
                <?php if (empty($car['is_draft']) && $car['is_active']): ?>
                <a href="<?= e(carUrl((int)$car['id'], $car['slug'] ?? '')) ?>" class="im-btn im-btn-sm"><?= e(__t('view_listing')) ?></a>
                <?php endif; ?>
                <a href="<?= url('add.php?edit=' . (int)$car['id']) ?>" class="im-btn im-btn-sm im-btn-primary"><?= e(__t('edit_listing')) ?></a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>