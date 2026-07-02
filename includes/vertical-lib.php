<?php
declare(strict_types=1);

function tv_absolute_url(string $path): string
{
    if (str_starts_with($path, 'http')) {
        return $path;
    }
    return rtrim(SITE_ORIGIN, '/') . $path;
}

function tv_vertical_defs(): array
{
    static $defs = null;
    if ($defs === null) {
        $defs = require LANDING_ROOT . '/data/vertical-defs.php';
    }
    return $defs;
}

function tv_vertical_hub_label(string $lang): string
{
    return match ($lang) {
        'no' => 'Bil-løsninger',
        'uk' => 'Авто-рішення',
        'ru' => 'Авто-решения',
        default => 'Car solutions',
    };
}

function tv_demo_url(string $listingType): string
{
    return match ($listingType) {
        'plate' => DEMO_URL . 'plates/',
        'special' => DEMO_URL . 'special/',
        default => DEMO_URL,
    };
}

function tv_verticals_build(): array
{
    $defs = tv_vertical_defs();
    $tpl = [
        'no' => [
            'title' => '%s — Bilen CMS Norge | PHP bilmarkedsplass',
            'description' => 'Bilen CMS for %s i Norge og Europa. PHP bilannonser, sticky filtre, Schema.org Car SEO, SQLite admin og 4 språk. Live demo.',
            'subtitle' => 'Profesjonell bilannonse-plattform for %s',
            'intro' => 'Bilen CMS er en modulær PHP-markedsplass for %s. 30+ demoannonser, nummerplater, spesialutstyr, VIN-skjerming, telefonavsløring og fullt adminpanel — klar for norske og europeiske markeder.',
            'cta' => 'Klar for en bilplattform for %s? Kontakt oss i dag.',
            'h1' => 'Bilen CMS — %s',
        ],
        'en' => [
            'title' => '%s — Bilen CMS Norway | PHP car marketplace',
            'description' => 'Bilen CMS for %s in Norway & Europe. PHP car listings, sticky filters, Schema.org Car SEO, SQLite admin and 4 languages. Live demo.',
            'subtitle' => 'Professional car listing platform for %s',
            'intro' => 'Bilen CMS is a modular PHP marketplace for %s. 30+ demo listings, license plates, special equipment, VIN masking, phone reveal and full admin — ready for Norwegian and European markets.',
            'cta' => 'Ready for a car platform for %s? Contact us today.',
            'h1' => 'Bilen CMS — %s',
        ],
        'uk' => [
            'title' => '%s — Bilen CMS Норвегія | PHP автомайданчик',
            'description' => 'Bilen CMS для %s у Норвегії та Європі. PHP-оголошення, липкі фільтри, Schema.org Car SEO, SQLite-адмін і 4 мови. Live demo.',
            'subtitle' => 'Професійний автомайданчик для %s',
            'intro' => 'Bilen CMS — модульна PHP-платформа для %s. 30+ демо-оголошень, номери, спецтехніка, прихований VIN, показ телефону та повна адмін-панель для Норвегії та Європи.',
            'cta' => 'Потрібен автомайданчик для %s? Зв\'яжіться з нами.',
            'h1' => 'Bilen CMS — %s',
        ],
        'ru' => [
            'title' => '%s — Bilen CMS Норвегия | PHP автоплощадка',
            'description' => 'Bilen CMS для %s в Норвегии и Европе. PHP-объявления, липкие фильтры, Schema.org Car SEO, SQLite-админ и 4 языка. Live demo.',
            'subtitle' => 'Профессиональная автоплощадка для %s',
            'intro' => 'Bilen CMS — модульная PHP-платформа для %s. 30+ демо-объявлений, номера, спецтехника, скрытый VIN, показ телефона и полная админ-панель для Норвегии и Европы.',
            'cta' => 'Нужна автоплощадка для %s? Свяжитесь с нами.',
            'h1' => 'Bilen CMS — %s',
        ],
    ];
    $benefits = [
        'no' => [
            ['title' => 'Høy konvertering', 'text' => 'Mobilvennlig katalog med sticky filtre, VIN/telefon-avsløring og rask lasting.'],
            ['title' => 'Norge & Europa SEO', 'text' => 'hreflang, Schema.org Car, breadcrumbs og dynamisk sitemap for NO/EU-markeder.'],
            ['title' => 'Full admin', 'text' => 'SQLite-database, merker, modeller, forhandlere, nyheter og flerspråklige beskrivelser.'],
        ],
        'en' => [
            ['title' => 'High conversion', 'text' => 'Mobile-first catalog with sticky filters, VIN/phone reveal and fast loading.'],
            ['title' => 'Norway & Europe SEO', 'text' => 'hreflang, Schema.org Car, breadcrumbs and dynamic sitemap for NO/EU markets.'],
            ['title' => 'Full admin', 'text' => 'SQLite database, brands, models, dealers, news and multilingual descriptions.'],
        ],
        'uk' => [
            ['title' => 'Висока конверсія', 'text' => 'Мобільний каталог з липкими фільтрами, показом VIN/телефону та швидким завантаженням.'],
            ['title' => 'SEO Норвегія & Європа', 'text' => 'hreflang, Schema.org Car, breadcrumbs і динамічний sitemap для ринків NO/EU.'],
            ['title' => 'Повна адмінка', 'text' => 'SQLite, бренди, моделі, дилери, новини та багатомовні описи.'],
        ],
        'ru' => [
            ['title' => 'Высокая конверсия', 'text' => 'Мобильный каталог с липкими фильтрами, показом VIN/телефона и быстрой загрузкой.'],
            ['title' => 'SEO Норвегия & Европа', 'text' => 'hreflang, Schema.org Car, breadcrumbs и динамический sitemap для рынков NO/EU.'],
            ['title' => 'Полная админка', 'text' => 'SQLite, бренды, модели, дилеры, новости и многоязычные описания.'],
        ],
    ];
    $features = [
        'no' => ['Biler, nummerskilt og anleggsmaskiner', 'Sticky toolbar og avanserte filtre', 'Schema.org Car + BreadcrumbList', '4 språk: NO, EN, UK, RU', 'SQLite — ingen MySQL', 'CSRF, .htaccess pretty URLs'],
        'en' => ['Cars, license plates and special equipment', 'Sticky toolbar and advanced filters', 'Schema.org Car + BreadcrumbList', '4 languages: NO, EN, UK, RU', 'SQLite — no MySQL required', 'CSRF, .htaccess pretty URLs'],
        'uk' => ['Авто, номери та спецтехніка', 'Липка панель і розширені фільтри', 'Schema.org Car + BreadcrumbList', '4 мови: NO, EN, UK, RU', 'SQLite — без MySQL', 'CSRF, pretty URLs'],
        'ru' => ['Авто, номера и спецтехника', 'Липкая панель и расширенные фильтры', 'Schema.org Car + BreadcrumbList', '4 языка: NO, EN, UK, RU', 'SQLite — без MySQL', 'CSRF, pretty URLs'],
    ];
    $faq = [
        'no' => [
            ['q' => 'Passer Bilen CMS for norske bilforhandlere?', 'a' => 'Ja — plattformen er bygget med norske og europeiske SEO-standarder, flerspråklig støtte og demo for Oslo, Bergen og Drammen.'],
            ['q' => 'Kan jeg selge nummerplater og spesialutstyr?', 'a' => 'Ja — separate kataloger for biler, nummerskilt og anleggsmaskiner med egne filtre og kortdesign.'],
        ],
        'en' => [
            ['q' => 'Is Bilen CMS suitable for Norwegian dealerships?', 'a' => 'Yes — built with Norwegian and European SEO standards, multilingual support and demos for Oslo, Bergen and Drammen.'],
            ['q' => 'Can I sell license plates and special equipment?', 'a' => 'Yes — separate catalogs for cars, plates and heavy equipment with dedicated filters and card layouts.'],
        ],
        'uk' => [
            ['q' => 'Чи підходить Bilen CMS для автосалонів Норвегії?', 'a' => 'Так — платформа з норвезькими та європейськими SEO-стандартами, багатомовністю та демо для Oslo, Bergen і Drammen.'],
            ['q' => 'Чи можна продавати номери та спецтехніку?', 'a' => 'Так — окремі каталоги для авто, номерів і спецтехніки з власними фільтрами та картками.'],
        ],
        'ru' => [
            ['q' => 'Подходит ли Bilen CMS для автосалонов Норвегии?', 'a' => 'Да — платформа с норвежскими и европейскими SEO-стандартами, мультиязычностью и демо для Oslo, Bergen и Drammen.'],
            ['q' => 'Можно ли продавать номера и спецтехнику?', 'a' => 'Да — отдельные каталоги для авто, номеров и спецтехники с собственными фильтрами и карточками.'],
        ],
    ];

    $out = [];
    foreach ($defs as $slug => $meta) {
        $out[$slug] = [];
        foreach (['no', 'en', 'uk', 'ru'] as $l) {
            $name = $meta[$l] ?? $meta['en'];
            $t = $tpl[$l];
            $out[$slug][$l] = [
                'title' => sprintf($t['title'], $name),
                'description' => sprintf($t['description'], $name),
                'subtitle' => sprintf($t['subtitle'], $name),
                'intro' => sprintf($t['intro'], $name),
                'cta' => sprintf($t['cta'], $name),
                'h1' => sprintf($t['h1'], $name),
                'benefits' => $benefits[$l],
                'features' => $features[$l],
                'faq' => $faq[$l],
            ];
        }
        $out[$slug]['icon'] = $meta['icon'];
        $out[$slug]['demo_listing'] = $meta['demo_listing'];
    }
    return $out;
}

function tv_verticals_all(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = tv_verticals_build();
    }
    return $cache;
}

function tv_vertical_by_slug(string $slug): ?array
{
    $all = tv_verticals_all();
    return $all[$slug] ?? null;
}

function tv_vertical_lang(array $vertical, string $lang): array
{
    return $vertical[$lang] ?? $vertical['en'] ?? [];
}

function tv_vertical_url(string $slug, ?string $lang = null): string
{
    $path = url($slug . '/');
    if ($lang && $lang !== DEFAULT_LANG) {
        $path .= (str_contains($path, '?') ? '&' : '?') . 'lang=' . urlencode($lang);
    }
    return $path;
}

function tv_vertical_canonical(string $slug, ?string $lang = null): string
{
    $l = $lang ?? DEFAULT_LANG;
    return tv_vertical_url($slug, $l === DEFAULT_LANG ? null : $l);
}

function tv_seo_json(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
}

function tv_render_schema_graph(array $graphs): string
{
    $out = '';
    foreach ($graphs as $g) {
        if ($g) {
            $out .= '<script type="application/ld+json">' . tv_seo_json($g) . '</script>' . "\n";
        }
    }
    return $out;
}