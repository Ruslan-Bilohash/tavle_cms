<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$slug = $_GET['slug'] ?? null;

if ($slug) {
    $article = Database::fetchOne('SELECT * FROM news WHERE slug = ? AND is_published = 1', 's', [sanitize($slug)]);
    if (!$article) {
        redirect(url('news.php'));
    }
    Database::execute('UPDATE news SET views = views + 1 WHERE id = ?', 'i', [$article['id']]);
    $pageTitle = getNewsTitle($article) . ' — ' . getSetting('site_name', SITE_NAME);
    $pageDescription = getNewsExcerpt($article) ?: mb_substr(strip_tags($article['content_uk'] ?? ''), 0, 160);
    $lang = getCurrentLang();
    $contentKey = 'content_' . $lang;
    $ogType = 'article';
    $extraSchema = renderNewsArticleSchema($article) . renderBreadcrumbSchema([
        ['name' => __t('home'), 'url' => absoluteUrl(url())],
        ['name' => __t('news'), 'url' => absoluteUrl(url('news.php'))],
        ['name' => getNewsTitle($article), 'url' => currentUrl()],
    ]);

    require __DIR__ . '/includes/header.php';
    ?>
    <div class="container-im im-breadcrumb">
        <a href="<?= url() ?>"><?= __t('home') ?></a> /
        <a href="<?= url('news.php') ?>"><?= __t('news') ?></a> /
        <?= e(getNewsTitle($article)) ?>
    </div>
    <article class="container-im im-article">
        <h1><?= e(getNewsTitle($article)) ?></h1>
        <p class="im-news-date"><?= date('d.m.Y', strtotime($article['created_at'])) ?></p>
        <div class="im-article-content"><?= $article[$contentKey] ?? $article['content_uk'] ?></div>
    </article>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$news = Database::fetchAll('SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC');
$pageTitle = __t('news') . ' — ' . getSetting('site_name', SITE_NAME);
$pageDescription = __t('news') . ' — ' . getSetting('site_name', SITE_NAME);
$extraSchema = renderBreadcrumbSchema([
    ['name' => __t('home'), 'url' => absoluteUrl(url())],
    ['name' => __t('news'), 'url' => currentUrl()],
]);

require __DIR__ . '/includes/header.php';
?>

<div class="container-im im-page-head">
    <h1><?= __t('news') ?></h1>
</div>

<section class="container-im">
    <div class="im-news-grid">
        <?php foreach ($news as $item): ?>
        <article class="im-news-card">
            <p class="im-news-date"><?= date('d.m.Y', strtotime($item['created_at'])) ?></p>
            <h3><a href="<?= url('news.php?slug=' . $item['slug']) ?>"><?= e(getNewsTitle($item)) ?></a></h3>
            <p class="im-news-excerpt"><?= e(getNewsExcerpt($item)) ?></p>
            <a href="<?= url('news.php?slug=' . $item['slug']) ?>" class="im-btn"><?= __t('read_more') ?> <i class="bi bi-arrow-right"></i></a>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>