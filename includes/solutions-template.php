<?php
$ogImage = rtrim(SITE_URL, '/') . '/assets/images/og-landing.svg';
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($page_desc) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e(tv_absolute_url($canonical)) ?>">
    <?= hreflang() ?>
    <?php
    require_once dirname(__DIR__, 2) . '/includes/bh-open-graph.php';
    $tvOgLocale = $lang === 'uk' ? 'uk_UA' : ($lang === 'ru' ? 'ru_RU' : ($lang === 'no' ? 'nb_NO' : 'en_US'));
    bh_render_open_graph([
        'title' => $page_title,
        'description' => $page_desc,
        'url' => tv_absolute_url($canonical),
        'image' => $ogImage,
        'site_name' => PRODUCT_NAME,
        'type' => 'website',
        'locale' => $tvOgLocale,
        'locale_alternates' => array_values(array_filter(['en_US', 'uk_UA', 'ru_RU', 'nb_NO'], fn($l) => $l !== $tvOgLocale)),
        'image_alt' => $page_title,
        'image_type' => 'image/svg+xml',
    ]);
    ?>
    <meta name="geo.region" content="NO-06">
    <meta name="geo.placename" content="Drammen, Norway">
    <link href="<?= url('assets/css/landing.css') ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?= $extraSchema ?? '' ?>
</head>
<body class="ld-page">
<header class="ld-header">
    <div class="ld-wrap ld-header-inner">
        <a href="<?= url() ?>" class="ld-logo"><i class="bi bi-car-front-fill"></i> <?= e(PRODUCT_NAME) ?></a>
        <nav>
            <a href="<?= url() ?>"><?= __l('nav_home') ?? 'Home' ?></a>
            <a href="<?= DEMO_URL ?>"><?= __l('nav_demo') ?? 'Demo' ?></a>
            <a href="https://bilohash.com/news/tavle.html" rel="related">News</a>
            <a href="https://bilohash.com/" rel="author">BILOHASH</a>
        </nav>
    </div>
</header>
<main class="ld-wrap" style="padding:48px 16px 64px">
    <h1 style="font-size:clamp(28px,4vw,40px);margin:0 0 12px"><?= e($hub) ?> — Norway &amp; Europe</h1>
    <p style="color:#666;max-width:720px;line-height:1.6;margin:0 0 32px"><?= e($page_desc) ?></p>
    <div class="ld-features" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
        <?php foreach ($all as $slug => $item):
            $label = $defs[$slug][$lang] ?? $defs[$slug]['en'];
            $lv = tv_vertical_lang($item, $lang);
        ?>
        <a href="<?= e(tv_vertical_url($slug)) ?>" class="ld-feature" style="text-decoration:none;color:inherit">
            <div class="ld-feature-icon"><i class="bi bi-<?= e($item['icon'] ?? 'car-front') ?>"></i></div>
            <h3><?= e($label) ?></h3>
            <p><?= e(mb_substr($lv['subtitle'] ?? '', 0, 100)) ?></p>
        </a>
        <?php endforeach; ?>
    </div>
    <p style="margin-top:32px"><a href="<?= DEMO_URL ?>" class="ld-btn ld-btn-red"><?= __l('hero_cta_demo') ?></a></p>
</main>
<footer class="ld-footer">
    <div class="ld-wrap ld-footer-inner">
        <span>&copy; <?= date('Y') ?> <?= e(PRODUCT_NAME) ?></span>
        <nav>
            <a href="<?= DEMO_URL ?>"><?= __l('nav_demo') ?></a>
            <a href="https://bilohash.com/pizza/" rel="related">Pizza CMS</a>
            <a href="https://bilohash.com/booking/" rel="related">Booking CMS</a>
            <a href="https://bilohash.com/" rel="author">bilohash.com</a>
        </nav>
    </div>
</footer>
</body>
</html>