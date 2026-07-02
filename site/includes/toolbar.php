<?php
/** @var array $filters */
/** @var int $total */
$q = $filters;
?>
<section class="im-toolbar sticky-toolbar" id="catalogToolbar">
    <div class="container-im">
        <div class="im-toolbar-compact">
            <button type="button" class="im-toolbar-toggle im-btn im-btn-dark" id="toolbarToggle" aria-expanded="false" aria-controls="toolbarBody">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span><?= __t('search') ?></span>
                <i class="bi bi-chevron-down im-toolbar-chevron" aria-hidden="true"></i>
            </button>
            <span class="im-toolbar-compact-count"><?= (int)$total ?></span>
        </div>
        <div class="im-toolbar-body" id="toolbarBody">
        <form class="im-search-row" method="get" action="<?= e($catalogAction ?? url()) ?>"
              toolname="searchListings"
              tooldescription="Search vehicle listings by brand, model or keyword.">
            <?php if (!empty($filters['listing_type'])): ?>
            <input type="hidden" name="listing_type" value="<?= e($filters['listing_type']) ?>">
            <?php endif; ?>
            <?php foreach ($_GET as $k => $v): if (in_array($k, ['q', 'page', 'sort', 'period'], true)) continue; ?>
            <input type="hidden" name="<?= e($k) ?>" value="<?= e((string)$v) ?>">
            <?php endforeach; ?>
            <label class="im-visually-hidden" for="toolbar-q"><?= __t('brand_model') ?></label>
            <input type="search" name="q" id="toolbar-q" class="im-search-input" placeholder="<?= __t('brand_model') ?>" value="<?= e($q['q'] ?? '') ?>" autocomplete="off" toolparamdescription="Brand or model keyword to filter listings.">
            <button type="button" class="im-btn im-btn-filters" id="toggleFilters" aria-expanded="false" aria-controls="filterPanel" aria-label="<?= e(__t('filters')) ?>">
                <i class="bi bi-funnel" aria-hidden="true"></i>
                <span class="im-btn-filters-text"><?= __t('filters') ?></span>
            </button>
            <select name="sort" class="im-sort-select" aria-label="<?= __t('sort') ?>">
                <option value="newest"<?= ($q['sort'] ?? '') === 'newest' ? ' selected' : '' ?>><?= __t('sort_newest') ?></option>
                <option value="price_asc"<?= ($q['sort'] ?? '') === 'price_asc' ? ' selected' : '' ?>><?= __t('sort_price_asc') ?></option>
                <option value="price_desc"<?= ($q['sort'] ?? '') === 'price_desc' ? ' selected' : '' ?>><?= __t('sort_price_desc') ?></option>
                <option value="year_desc"<?= ($q['sort'] ?? '') === 'year_desc' ? ' selected' : '' ?>><?= __t('sort_year_desc') ?></option>
            </select>
            <select name="period" class="im-sort-select" aria-label="<?= __t('period') ?>">
                <option value=""<?= empty($q['period']) ? ' selected' : '' ?>><?= __t('period_all') ?></option>
                <option value="day"<?= ($q['period'] ?? '') === 'day' ? ' selected' : '' ?>><?= __t('period_day') ?></option>
                <option value="week"<?= ($q['period'] ?? '') === 'week' ? ' selected' : '' ?>><?= __t('period_week') ?></option>
                <option value="month"<?= ($q['period'] ?? '') === 'month' ? ' selected' : '' ?>><?= __t('period_month') ?></option>
                <option value="year"<?= ($q['period'] ?? '') === 'year' ? ' selected' : '' ?>><?= __t('period_year') ?></option>
            </select>
            <button type="submit" class="im-btn im-btn-dark im-search-submit"><?= __t('show_results') ?> <?= (int)$total ?></button>
        </form>
        </div>
    </div>
</section>