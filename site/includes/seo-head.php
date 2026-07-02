<?php
/** @var string $pageTitle */
/** @var string $pageDescription */
/** @var string $pageImage */
/** @var string $extraSchema */
$lang = getCurrentLang();
$siteName = getSetting('site_name', SITE_NAME);
$keywords = getSeoKeywords();
$ogType = $ogType ?? 'website';
$robots = $robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
$locales = [
    'uk' => 'uk_UA', 'en' => 'en_US', 'ru' => 'ru_RU', 'no' => 'nb_NO',
];
$currentLocale = $locales[$lang] ?? 'en_US';
$llmsUrl = llmsTxtUrl();
$rootLlmsUrl = rootLlmsTxtUrl();
$cssUrl = asset('css/style.css') . '?v=9';
$iconsJs = asset('js/icons-loader.js') . '?v=1';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<?php if ($keywords): ?>
<meta name="keywords" content="<?= e($keywords) ?>">
<?php endif; ?>
<meta name="robots" content="<?= e($robots) ?>">
<meta name="author" content="<?= e($siteName) ?>">
<meta name="language" content="<?= e($lang) ?>">
<link rel="canonical" href="<?= e(currentUrl()) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e(langCanonicalUrl(DEFAULT_LANG)) ?>">
<?= renderHreflangTags() ?>
<link rel="alternate" type="text/plain" href="<?= e($rootLlmsUrl) ?>" title="LLM context">
<link rel="alternate" type="text/plain" href="<?= e($llmsUrl) ?>" title="Bilen Auto LLM context">
<?php
require_once dirname(BILEN_ROOT, 2) . '/includes/bh-open-graph.php';
$ogAlts = [];
foreach ($locales as $l => $loc) {
    if ($l !== $lang) {
        $ogAlts[] = $loc;
    }
}
bh_render_open_graph([
    'title' => $pageTitle,
    'description' => $pageDescription,
    'url' => currentUrl(),
    'image' => $pageImage,
    'site_name' => $siteName,
    'type' => $ogType,
    'locale' => $currentLocale,
    'locale_alternates' => $ogAlts,
    'image_alt' => $pageTitle,
]);
?>
<meta name="theme-color" content="#2e9e5b">
<meta name="geo.region" content="NO-06">
<meta name="geo.placename" content="Drammen, Norway">
<meta name="ICBM" content="59.7439, 10.2045">
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preconnect" href="https://images.unsplash.com" crossorigin>
<link rel="dns-prefetch" href="https://images.unsplash.com">
<link rel="preload" href="<?= e($cssUrl) ?>" as="style">
<link rel="stylesheet" href="<?= e($cssUrl) ?>">
<?php if (!empty($extraCss)): ?>
<link rel="stylesheet" href="<?= e($extraCss) ?>">
<?php endif; ?>
<link rel="preload" href="<?= e($iconsJs) ?>" as="script">
<script src="<?= e($iconsJs) ?>" defer></script>
<?= $extraSchema ?? '' ?>