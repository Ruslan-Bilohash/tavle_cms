<?php
$q = buildCarFilters($_GET);
$stockActive = empty($q['is_en_route']) && empty($q['is_on_order']);
$catalogLabel = catalogNavActiveLabel($q);
$currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if (BASE_PATH !== '' && str_starts_with($currentPath, BASE_PATH)) {
    $currentPath = substr($currentPath, strlen(BASE_PATH)) ?: '/';
}
$currentBase = basename($currentPath);
$carsActive = in_array($currentBase, ['', 'index.php'], true) || $currentPath === '/';
$platesActive = in_array($currentBase, ['plates.php', 'plates'], true);
$specialActive = in_array($currentBase, ['special.php', 'special'], true);
?>
<div class="im-nav-dropdown" id="catalogNavDropdown">
    <button type="button" class="im-nav-dropdown-btn" id="catalogNavToggle" aria-haspopup="true" aria-expanded="false" aria-controls="catalogNavMenu">
        <?= e($catalogLabel) ?> <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="im-nav-dropdown-menu" id="catalogNavMenu" hidden>
        <div class="im-nav-dropdown-group">
            <span class="im-nav-dropdown-label"><?= __t('nav_catalog') ?></span>
            <a href="<?= url() ?>" class="im-nav-dropdown-link<?= $carsActive ? ' active' : '' ?>"><?= __t('cars') ?></a>
            <a href="<?= catalogUrl('plate') ?>" class="im-nav-dropdown-link<?= $platesActive ? ' active' : '' ?>"><?= __t('plates') ?></a>
            <a href="<?= catalogUrl('special') ?>" class="im-nav-dropdown-link<?= $specialActive ? ' active' : '' ?>"><?= __t('special') ?></a>
        </div>
        <div class="im-nav-dropdown-group">
            <span class="im-nav-dropdown-label"><?= __t('nav_group_deal') ?></span>
            <a href="<?= filterUrl([], ['is_leasing', 'page']) ?>" class="im-nav-dropdown-link<?= empty($q['is_leasing']) ? ' active' : '' ?>"><?= __t('buy') ?></a>
            <a href="<?= filterUrl(['is_leasing' => '1'], ['page']) ?>" class="im-nav-dropdown-link<?= !empty($q['is_leasing']) ? ' active' : '' ?>"><?= __t('leasing') ?></a>
        </div>
        <div class="im-nav-dropdown-group">
            <span class="im-nav-dropdown-label"><?= __t('nav_group_stock') ?></span>
            <a href="<?= filterUrl([], ['is_en_route', 'is_on_order', 'page']) ?>" class="im-nav-dropdown-link<?= $stockActive ? ' active' : '' ?>"><?= __t('in_stock') ?></a>
            <a href="<?= filterUrl(['is_en_route' => '1'], ['is_on_order', 'page']) ?>" class="im-nav-dropdown-link<?= !empty($q['is_en_route']) ? ' active' : '' ?>"><?= __t('en_route') ?></a>
            <a href="<?= filterUrl(['is_on_order' => '1'], ['is_en_route', 'page']) ?>" class="im-nav-dropdown-link<?= !empty($q['is_on_order']) ? ' active' : '' ?>"><?= __t('on_order') ?></a>
        </div>
        <div class="im-nav-dropdown-group">
            <span class="im-nav-dropdown-label"><?= __t('nav_group_condition') ?></span>
            <a href="<?= filterUrl([], ['condition_type', 'page']) ?>" class="im-nav-dropdown-link<?= empty($q['condition_type']) ? ' active' : '' ?>"><?= __t('all') ?></a>
            <a href="<?= filterUrl(['condition_type' => 'new'], ['page']) ?>" class="im-nav-dropdown-link<?= ($q['condition_type'] ?? '') === 'new' ? ' active' : '' ?>"><?= __t('new_cars') ?></a>
            <a href="<?= filterUrl(['condition_type' => 'like_new'], ['page']) ?>" class="im-nav-dropdown-link<?= ($q['condition_type'] ?? '') === 'like_new' ? ' active' : '' ?>"><?= __t('like_new') ?></a>
            <a href="<?= filterUrl(['condition_type' => 'used'], ['page']) ?>" class="im-nav-dropdown-link<?= ($q['condition_type'] ?? '') === 'used' ? ' active' : '' ?>"><?= __t('used') ?></a>
        </div>
    </div>
</div>