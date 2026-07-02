<?php
/** @var array $car */
/** @var int $cardIndex */
$carId = (int)$car['id'];
$listingType = $car['listing_type'] ?? 'car';
$cardIndex = $cardIndex ?? 0;
$imageFallback = e(listingImageUrl(null, $carId, $listingType));
$images = getCarPreviewImages($carId);
if (empty($images)) {
    $images = [null];
}
$primaryFilename = $images[0];
$extraImages = array_slice($images, 1);
$primaryUrl = listingImageUrl($primaryFilename, $carId, $listingType);
$primarySrcset = listingImageSrcset($primaryFilename, $carId, $listingType);
$subtitle = [];
if ($car['generation']) $subtitle[] = e($car['generation']);
if ($car['engine_volume']) $subtitle[] = $car['engine_volume'] . ' ' . __t('liters');
if ($car['transmission']) $subtitle[] = __t($car['transmission']);
if ($car['fuel_type']) $subtitle[] = __t($car['fuel_type']);
if ($car['engine_power']) $subtitle[] = (int)$car['engine_power'] . ' ' . __t('hp');
if ($car['body_type']) $subtitle[] = __t($car['body_type']);
?>
<article class="im-car-card">
    <div class="im-car-media">
        <a href="<?= e(carUrl($carId, $car['slug'] ?? '')) ?>" class="im-car-slider" data-slider<?= $extraImages ? ' data-slider-extra="' . e(json_encode(array_map(fn($f) => listingImageUrl($f, $carId, $listingType), $extraImages), JSON_UNESCAPED_SLASHES)) . '"' : '' ?>>
            <div class="im-slider-track">
                <img src="<?= e($primaryUrl) ?>"<?= $primarySrcset ? ' srcset="' . e($primarySrcset) . '" sizes="(max-width:767px) 100vw, 33vw"' : '' ?> alt="<?= e($car['title']) ?>" width="400" height="300" decoding="async"<?= $cardIndex === 0 ? ' fetchpriority="high" loading="eager"' : ' loading="lazy"' ?> class="active" data-fallback="<?= $imageFallback ?>">
            </div>
            <?php if (count($images) > 1): ?>
            <div class="im-slider-dots" aria-hidden="true">
                <?php foreach ($images as $i => $_): ?>
                <span class="<?= $i === 0 ? 'active' : '' ?>"></span>
                <?php endforeach; ?>
            </div>
            <button type="button" class="im-slider-nav im-slider-prev" aria-label="<?= e(__t('slider_prev')) ?>"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
            <button type="button" class="im-slider-nav im-slider-next" aria-label="<?= e(__t('slider_next')) ?>"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
            <?php endif; ?>
        </a>
        <div class="im-car-badges">
            <?php if ($listingType === 'special' && $car['body_type']): ?>
            <span class="im-badge im-badge-vin"><i class="bi bi-truck" aria-hidden="true"></i> <?= __t($car['body_type']) ?></span>
            <?php elseif ($car['vin_verified']): ?>
            <span class="im-badge im-badge-vin"><i class="bi bi-shield-check" aria-hidden="true"></i> <?= __t('vin_verified') ?></span>
            <?php endif; ?>
            <?php if ($car['is_featured']): ?>
            <span class="im-badge im-badge-exclusive"><?= __t('only_here') ?></span>
            <?php endif; ?>
            <?php if ($car['is_leasing']): ?>
            <span class="im-badge im-badge-leasing"><?= __t('leasing') ?></span>
            <?php endif; ?>
            <?php if ($car['is_exchange']): ?>
            <span class="im-badge im-badge-exchange"><?= __t('exchange') ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="im-car-body">
        <a href="<?= e(carUrl($carId, $car['slug'] ?? '')) ?>">
            <h3 class="im-car-title"><?= e($car['title']) ?></h3>
            <?php if ($subtitle): ?>
            <p class="im-car-subtitle"><?= implode(', ', $subtitle) ?></p>
            <?php endif; ?>
            <?php
            $excerpt = trim(strip_tags(getCarDescription($car)));
            if ($excerpt !== ''):
                if (mb_strlen($excerpt) > 160) {
                    $excerpt = mb_substr($excerpt, 0, 157) . '…';
                }
            ?>
            <p class="im-car-excerpt"><?= e($excerpt) ?></p>
            <?php endif; ?>
        </a>
        <div class="im-car-price-row">
            <?php if ($car['price_old_usd']): ?>
            <span class="im-price-old"><?= formatPrice((int)$car['price_old_usd']) ?></span>
            <?php endif; ?>
            <span class="im-price-main"><?= formatPrice((int)$car['price_usd']) ?></span>
            <?php $sec = formatSecondaryPrice((int)$car['price_usd']); if ($sec): ?>
            <span class="im-price-secondary"><?= $sec ?></span>
            <?php endif; ?>
        </div>
        <div class="im-car-specs">
            <div class="im-spec">
                <i class="bi bi-speedometer2 im-spec-icon" aria-hidden="true"></i>
                <span class="im-spec-label"><?= $listingType === 'special' ? __t('operating_hours') : __t('mileage') ?></span>
                <span class="im-spec-value"><?= $listingType === 'special'
                    ? ($car['mileage'] ? number_format((int)$car['mileage']) . ' ' . __t('hours') : '—')
                    : formatMileage($car['mileage'] ? (int)$car['mileage'] : null) ?></span>
            </div>
            <div class="im-spec">
                <i class="bi bi-fuel-pump im-spec-icon" aria-hidden="true"></i>
                <span class="im-spec-label"><?= __t('engine') ?></span>
                <span class="im-spec-value"><?= __t($car['fuel_type']) ?><?= $car['engine_volume'] ? ', ' . $car['engine_volume'] . ' ' . __t('liters') : '' ?></span>
            </div>
            <div class="im-spec">
                <i class="bi bi-gear im-spec-icon" aria-hidden="true"></i>
                <span class="im-spec-label"><?= __t('transmission') ?></span>
                <span class="im-spec-value"><?= __t($car['transmission']) ?></span>
            </div>
            <div class="im-spec">
                <i class="bi bi-distribute-horizontal im-spec-icon" aria-hidden="true"></i>
                <span class="im-spec-label"><?= __t('drive') ?></span>
                <span class="im-spec-value"><?= __t($car['drive_type']) ?></span>
            </div>
            <?php if ($car['city']): ?>
            <div class="im-spec">
                <i class="bi bi-geo-alt im-spec-icon" aria-hidden="true"></i>
                <span class="im-spec-label"><?= __t('location') ?></span>
                <span class="im-spec-value"><?= e($car['city']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($car['dealer_name'])): ?>
        <div class="im-dealer-row">
            <span class="im-dealer-avatar" aria-hidden="true"><?= mb_strtoupper(mb_substr($car['dealer_name'], 0, 1)) ?></span>
            <span><?= e($car['dealer_name']) ?></span>
        </div>
        <?php endif; ?>
        <?php
        $listingPhone = getListingPhone($car);
        $hasVin = !empty($car['vin']);
        if ($listingPhone || $hasVin):
        ?>
        <div class="im-car-actions">
            <?php if ($listingPhone): ?>
            <button type="button" class="im-btn-reveal" data-reveal="phone" data-value="<?= e($listingPhone) ?>">
                <i class="bi bi-telephone" aria-hidden="true"></i>
                <span class="im-reveal-label"><?= __t('show_phone') ?></span>
            </button>
            <?php endif; ?>
            <?php if ($hasVin): ?>
            <button type="button" class="im-btn-reveal" data-reveal="vin" data-value="<?= e($car['vin']) ?>">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                <span class="im-reveal-label"><?= __t('show_vin') ?></span>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</article>