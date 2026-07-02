<?php
declare(strict_types=1);
require_once __DIR__ . '/init.php';

$page_title = match ($lang) {
    'no' => 'Bil-løsninger Norge | Bilen CMS',
    'uk' => 'Авто-рішення | Bilen CMS',
    'ru' => 'Авто-решения | Bilen CMS',
    default => 'Car Solutions Norway | Bilen CMS',
};
$page_desc = match ($lang) {
    'no' => 'PHP bilmarkedsplass for forhandlere, privat selger, elbiler, nummerskilt og anleggsmaskiner. Flerspråklig SEO for Norge og Europa.',
    'uk' => 'PHP автомайданчик для дилерів, приватних продавців, електромобілів, номерів і спецтехніки. SEO для Норвегії та Європи.',
    'ru' => 'PHP автоплощадка для дилеров, частных продавцов, электромобилей, номеров и спецтехники. SEO для Норвегии и Европы.',
    default => 'PHP car marketplace for dealerships, private sellers, EVs, license plates and special equipment. Multilingual SEO for Norway & Europe.',
};
$canonical = $site_url . '/solutions.php' . ($lang !== DEFAULT_LANG ? '?lang=' . urlencode($lang) : '');
$all = tv_verticals_all();
$defs = tv_vertical_defs();
$hub = tv_vertical_hub_label($lang);
$extraSchema = tv_render_schema_graph([
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $page_title,
        'description' => $page_desc,
        'url' => tv_absolute_url($canonical),
        'inLanguage' => ['no', 'en', 'uk', 'ru'],
        'isPartOf' => ['@type' => 'WebSite', 'name' => PRODUCT_NAME, 'url' => $site_url . '/'],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $hub,
        'itemListElement' => array_values(array_map(static function ($slug, $i) use ($defs, $lang) {
            return [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $defs[$slug][$lang] ?? $defs[$slug]['en'],
                'url' => tv_absolute_url(tv_vertical_url($slug)),
            ];
        }, array_keys($all), array_keys($all))),
    ],
]);
require __DIR__ . '/includes/solutions-template.php';