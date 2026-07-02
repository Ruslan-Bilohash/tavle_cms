<?php
if (!defined('DEMO_BANNER') || !DEMO_BANNER) {
    return;
}
$demoProductUrl = 'https://bilohash.com/news/tavle.html';
?>
<div class="im-demo-banner" role="note" aria-label="<?= e(__t('demo_banner_aria')) ?>">
    <div class="container-im im-demo-banner-inner">
        <i class="bi bi-info-circle-fill im-demo-banner-icon" aria-hidden="true"></i>
        <p class="im-demo-banner-text"><?= e(__t('demo_banner')) ?></p>
        <a href="<?= e($demoProductUrl) ?>" class="im-demo-banner-link" target="_blank" rel="noopener"><?= e(__t('demo_banner_link')) ?></a>
    </div>
</div>