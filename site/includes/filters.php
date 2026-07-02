<?php
/** @var array $filters */
/** @var array $brands */
/** @var int $total */
$models = !empty($filters['brand_id']) ? getModelsByBrand((int)$filters['brand_id']) : [];
?>
<div class="im-filter-overlay" id="filterOverlay" aria-hidden="true"></div>
<aside class="im-filter-panel" id="filterPanel" aria-label="<?= e(__t('filters')) ?>" aria-hidden="true">
    <div class="im-filter-head">
        <h3><?= __t('filters') ?></h3>
        <button type="button" class="im-filter-close" id="closeFilters" aria-label="<?= e(__t('close_filters')) ?>">&times;</button>
    </div>
    <form id="filterForm" method="get" action="<?= e($catalogAction ?? url()) ?>"
          toolname="filterListings"
          tooldescription="Filter car, plate or equipment listings by price, year, brand and other criteria.">
        <?php if (!empty($filters['listing_type'])): ?>
        <input type="hidden" name="listing_type" value="<?= e($filters['listing_type']) ?>">
        <?php endif; ?>
        <?php if (($filters['listing_type'] ?? '') === 'plate'): ?>
        <div class="im-field">
            <label for="ff-plate"><?= __t('plate_number') ?></label>
            <input type="text" name="plate_number" id="ff-plate" maxlength="20" value="<?= e($filters['plate_number'] ?? '') ?>">
        </div>
        <?php elseif (($filters['listing_type'] ?? '') !== 'special'): ?>
        <div class="im-field">
            <label for="ff-vin"><?= __t('search_vin') ?></label>
            <input type="text" name="vin" id="ff-vin" maxlength="17" value="<?= e($filters['vin'] ?? '') ?>" toolparamdescription="Vehicle identification number (VIN).">
        </div>
        <?php endif; ?>
        <?php if (($filters['listing_type'] ?? '') !== 'plate'): ?>
        <div class="im-field">
            <label for="ff-brand"><?= __t('brand') ?></label>
            <select name="brand_id" id="ff-brand" class="js-brand-select" data-model-target="modelSelectPanel" toolparamdescription="Vehicle or equipment brand.">
                <option value=""><?= __t('all') ?></option>
                <?php foreach ($brands as $b): ?>
                <option value="<?= (int)$b['id'] ?>"<?= $filters['brand_id'] == $b['id'] ? ' selected' : '' ?>><?= e($b['name']) ?> (<?= (int)$b['car_count'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="im-field">
            <label for="modelSelectPanel"><?= __t('model') ?></label>
            <select name="model_id" id="modelSelectPanel" class="js-model-select" data-all-label="<?= e(__t('all')) ?>" toolparamdescription="Model within the selected brand.">
                <option value=""><?= __t('all') ?></option>
                <?php foreach ($models as $m): ?>
                <option value="<?= (int)$m['id'] ?>"<?= $filters['model_id'] == $m['id'] ? ' selected' : '' ?>><?= e($m['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="im-field-row">
            <div class="im-field">
                <label for="ff-year-from"><?= __t('year_from') ?></label>
                <input type="number" name="year_from" id="ff-year-from" min="1990" max="2030" value="<?= e((string)($filters['year_from'] ?? '')) ?>">
            </div>
            <div class="im-field">
                <label for="ff-year-to"><?= __t('year_to') ?></label>
                <input type="number" name="year_to" id="ff-year-to" min="1990" max="2030" value="<?= e((string)($filters['year_to'] ?? '')) ?>">
            </div>
        </div>
        <div class="im-field-row">
            <div class="im-field">
                <label for="ff-price-from"><?= __t('price_from') ?></label>
                <input type="number" name="price_from" id="ff-price-from" min="0" value="<?= e((string)($filters['price_from'] ?? '')) ?>">
            </div>
            <div class="im-field">
                <label for="ff-price-to"><?= __t('price_to') ?></label>
                <input type="number" name="price_to" id="ff-price-to" min="0" value="<?= e((string)($filters['price_to'] ?? '')) ?>">
            </div>
        </div>
        <?php if (($filters['listing_type'] ?? '') === 'special'): ?>
        <div class="im-field">
            <label for="ff-body"><?= __t('equipment_type') ?></label>
            <select name="body_type" id="ff-body">
                <option value=""><?= __t('all') ?></option>
                <?php foreach (getSpecialBodyTypes() as $bt): ?>
                <option value="<?= $bt ?>"<?= ($filters['body_type'] ?? '') === $bt ? ' selected' : '' ?>><?= __t($bt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php elseif (($filters['listing_type'] ?? '') !== 'plate'): ?>
        <div class="im-field">
            <label for="ff-body-car"><?= __t('body') ?></label>
            <select name="body_type" id="ff-body-car">
                <option value=""><?= __t('all') ?></option>
                <?php foreach (getBodyTypes() as $bt): ?>
                <option value="<?= $bt ?>"<?= ($filters['body_type'] ?? '') === $bt ? ' selected' : '' ?>><?= __t($bt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="im-field">
            <label for="ff-transmission"><?= __t('transmission') ?></label>
            <select name="transmission" id="ff-transmission">
                <option value=""><?= __t('all') ?></option>
                <?php foreach (getTransmissions() as $tr): ?>
                <option value="<?= $tr ?>"<?= ($filters['transmission'] ?? '') === $tr ? ' selected' : '' ?>><?= __t($tr) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="im-field">
            <label for="ff-fuel"><?= __t('engine') ?></label>
            <select name="fuel_type" id="ff-fuel">
                <option value=""><?= __t('all') ?></option>
                <?php foreach (getFuelTypes() as $f): ?>
                <option value="<?= $f ?>"<?= ($filters['fuel_type'] ?? '') === $f ? ' selected' : '' ?>><?= __t($f) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <?php if (($filters['listing_type'] ?? '') !== 'plate'): ?>
        <div class="im-field">
            <label for="ff-drive"><?= __t('drive') ?></label>
            <select name="drive_type" id="ff-drive">
                <option value=""><?= __t('all') ?></option>
                <?php foreach (getDriveTypes() as $d): ?>
                <option value="<?= $d ?>"<?= ($filters['drive_type'] ?? '') === $d ? ' selected' : '' ?>><?= __t($d) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="im-field">
            <label for="ff-color"><?= __t('color') ?></label>
            <input type="text" name="color" id="ff-color" value="<?= e($filters['color'] ?? '') ?>">
        </div>
        <?php endif; ?>
        <div class="im-field">
            <label for="ff-region"><?= __t('region') ?></label>
            <select name="region" id="ff-region">
                <option value=""><?= __t('all') ?></option>
                <?php foreach (getRegions() as $r): ?>
                <option value="<?= e($r) ?>"<?= ($filters['region'] ?? '') === $r ? ' selected' : '' ?>><?= e($r) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="im-filter-submit"><?= __t('show_results') ?> <?= (int)($total ?? 0) ?></button>
        <a href="<?= e($catalogAction ?? url()) ?>" class="im-btn im-filter-reset"><?= __t('reset') ?></a>
    </form>
</aside>