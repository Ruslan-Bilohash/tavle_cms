<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$cars = Database::fetchAll('SELECT id, slug, updated_at FROM cars WHERE is_active = 1 ORDER BY updated_at DESC');
$news = Database::fetchAll('SELECT slug, updated_at FROM news WHERE is_published = 1 ORDER BY updated_at DESC');
$brands = Database::fetchAll('SELECT slug FROM brands WHERE is_active = 1');
$models = Database::fetchAll(
    'SELECT m.slug AS model_slug, b.slug AS brand_slug
     FROM models m JOIN brands b ON m.brand_id = b.id
     WHERE m.is_active = 1 AND b.is_active = 1'
);

$base = rtrim(SITE_URL, '/');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

sitemapEntry($base . '/', date('Y-m-d'), 'daily', '1.0', '/');
sitemapEntry($base . '/llms.txt', date('Y-m-d'), 'monthly', '0.5', '/llms.txt');
sitemapEntry($base . '/plates/', date('Y-m-d'), 'daily', '0.85', '/plates/');
sitemapEntry($base . '/special/', date('Y-m-d'), 'daily', '0.85', '/special/');
sitemapEntry($base . '/news.php', date('Y-m-d'), 'daily', '0.8', '/news.php');

foreach ($cars as $car) {
    $path = '/car.php?id=' . $car['id'];
    sitemapEntry($base . $path, date('Y-m-d', strtotime($car['updated_at'])), 'weekly', '0.8', $path);
}

foreach ($news as $item) {
    $path = '/news.php?slug=' . rawurlencode($item['slug']);
    sitemapEntry($base . $path, date('Y-m-d', strtotime($item['updated_at'])), 'monthly', '0.6', $path);
}

foreach ($brands as $brand) {
    $path = '/brand/' . $brand['slug'];
    sitemapEntry($base . $path, date('Y-m-d'), 'weekly', '0.7', $path);
}

foreach ($models as $m) {
    $path = '/brand/' . $m['brand_slug'] . '/' . $m['model_slug'];
    sitemapEntry($base . $path, date('Y-m-d'), 'weekly', '0.75', $path);
}

echo '</urlset>';

function sitemapEntry(string $loc, string $lastmod, string $changefreq, string $priority, string $path): void
{
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($loc) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>{$changefreq}</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    foreach (AVAILABLE_LANGS as $lang) {
        $href = rtrim(SITE_URL, '/') . $path;
        if ($lang !== DEFAULT_LANG) {
            $href .= (str_contains($href, '?') ? '&' : '?') . 'lang=' . $lang;
        }
        echo '    <xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . htmlspecialchars($href) . "\"/>\n";
    }
    echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars(rtrim(SITE_URL, '/') . $path) . "\"/>\n";
    echo "  </url>\n";
}