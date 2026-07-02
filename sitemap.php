<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/vertical-lib.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(SITE_URL, '/');
$langs = AVAILABLE_LANGS;
$today = date('Y-m-d');

$urls = [
    ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'weekly', 'hreflang' => true, 'path' => '/tavle/'],
    ['loc' => DEMO_URL, 'priority' => '0.95', 'changefreq' => 'daily', 'path' => '/tavle/site/'],
    ['loc' => $base . '/solutions.php', 'priority' => '0.92', 'changefreq' => 'weekly', 'hreflang' => true, 'path' => '/tavle/solutions.php'],
    ['loc' => DEMO_URL . 'plates/', 'priority' => '0.88', 'changefreq' => 'daily', 'path' => '/tavle/site/plates/'],
    ['loc' => DEMO_URL . 'special/', 'priority' => '0.88', 'changefreq' => 'daily', 'path' => '/tavle/site/special/'],
    ['loc' => 'https://bilohash.com/news/tavle.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
];

foreach ($langs as $lng) {
    $q = $lng === DEFAULT_LANG ? '' : '?lang=' . $lng;
    $urls[] = ['loc' => $base . '/' . $q, 'priority' => '0.9', 'changefreq' => 'weekly'];
    $urls[] = ['loc' => $base . '/solutions.php' . ($q ? $q : ''), 'priority' => '0.88', 'changefreq' => 'weekly'];
}

foreach (array_keys(tv_vertical_defs()) as $slug) {
    $urls[] = [
        'loc' => $base . '/' . $slug . '/',
        'priority' => '0.88',
        'changefreq' => 'monthly',
        'hreflang' => true,
        'path' => '/tavle/' . $slug . '/',
    ];
    foreach ($langs as $lng) {
        if ($lng === DEFAULT_LANG) {
            continue;
        }
        $urls[] = [
            'loc' => $base . '/' . $slug . '/?lang=' . $lng,
            'priority' => '0.86',
            'changefreq' => 'monthly',
        ];
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?= htmlspecialchars($u['loc']) ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq><?= $u['changefreq'] ?? 'monthly' ?></changefreq>
        <priority><?= $u['priority'] ?? '0.5' ?></priority>
        <?php if (!empty($u['hreflang']) && !empty($u['path'])):
            $path = $u['path'];
        ?>
        <xhtml:link rel="alternate" hreflang="x-default" href="https://bilohash.com<?= htmlspecialchars($path) ?>"/>
        <?php foreach ($langs as $lng): ?>
        <xhtml:link rel="alternate" hreflang="<?= htmlspecialchars($lng) ?>" href="https://bilohash.com<?= htmlspecialchars($path) ?><?= $lng !== DEFAULT_LANG ? '?lang=' . $lng : '' ?>"/>
        <?php endforeach; ?>
        <?php endif; ?>
    </url>
<?php endforeach; ?>
</urlset>