    </main>
    <?php
    $ecoItems = getEcosystemItems();
    $footerPhone = getSetting('site_phone');
    $footerEmail = getSetting('site_email');
    $demoDealer = getDemoDealer();
    $demoHours = getSetting('demo_company_hours', 'Mon–Fri 09:00–18:00');
    $norwayCities = [
        ['label' => 'Oslo', 'url' => 'https://bilohash.com/tavle/car-dealership-no/'],
        ['label' => 'Bergen', 'url' => 'https://bilohash.com/tavle/ev-cars/'],
        ['label' => 'Drammen', 'url' => 'https://bilohash.com/tavle/private-seller/'],
        ['label' => 'Stavanger', 'url' => 'https://bilohash.com/tavle/special-equipment/'],
        ['label' => 'Trondheim', 'url' => 'https://bilohash.com/tavle/import-export/'],
    ];
    ?>
    <footer class="im-footer im-footer-advanced">
        <?php if ($demoDealer): ?>
        <div class="im-footer-company-band">
            <div class="container-im im-footer-company">
                <div class="im-footer-company-card">
                    <div class="im-footer-company-avatar" aria-hidden="true"><?= mb_strtoupper(mb_substr($demoDealer['name'], 0, 1)) ?></div>
                    <div class="im-footer-company-body">
                        <div class="im-footer-company-top">
                            <h3><?= e($demoDealer['name']) ?></h3>
                            <?php if (!empty($demoDealer['is_verified'])): ?>
                            <span class="im-footer-verified"><i class="bi bi-patch-check-fill"></i> <?= e(__t('verified_dealer')) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($demoDealer['description'])): ?>
                        <p><?= e($demoDealer['description']) ?></p>
                        <?php endif; ?>
                        <ul class="im-footer-company-contacts">
                            <?php if (!empty($demoDealer['phone'])): ?>
                            <li><a href="tel:<?= e(preg_replace('/\s+/', '', $demoDealer['phone'])) ?>"><i class="bi bi-telephone"></i> <?= e($demoDealer['phone']) ?></a></li>
                            <?php endif; ?>
                            <?php if (!empty($demoDealer['email'])): ?>
                            <li><a href="mailto:<?= e($demoDealer['email']) ?>"><i class="bi bi-envelope"></i> <?= e($demoDealer['email']) ?></a></li>
                            <?php endif; ?>
                            <?php if (!empty($demoDealer['address'])): ?>
                            <li><i class="bi bi-geo-alt"></i> <?= e($demoDealer['address']) ?></li>
                            <?php endif; ?>
                            <li><i class="bi bi-clock"></i> <?= e($demoHours) ?></li>
                        </ul>
                    </div>
                    <div class="im-footer-company-cta">
                        <a href="tel:<?= e(preg_replace('/\s+/', '', $demoDealer['phone'] ?? $footerPhone)) ?>" class="im-btn im-btn-primary"><i class="bi bi-telephone"></i> <?= e(__t('call_dealer')) ?></a>
                        <a href="<?= url('add.php') ?>" class="im-btn"><i class="bi bi-plus-lg"></i> <?= e(__t('sell_your_car')) ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="container-im im-footer-top">
            <div class="im-footer-brand">
                <a href="<?= url() ?>" class="im-footer-logo">
                    <span class="im-logo-mark"><i class="bi bi-car-front-fill"></i></span>
                    <span><?= e(getSetting('site_name', SITE_NAME)) ?></span>
                </a>
                <p class="im-footer-about"><?= __t('footer_about') ?></p>
                <div class="im-footer-badges">
                    <span><i class="bi bi-geo-alt"></i> Norway &amp; Europe</span>
                    <span><i class="bi bi-translate"></i> 4 <?= __t('languages') ?></span>
                    <span><i class="bi bi-shield-check"></i> Schema.org</span>
                </div>
            </div>
            <div class="im-footer-col">
                <h4><?= __t('footer_catalog') ?></h4>
                <ul class="im-footer-list">
                    <li><a href="<?= url() ?>"><?= __t('cars') ?></a></li>
                    <li><a href="<?= catalogUrl('plate') ?>"><?= __t('plates') ?></a></li>
                    <li><a href="<?= catalogUrl('special') ?>"><?= __t('special') ?></a></li>
                    <li><a href="<?= url('news.php') ?>"><?= __t('news') ?></a></li>
                    <li><a href="<?= url('add.php') ?>" class="im-footer-add"><i class="bi bi-plus-lg"></i> <?= __t('add_listing') ?></a></li>
                </ul>
            </div>
            <div class="im-footer-col">
                <h4><?= __t('footer_solutions') ?></h4>
                <ul class="im-footer-list">
                    <li><a href="https://bilohash.com/tavle/solutions.php" rel="related"><?= __t('footer_solutions_all') ?></a></li>
                    <li><a href="https://bilohash.com/tavle/car-dealership-no/" rel="related"><?= __t('footer_dealer_no') ?></a></li>
                    <li><a href="https://bilohash.com/tavle/license-plates/" rel="related"><?= __t('plates') ?></a></li>
                    <li><a href="https://bilohash.com/tavle/ev-cars/" rel="related"><?= __t('footer_ev') ?></a></li>
                    <li><a href="https://bilohash.com/tavle/" rel="author"><?= __t('footer_script') ?></a></li>
                </ul>
            </div>
            <div class="im-footer-col">
                <h4><?= __t('footer_contacts') ?></h4>
                <ul class="im-footer-list">
                    <?php if ($footerPhone): ?>
                    <li><a href="tel:<?= e(preg_replace('/\s+/', '', $footerPhone)) ?>"><i class="bi bi-telephone"></i> <?= e($footerPhone) ?></a></li>
                    <?php endif; ?>
                    <?php if ($footerEmail): ?>
                    <li><a href="mailto:<?= e($footerEmail) ?>"><i class="bi bi-envelope"></i> <?= e($footerEmail) ?></a></li>
                    <?php endif; ?>
                    <li><a href="<?= url(isAdmin() ? 'admin/' : 'admin/login.php') ?>"><i class="bi bi-shield-lock"></i> <?= __t('admin_panel') ?></a></li>
                    <?php if (isLoggedIn()): ?>
                    <li><a href="<?= url('my-listings.php') ?>"><i class="bi bi-list-ul"></i> <?= __t('my_listings') ?></a></li>
                    <?php else: ?>
                    <li><a href="<?= url('admin/login.php') ?>"><i class="bi bi-box-arrow-in-right"></i> <?= __t('login') ?></a></li>
                    <?php endif; ?>
                    <li><a href="https://bilohash.com/website/" rel="related"><i class="bi bi-code-slash"></i> <?= __t('footer_webdev') ?></a></li>
                </ul>
            </div>
            <div class="im-footer-col">
                <h4><?= __t('footer_developer') ?></h4>
                <ul class="im-footer-list">
                    <li><a href="https://bilohash.com/" rel="author">bilohash.com</a></li>
                    <li><a href="https://bilohash.com/news/tavle.html" rel="related"><?= __t('footer_release') ?></a></li>
                    <li><a href="https://bilohash.com/news/" rel="related"><?= __t('footer_all_products') ?></a></li>
                    <li><a href="<?= url('sitemap.xml') ?>">Sitemap</a></li>
                    <li><a href="<?= e(llmsTxtUrl()) ?>"><?= __t('footer_llms') ?></a></li>
                </ul>
            </div>
        </div>
        <div class="container-im im-footer-norway">
            <span class="im-footer-norway-label"><i class="bi bi-flag"></i> <?= __t('footer_norway') ?></span>
            <?php foreach ($norwayCities as $city): ?>
            <a href="<?= e($city['url']) ?>" rel="related"><?= e($city['label']) ?></a>
            <?php endforeach; ?>
            <a href="https://bilohash.com/pizza/" rel="related">Pizza CMS</a>
            <a href="https://bilohash.com/booking/" rel="related">Booking CMS</a>
            <a href="https://mapsme.no" rel="related" target="_blank">mapsme.no</a>
        </div>
        <?php if ($ecoItems): ?>
        <?php
        $bh_eco_lang = getCurrentLang();
        $bh_eco_exclude = 'tavle';
        $t = ['ecosystem' => ['items' => $ecoItems], 'footer' => ['ecosystem' => __t('footer_ecosystem')]];
        $lang = $bh_eco_lang;
        require dirname(BILEN_ROOT, 2) . '/includes/ecosystem-footer-pills.php';
        ?>
        <?php endif; ?>
        <div class="container-im im-footer-copy">
            <span>&copy; <?= date('Y') ?> <?= e(getSetting('site_name', SITE_NAME)) ?> · Drammen, Norway</span>
            <nav class="im-footer-legal">
                <a href="https://bilohash.com/" rel="author">BILOHASH</a>
                <a href="https://bilohash.com/website/" rel="related"><?= __t('footer_webdev') ?></a>
                <a href="https://mapsme.no" rel="related" target="_blank">mapsme.no</a>
            </nav>
        </div>
    </footer>

    <?php if (getSetting('cookie_consent_enabled', '1') === '1'): ?>
    <div id="cookieConsent" class="im-cookie" role="dialog" aria-label="<?= e(__t('cookie_text')) ?>" hidden>
        <div class="im-cookie-inner">
            <p class="im-cookie-text"><?= e(getSetting('cookie_consent_text_' . getCurrentLang(), __t('cookie_text'))) ?></p>
            <div class="im-cookie-actions">
                <button class="im-btn im-btn-primary" id="cookieAccept" type="button"><?= __t('cookie_accept') ?></button>
                <button class="im-btn im-btn-decline" id="cookieDecline" type="button"><?= __t('cookie_decline') ?></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="<?= asset('js/main.js') ?>?v=5" defer></script>
    <?php if (isset($extraJs)): ?><script src="<?= e($extraJs) ?>" defer></script><?php endif; ?>
    <?php if (!empty($extraJs2)): ?><script src="<?= e($extraJs2) ?>" defer></script><?php endif; ?>
    <?php $gaId = getSetting('google_analytics'); if ($gaId): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($gaId) ?>');</script>
    <?php endif; ?>
</body>
</html>