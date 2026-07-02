<?php
$ogImage = rtrim(SITE_URL, '/') . '/assets/images/og-landing.svg';
$hub = tv_vertical_hub_label($lang);
$all = tv_verticals_all();
$defs = tv_vertical_defs();
$icon = $vertical['icon'] ?? 'car-front';
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
            <a href="<?= url('solutions.php') ?>"><?= e($hub) ?></a>
            <a href="<?= e($demoUrl) ?>">Live demo</a>
            <a href="https://bilohash.com/news/tavle.html" rel="related">News</a>
        </nav>
    </div>
</header>
<main class="ld-wrap" style="padding:40px 16px 64px;max-width:900px">
    <nav style="font-size:13px;color:#888;margin-bottom:20px">
        <a href="<?= url() ?>">Home</a> → <a href="<?= url('solutions.php') ?>"><?= e($hub) ?></a> → <?= e($v['h1']) ?>
    </nav>
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
        <div class="ld-feature-icon" style="margin:0"><i class="bi bi-<?= e($icon) ?>"></i></div>
        <h1 style="margin:0;font-size:clamp(26px,4vw,36px)"><?= e($v['h1']) ?></h1>
    </div>
    <p style="font-size:18px;color:#444;margin:0 0 16px"><?= e($v['subtitle']) ?></p>
    <p style="line-height:1.7;color:#555;margin:0 0 24px"><?= e($v['intro']) ?></p>
    <p style="font-size:13px;color:#2e9e5b;font-weight:600;margin-bottom:24px"><i class="bi bi-globe-europe-africa"></i> Norway · Europe · Ukraine</p>
    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:40px">
        <a href="<?= e($demoUrl) ?>" class="ld-btn ld-btn-red"><i class="bi bi-play-circle"></i> Live demo</a>
        <a href="<?= url() ?>" class="ld-btn ld-btn-outline">Product page</a>
        <a href="https://bilohash.com/news/tavle.html" class="ld-btn ld-btn-outline" rel="related">Release notes</a>
    </div>
    <h2 style="font-size:22px;margin:0 0 16px">Benefits</h2>
    <div class="ld-features" style="margin-bottom:40px">
        <?php foreach ($v['benefits'] ?? [] as $b): ?>
        <div class="ld-feature">
            <h3><?= e($b['title']) ?></h3>
            <p><?= e($b['text']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <h2 style="font-size:22px;margin:0 0 16px">Bilen CMS features</h2>
    <ul style="line-height:1.8;color:#444;margin:0 0 40px;padding-left:20px">
        <?php foreach ($v['features'] ?? [] as $f): ?>
        <li><?= e($f) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php if (!empty($v['faq'])): ?>
    <h2 style="font-size:22px;margin:0 0 16px">FAQ</h2>
    <div class="ld-faq" style="margin-bottom:40px">
        <?php foreach ($v['faq'] as $item): ?>
        <details class="ld-faq-item">
            <summary><?= e($item['q']) ?></summary>
            <p><?= e($item['a']) ?></p>
        </details>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <h2 style="font-size:20px;margin:0 0 16px">Related solutions</h2>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:32px">
        <?php foreach ($all as $s => $item):
            if ($s === $slug) continue;
            $short = $defs[$s][$lang] ?? $defs[$s]['en'];
        ?>
        <a href="<?= e(tv_vertical_url($s)) ?>" style="padding:8px 14px;border:1px solid #ddd;border-radius:8px;font-size:13px;font-weight:600;color:#333;text-decoration:none"><?= e($short) ?></a>
        <?php endforeach; ?>
    </div>
    <p style="font-weight:600;margin-bottom:12px"><?= e($v['cta'] ?? '') ?></p>
    <a href="https://bilohash.com/" class="ld-btn ld-btn-red" rel="author">Contact BILOHASH</a>
</main>
<footer class="ld-footer">
    <div class="ld-wrap ld-footer-inner">
        <span>&copy; <?= date('Y') ?> <?= e(PRODUCT_NAME) ?> · Drammen, Norway</span>
        <nav>
            <a href="<?= url('solutions.php') ?>"><?= e($hub) ?></a>
            <a href="https://bilohash.com/pizza/" rel="related">Pizza CMS</a>
            <a href="https://bilohash.com/booking/" rel="related">Booking CMS</a>
            <a href="https://bilohash.com/" rel="author">bilohash.com</a>
        </nav>
    </div>
</footer>
</body>
</html>