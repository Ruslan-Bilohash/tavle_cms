<?php
/** @var array $car */
$carId = (int)$car['id'];
$listingType = $car['listing_type'] ?? 'plate';
$imageFallback = e(getCarImageUrl(null, $carId, $listingType));
$images = getCarPreviewImages($carId);
if (empty($images)) {
    $images = [null];
}
$plateNumber = trim((string)($car['plate_number'] ?? ''));
if ($plateNumber === '') {
    $plateNumber = $car['title'];
}
?>
<article class="im-car-card im-plate-card">
    <div class="im-car-media">
        <a href="<?= e(carUrl($carId, $car['slug'] ?? '')) ?>" class="im-plate-visual">
            <div class="im-plate-frame">
                <span class="im-plate-number"><?= e($plateNumber) ?></span>
                <?php if (!empty($car['region'])): ?>
                <span class="im-plate-region"><?= e($car['region']) ?></span>
                <?php endif; ?>
            </div>
        </a>
        <div class="im-car-badges">
            <?php if ($car['is_featured']): ?>
            <span class="im-badge im-badge-exclusive"><?= __t('only_here') ?></span>
            <?php endif; ?>
            <?php if (!empty($car['model_name'])): ?>
            <span class="im-badge im-badge-leasing"><?= e($car['model_name']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="im-car-body">
        <a href="<?= e(carUrl($carId, $car['slug'] ?? '')) ?>">
            <h3 class="im-car-title"><?= e($car['title']) ?></h3>
            <?php
            $excerpt = trim(strip_tags(getCarDescription($car)));
            if ($excerpt !== ''):
                if (mb_strlen($excerpt) > 140) {
                    $excerpt = mb_substr($excerpt, 0, 137) . '…';
                }
            ?>
            <p class="im-car-excerpt"><?= e($excerpt) ?></p>
            <?php endif; ?>
        </a>
        <div class="im-car-price-row">
            <span class="im-price-main"><?= formatPrice((int)$car['price_usd']) ?></span>
            <?php $sec = formatSecondaryPrice((int)$car['price_usd']); if ($sec): ?>
            <span class="im-price-secondary"><?= $sec ?></span>
            <?php endif; ?>
        </div>
        <div class="im-car-specs im-car-specs-compact">
            <?php if ($car['city']): ?>
            <div class="im-spec">
                <i class="bi bi-geo-alt im-spec-icon"></i>
                <span class="im-spec-label"><?= __t('location') ?></span>
                <span class="im-spec-value"><?= e($car['city']) ?></span>
            </div>
            <?php endif; ?>
            <div class="im-spec">
                <i class="bi bi-calendar3 im-spec-icon"></i>
                <span class="im-spec-label"><?= __t('year') ?></span>
                <span class="im-spec-value"><?= (int)$car['year'] ?></span>
            </div>
        </div>
        <?php $listingPhone = getListingPhone($car); ?>
        <div class="im-car-actions">
            <?php if ($listingPhone): ?>
            <button type="button" class="im-btn-reveal" data-reveal="phone" data-value="<?= e($listingPhone) ?>">
                <i class="bi bi-telephone"></i>
                <span class="im-reveal-label"><?= __t('show_phone') ?></span>
            </button>
            <?php endif; ?>
            <button type="button" class="im-btn-reveal" data-reveal="plate" data-value="<?= e($plateNumber) ?>">
                <i class="bi bi-123"></i>
                <span class="im-reveal-label"><?= __t('show_plate') ?></span>
            </button>
        </div>
    </div>
</article>