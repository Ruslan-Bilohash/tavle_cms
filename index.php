<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
$lang = getLang();
$canonical = rtrim(SITE_URL, '/') . '/';
if ($lang !== DEFAULT_LANG) {
    $canonical .= '?lang=' . $lang;
}
$ogImage = rtrim(SITE_URL, '/') . '/assets/images/og-landing.svg';
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(__l('meta_title')) ?></title>
    <meta name="description" content="<?= e(__l('meta_desc')) ?>">
    <meta name="keywords" content="<?= e(__l('meta_keywords')) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <?= hreflang() ?>
    <link rel="alternate" hreflang="x-default" href="<?= e(rtrim(SITE_URL, '/') . '/') ?>">
    <?php
    require_once dirname(__DIR__) . '/includes/bh-open-graph.php';
    $ogLocale = $lang === 'uk' ? 'uk_UA' : ($lang === 'ru' ? 'ru_RU' : ($lang === 'no' ? 'nb_NO' : 'en_US'));
    $ogLocaleAlts = array_values(array_filter(['en_US', 'uk_UA', 'ru_RU', 'nb_NO'], fn($l) => $l !== $ogLocale));
    bh_render_open_graph([
        'title' => __l('meta_title'),
        'description' => __l('meta_desc'),
        'url' => $canonical,
        'image' => $ogImage,
        'site_name' => PRODUCT_NAME,
        'type' => 'website',
        'locale' => $ogLocale,
        'locale_alternates' => $ogLocaleAlts,
        'image_alt' => __l('meta_title'),
        'image_type' => 'image/svg+xml',
    ]);
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/landing.css') ?>" rel="stylesheet">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "https://bilohash.com/#organization",
                "name": "BILOHASH",
                "url": "https://bilohash.com/",
                "logo": "https://bilohash.com/ruslan_cub.jpg"
            },
            {
                "@type": "SoftwareApplication",
                "name": "<?= PRODUCT_NAME ?>",
                "applicationCategory": "BusinessApplication",
                "operatingSystem": "PHP 8+",
                "description": <?= json_encode(__l('meta_desc'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
                "url": "<?= DEMO_URL ?>",
                "image": "<?= e($ogImage) ?>",
                "inLanguage": ["en", "uk", "ru", "no"],
                "publisher": {"@id": "https://bilohash.com/#organization"},
                "offers": {"@type": "Offer", "price": "0", "priceCurrency": "USD"}
            },
            {
                "@type": "WebSite",
                "@id": "<?= rtrim(SITE_URL, '/') ?>/#website",
                "name": "<?= PRODUCT_NAME ?>",
                "url": "<?= rtrim(SITE_URL, '/') ?>/",
                "inLanguage": ["en", "uk", "ru", "no"],
                "publisher": {"@id": "https://bilohash.com/#organization"}
            },
            {
                "@type": "FAQPage",
                "mainEntity": [
                    <?php for ($fi = 1; $fi <= 6; $fi++): ?>
                    {
                        "@type": "Question",
                        "name": <?= json_encode(__l("faq{$fi}_q"), JSON_UNESCAPED_UNICODE) ?>,
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": <?= json_encode(__l("faq{$fi}_a"), JSON_UNESCAPED_UNICODE) ?>
                        }
                    }<?= $fi < 6 ? ',' : '' ?>
                    <?php endfor; ?>
                ]
            }
        ]
    }
    </script>
</head>
<body>
<header class="ld-header">
    <div class="ld-wrap ld-header-inner">
        <a href="<?= url() ?>" class="ld-logo"><span class="ld-logo-icon"><i class="bi bi-code-slash"></i></span> <?= e(PRODUCT_NAME) ?></a>
        <nav class="ld-nav" id="ldNav">
            <a href="#features"><?= __l('nav_features') ?></a>
            <a href="#showcase"><?= __l('nav_showcase') ?></a>
            <a href="#screenshots"><?= __l('nav_screens') ?></a>
            <a href="#demo"><?= __l('nav_demo') ?></a>
            <a href="#faq"><?= __l('nav_faq') ?></a>
            <a href="#tech"><?= __l('nav_tech') ?></a>
        </nav>
        <div class="ld-actions">
            <div class="ld-lang">
                <button type="button" id="ldLangBtn" aria-haspopup="true"><?= strtoupper($lang) ?> ▾</button>
                <div class="ld-lang-menu" id="ldLangMenu" hidden>
                    <?php foreach (AVAILABLE_LANGS as $l): ?>
                    <a href="<?= langLink($l) ?>"><?= strtoupper($l) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="<?= DEMO_URL ?>" class="ld-btn ld-btn-red"><?= __l('hero_cta_demo') ?></a>
            <button class="ld-burger" id="ldBurger" type="button" aria-label="Menu"><i class="bi bi-list"></i></button>
        </div>
    </div>
</header>

<section class="ld-hero">
    <div class="ld-wrap ld-hero-grid">
        <div class="ld-hero-copy">
            <span class="ld-badge"><?= __l('hero_badge') ?></span>
            <h1><?= __l('hero_title') ?></h1>
            <p><?= __l('hero_sub') ?></p>
            <div class="ld-hero-btns">
                <a href="<?= DEMO_URL ?>" class="ld-btn ld-btn-red ld-btn-lg"><?= __l('hero_cta_demo') ?> <i class="bi bi-box-arrow-up-right"></i></a>
                <a href="<?= ADMIN_DEMO_URL ?>" class="ld-btn ld-btn-outline ld-btn-lg"><?= __l('hero_cta_admin') ?></a>
            </div>
            <div class="ld-stats">
                <div><strong>4</strong><span><?= __l('stat_langs') ?></span></div>
                <div><strong>18+</strong><span><?= __l('stat_cars') ?></span></div>
                <div><strong>20+</strong><span><?= __l('stat_brands') ?></span></div>
                <div><strong>SEO</strong><span><?= __l('stat_seo') ?></span></div>
            </div>
        </div>
        <div class="ld-preview" aria-hidden="true">
            <div class="ld-preview-chrome">
                <span></span><span></span><span></span>
                <div class="ld-preview-url">bilohash.com/tavle/site</div>
            </div>
            <div class="ld-preview-ui">
                <div class="ld-p-tabs">
                    <span class="on"><?= __l('preview_buy') ?></span><span><?= __l('preview_leasing') ?></span>
                </div>
                <div class="ld-p-tabs sm">
                    <span class="on"><?= __l('preview_stock') ?></span><span><?= __l('preview_route') ?></span><span><?= __l('preview_order') ?></span>
                </div>
                <div class="ld-p-search"></div>
                <div class="ld-p-brands">
                    <span></span><span></span><span></span><span></span>
                </div>
                <div class="ld-p-card">
                    <div class="ld-p-img"></div>
                    <div class="ld-p-info">
                        <div class="ld-p-line w80"></div>
                        <div class="ld-p-line w60"></div>
                        <div class="ld-p-price"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="ld-section" id="features">
    <div class="ld-wrap">
        <h2><?= __l('features_title') ?></h2>
        <p class="ld-sub"><?= __l('features_sub') ?></p>
        <div class="ld-features">
            <?php
            $icons = ['palette', 'translate', 'database', 'shield-check', 'search', 'phone'];
            for ($i = 1; $i <= 6; $i++):
            ?>
            <article class="ld-feature">
                <div class="ld-feature-icon"><i class="bi bi-<?= $icons[$i - 1] ?>"></i></div>
                <h3><?= __l("f{$i}_title") ?></h3>
                <p><?= __l("f{$i}_desc") ?></p>
            </article>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="ld-section ld-section-alt" id="showcase">
    <div class="ld-wrap">
        <h2><?= __l('showcase_title') ?></h2>
        <p class="ld-sub"><?= __l('showcase_sub') ?></p>
        <div class="ld-showcase-grid">
            <?php
            $screens = ['screen-catalog', 'screen-catalog', 'screen-filters', 'screen-car', 'screen-admin', 'screen-mobile'];
            for ($i = 1; $i <= 6; $i++):
            ?>
            <article class="ld-showcase-card">
                <figure class="ld-showcase-figure">
                    <img src="<?= url('assets/images/screens/' . $screens[$i - 1] . '.svg') ?>" alt="<?= e(__l("showcase_{$i}_title")) ?>" width="800" height="500" loading="lazy">
                </figure>
                <div class="ld-showcase-body">
                    <h3><?= __l("showcase_{$i}_title") ?></h3>
                    <p class="ld-showcase-short"><?= __l("showcase_{$i}_short") ?></p>
                    <p class="ld-showcase-long"><?= __l("showcase_{$i}_long") ?></p>
                </div>
            </article>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="ld-section" id="seo">
    <div class="ld-wrap ld-seo-wrap">
        <h2><?= __l('seo_title') ?></h2>
        <p class="ld-sub"><?= __l('seo_sub') ?></p>
        <div class="ld-seo-copy">
            <p><?= __l('seo_p1') ?></p>
            <p><?= __l('seo_p2') ?></p>
            <p><?= __l('seo_p3') ?></p>
        </div>
    </div>
</section>

<section class="ld-section ld-section-alt" id="screenshots">
    <div class="ld-wrap">
        <h2><?= __l('screens_title') ?></h2>
        <p class="ld-sub"><?= __l('screens_sub') ?></p>
        <div class="ld-screens">
            <figure class="ld-screen">
                <div class="ld-screen-frame">
                    <iframe src="<?= DEMO_URL ?>?lang=<?= e($lang) ?>" title="<?= __l('screens_iframe') ?>" loading="lazy"></iframe>
                </div>
                <figcaption><?= __l('screens_caption_list') ?></figcaption>
            </figure>
            <div class="ld-screen-list">
                <div class="ld-screen-item"><i class="bi bi-check-circle-fill"></i> <?= __l('screens_item1') ?></div>
                <div class="ld-screen-item"><i class="bi bi-check-circle-fill"></i> <?= __l('screens_item2') ?></div>
                <div class="ld-screen-item"><i class="bi bi-check-circle-fill"></i> <?= __l('screens_item3') ?></div>
                <div class="ld-screen-item"><i class="bi bi-check-circle-fill"></i> <?= __l('screens_item4') ?></div>
                <a href="<?= DEMO_URL ?>" class="ld-btn ld-btn-red ld-btn-lg"><?= __l('hero_cta_demo') ?></a>
            </div>
        </div>
    </div>
</section>

<section class="ld-section ld-section-dark" id="demo">
    <div class="ld-wrap">
        <h2><?= __l('demo_title') ?></h2>
        <p class="ld-sub"><?= __l('demo_sub') ?></p>
        <div class="ld-demo-box">
            <div class="ld-demo-links">
                <a href="<?= DEMO_URL ?>" class="ld-btn ld-btn-red ld-btn-lg"><?= __l('hero_cta_demo') ?></a>
                <a href="<?= ADMIN_DEMO_URL ?>" class="ld-btn ld-btn-outline ld-btn-lg ld-btn-light"><?= __l('hero_cta_admin') ?></a>
            </div>
            <div class="ld-demo-creds">
                <div><span><?= __l('demo_login') ?></span><code>admin</code></div>
                <div><span><?= __l('demo_pass') ?></span><code>admin123</code></div>
            </div>
        </div>
    </div>
</section>

<section class="ld-section" id="faq">
    <div class="ld-wrap ld-faq-wrap">
        <h2><?= __l('faq_title') ?></h2>
        <div class="ld-faq">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <details class="ld-faq-item">
                <summary><?= __l("faq{$i}_q") ?></summary>
                <p><?= __l("faq{$i}_a") ?></p>
            </details>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="ld-section ld-section-alt" id="tech">
    <div class="ld-wrap">
        <h2><?= __l('tech_title') ?></h2>
        <p class="ld-tech"><?= __l('tech_list') ?></p>
        <div class="ld-tech-tags">
            <?php foreach (['PHP 8+', 'SQLite', 'SEO', 'Schema.org', 'hreflang', 'CSRF', '.htaccess'] as $tag): ?>
            <span><?= $tag ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="ld-cta">
    <div class="ld-wrap">
        <h2><?= __l('cta_title') ?></h2>
        <p><?= __l('cta_sub') ?></p>
        <a href="<?= DEMO_URL ?>" class="ld-btn ld-btn-red ld-btn-lg"><?= __l('hero_cta_demo') ?></a>
    </div>
</section>

<footer class="ld-footer ld-footer-advanced">
    <div class="ld-wrap ld-footer-grid">
        <div>
            <strong><?= e(PRODUCT_NAME) ?></strong>
            <p style="margin:8px 0 0;font-size:14px;color:#888;line-height:1.5"><?= __l('meta_desc') ?></p>
        </div>
        <div>
            <strong>Solutions</strong>
            <nav class="ld-footer-links">
                <a href="<?= url('solutions.php') ?>">Norway &amp; Europe</a>
                <a href="<?= url('car-dealership-no/') ?>">Car dealership</a>
                <a href="<?= DEMO_URL ?>plates/">License plates</a>
                <a href="<?= DEMO_URL ?>special/">Special equipment</a>
            </nav>
        </div>
        <div>
            <strong>BILOHASH</strong>
            <nav class="ld-footer-links">
                <a href="https://bilohash.com/" rel="author">bilohash.com</a>
                <a href="https://bilohash.com/news/tavle.html" rel="related">Release</a>
                <a href="https://bilohash.com/pizza/" rel="related">Pizza CMS</a>
                <a href="https://bilohash.com/booking/" rel="related">Booking CMS</a>
            </nav>
        </div>
    </div>
    <div class="ld-wrap ld-footer-inner">
        <span>&copy; <?= date('Y') ?> <?= __l('footer_copy') ?> · Drammen, Norway</span>
        <nav>
            <a href="<?= DEMO_URL ?>"><?= __l('nav_demo') ?></a>
            <a href="<?= ADMIN_DEMO_URL ?>"><?= __l('hero_cta_admin') ?></a>
            <a href="<?= url('solutions.php') ?>">Solutions</a>
            <a href="<?= rtrim(SITE_URL, '/') ?>/sitemap.xml">Sitemap</a>
        </nav>
    </div>
</footer>
<script src="<?= url('assets/js/landing.js') ?>"></script>
</body>
</html>