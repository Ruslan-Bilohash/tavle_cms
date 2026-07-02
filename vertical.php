<?php
declare(strict_types=1);
require_once __DIR__ . '/init.php';

$slug = trim($_GET['slug'] ?? '');
$vertical = tv_vertical_by_slug($slug);
if (!$vertical) {
    header('Location: ' . url('solutions.php'), true, 302);
    exit;
}

$v = tv_vertical_lang($vertical, $lang);
$page_title = $v['title'] ?? PRODUCT_NAME;
$page_desc = $v['description'] ?? '';
$canonical = tv_vertical_canonical($slug, $lang);
$demoUrl = tv_demo_url($vertical['demo_listing'] ?? 'car');
$extraSchema = tv_render_schema_graph([
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $page_title,
        'description' => $page_desc,
        'url' => tv_absolute_url($canonical),
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $v['h1'] ?? $slug,
        'description' => $page_desc,
        'provider' => ['@type' => 'Organization', 'name' => 'BILOHASH', 'url' => 'https://bilohash.com/'],
        'areaServed' => ['Norway', 'Europe'],
        'url' => tv_absolute_url($canonical),
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => PRODUCT_NAME,
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'PHP 8+',
        'url' => DEMO_URL,
        'description' => $page_desc,
    ],
    !empty($v['faq']) ? [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(static fn($item) => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ], $v['faq']),
    ] : null,
]);

require __DIR__ . '/includes/vertical-template.php';