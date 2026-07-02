<?php
/** @var array $filters */
/** @var array $brands */
/** @var int $total */
$filters = $filters ?? buildCarFilters($_GET);
$brands = $brands ?? [];
$total = (int)($total ?? 0);
$catalogAction = $catalogAction ?? catalogUrl($filters['listing_type'] ?? 'car');
$models = !empty($filters['brand_id']) ? getModelsByBrand((int)$filters['brand_id']) : [];
$spoilerOpen = !empty($_GET['spoiler'])
    || !empty($filters['brand_id'])
    || !empty($filters['model_id'])
    || !empty($filters['year_from']);
$spoilerSkipKeys = [
    'listing_type', 'brand_id', 'model_id', 'year_from', 'year_to', 'price_from', 'price_to',
    'body_type', 'transmission', 'fuel_type', 'drive_type', 'color', 'region', 'vin', 'spoiler',
];
?>
<div class="im-search-spoiler<?= $spoilerOpen ? ' open' : '' ?>" id="searchSpoiler">
    <div class="container-im">
        <form method="get" action="<?= e($catalogAction) ?>" class="im-spoiler-form" id="spoilerForm">
            <?php if (!empty($filters['listing_type'])): ?>
            <input type="hidden" name="listing_type" value="<?= e($filters['listing_type']) ?>">
            <?php endif; ?>
            <?php foreach ($_GET as $k => $v):
                if (in_array($k, $spoilerSkipKeys, true) || is_array($v)) {
                    continue;
                }
            ?>
            <input type="hidden" name="<?= e($k) ?>" value="<?= e((string)$v) ?>">
            <?php endforeach; ?>
            <input type="hidden" name="spoiler" value="1">
            <div class="im-spoiler-grid">
                <div class="im-field">
                    <label><?= __t('brand') ?></label>
                    <select name="brand_id" id="brandSelectSpoiler" class="js-brand-select" data-model-target="modelSelectSpoiler">
                        <option value=""><?= __t('all') ?></option>
                        <?php foreach ($brands as $b): ?>
                        <option value="<?= (int)$b['id'] ?>"<?= ($filters['brand_id'] ?? null) == $b['id'] ? ' selected' : '' ?>><?= e($b['name']) ?> (<?= (int)$b['car_count'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="im-field">
                    <label><?= __t('model') ?></label>
                    <select name="model_id" id="modelSelectSpoiler" class="js-model-select" data-all-label="<?= e(__t('all')) ?>">
                        <option value=""><?= __t('all') ?></option>
                        <?php foreach ($models as $m): ?>
                        <option value="<?= (int)$m['id'] ?>"<?= ($filters['model_id'] ?? null) == $m['id'] ? ' selected' : '' ?>><?= e($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="im-field">
                    <label><?= __t('generation') ?></label>
                    <input type="text" name="q" placeholder="<?= __t('brand_model') ?>" value="<?= e($filters['q'] ?? '') ?>">
                </div>
                <div class="im-field-row">
                    <div class="im-field">
                        <label><?= __t('year_from') ?></label>
                        <input type="number" name="year_from" min="1990" max="2030" value="<?= e((string)($filters['year_from'] ?? '')) ?>">
                    </div>
                    <div class="im-field">
                        <label><?= __t('year_to') ?></label>
                        <input type="number" name="year_to" min="1990" max="2030" value="<?= e((string)($filters['year_to'] ?? '')) ?>">
                    </div>
                </div>
                <div class="im-field">
                    <label><?= ($filters['listing_type'] ?? '') === 'special' ? __t('equipment_type') : __t('body') ?></label>
                    <select name="body_type">
                        <option value=""><?= __t('all') ?></option>
                        <?php foreach ((($filters['listing_type'] ?? '') === 'special') ? getSpecialBodyTypes() : getBodyTypes()) as $bt): ?>
                        <option value="<?= $bt ?>"<?= ($filters['body_type'] ?? '') === $bt ? ' selected' : '' ?>><?= __t($bt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="im-field">
                    <label><?= __t('transmission') ?></label>
                    <select name="transmission">
                        <option value=""><?= __t('all') ?></option>
                        <?php foreach (getTransmissions() as $tr): ?>
                        <option value="<?= $tr ?>"<?= ($filters['transmission'] ?? '') === $tr ? ' selected' : '' ?>><?= __t($tr) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="im-field">
                    <label><?= __t('engine') ?></label>
                    <select name="fuel_type">
                        <option value=""><?= __t('all') ?></option>
                        <?php foreach (getFuelTypes() as $f): ?>
                        <option value="<?= $f ?>"<?= ($filters['fuel_type'] ?? '') === $f ? ' selected' : '' ?>><?= __t($f) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="im-field">
                    <label><?= __t('drive') ?></label>
                    <select name="drive_type">
                        <option value=""><?= __t('all') ?></option>
                        <?php foreach (getDriveTypes() as $d): ?>
                        <option value="<?= $d ?>"<?= ($filters['drive_type'] ?? '') === $d ? ' selected' : '' ?>><?= __t($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="im-field">
                    <label><?= __t('color') ?></label>
                    <input type="text" name="color" value="<?= e($filters['color'] ?? '') ?>">
                </div>
                <div class="im-field">
                    <label><?= __t('region') ?></label>
                    <select name="region">
                        <option value=""><?= __t('all') ?></option>
                        <?php foreach (getRegions() as $r): ?>
                        <option value="<?= e($r) ?>"<?= ($filters['region'] ?? '') === $r ? ' selected' : '' ?>><?= e($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="im-spoiler-actions">
                <a href="<?= e($catalogAction) ?>" class="im-btn"><?= __t('reset') ?></a>
                <button type="submit" class="im-btn im-btn-primary"><?= __t('show_results') ?> <?= $total ?></button>
            </div>
        </form>
    </div>
</div>