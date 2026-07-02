<?php
$lang = getCurrentLang();
$pageTitle = $pageTitle ?? getSeoTitle();
$pageDescription = $pageDescription ?? getSeoDescription();
$pageImage = $pageImage ?? absoluteUrl(getSetting('og_image', BASE_PATH . '/assets/images/og-default.jpg'));
$extraSchema = ($extraSchema ?? '') . renderWebSiteSchema() . renderOrganizationSchema() . renderSoftwareApplicationSchema();
$siteName = getSetting('site_name', SITE_NAME);
$navTop = getNavMenu('top');
$navHeader = getNavMenu('header');
$currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if (BASE_PATH !== '' && str_starts_with($currentPath, BASE_PATH)) {
    $currentPath = substr($currentPath, strlen(BASE_PATH)) ?: '/';
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" data-bilen-base="<?= e(rtrim(BASE_PATH, '/')) ?>">
<head>
<?php require __DIR__ . '/seo-head.php'; ?>
</head>
<body<?= (defined('DEMO_BANNER') && DEMO_BANNER) ? ' class="has-demo-banner"' : '' ?>>
<a class="im-skip-link" href="#main-content"><?= __t('skip_to_content') ?></a>
<?php require __DIR__ . '/demo-banner.php'; ?>
<div class="im-topbar">
    <div class="container-im im-topbar-inner">
        <nav class="im-categories" aria-label="<?= __t('cars') ?>">
            <?php foreach ($navTop as $i => $item): ?>
            <?php $itemUrl = navItemUrl($item['url'] ?? ''); ?>
            <a href="<?= e($itemUrl) ?>"<?= isNavTopActive($item, $currentPath) ? ' class="active"' : '' ?>><?= e(getNavLabel($item)) ?></a>
            <?php endforeach; ?>
        </nav>
        <a href="<?= url('add.php') ?>" class="im-btn-add"><i class="bi bi-plus-lg"></i><span><?= __t('add_listing') ?></span></a>
    </div>
</div>
<header class="im-header">
    <div class="container-im im-header-inner">
        <a class="im-logo" href="<?= url() ?>">
            <span class="im-logo-mark"><i class="bi bi-car-front-fill"></i></span>
            <span class="im-logo-text"><?= e($siteName) ?></span>
        </a>
        <nav class="im-nav" id="imNav" aria-label="Main">
            <?php require __DIR__ . '/catalog-nav-dropdown.php'; ?>
            <?php foreach ($navHeader as $item): ?>
            <a href="<?= e(navItemUrl($item['url'] ?? '')) ?>"><?= e(getNavLabel($item)) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="im-header-actions">
            <div class="im-lang-wrap">
                <button class="im-lang-btn" id="langToggle" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="langMenu"><?= strtoupper($lang) ?> ▾</button>
                <div class="im-lang-menu" id="langMenu" hidden>
                    <?php foreach (AVAILABLE_LANGS as $l): ?>
                    <a href="<?= langUrl($l) ?>" hreflang="<?= $l ?>"<?= $l === $lang ? ' class="active"' : '' ?>><?= strtoupper($l) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (isLoggedIn() && !isAdmin()): ?>
            <a href="<?= url('my-listings.php') ?>" class="im-btn im-btn-sm"><?= __t('my_listings') ?></a>
            <?php endif; ?>
            <a href="<?= url(isAdmin() ? 'admin/' : 'admin/login.php') ?>" class="im-btn im-btn-primary im-btn-sm"><?= __t('admin_panel') ?></a>
            <button class="im-menu-toggle" id="menuToggle" type="button" aria-label="<?= e(__t('menu')) ?>" aria-expanded="false" aria-controls="imNav"><i class="bi bi-list" aria-hidden="true"></i></button>
        </div>
    </div>
</header>
<main id="main-content">