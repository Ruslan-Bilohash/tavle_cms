<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    $titleUk = sanitize($_POST['title_uk'] ?? '');
    $slug = slugify($titleUk);
    $excerptUk = sanitize($_POST['excerpt_uk'] ?? '');
    $contentUk = $_POST['content_uk'] ?? '';
    $titleEn = sanitize($_POST['title_en'] ?? '');
    $excerptEn = sanitize($_POST['excerpt_en'] ?? '');
    $contentEn = $_POST['content_en'] ?? '';
    $published = isset($_POST['is_published']) ? 1 : 0;

    if ($action === 'add' && $titleUk) {
        Database::insert(
            'INSERT INTO news (title_uk, title_en, slug, excerpt_uk, excerpt_en, content_uk, content_en, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'sssssssi', [$titleUk, $titleEn, $slug, $excerptUk, $excerptEn, $contentUk, $contentEn, $published]
        );
        adminFlash('success', 'Новину додано.');
        redirect('/admin/news.php');
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $titleUk) {
            Database::execute(
                'UPDATE news SET title_uk=?, title_en=?, slug=?, excerpt_uk=?, excerpt_en=?, content_uk=?, content_en=?, is_published=? WHERE id=?',
                'sssssssii', [$titleUk, $titleEn, $slug, $excerptUk, $excerptEn, $contentUk, $contentEn, $published, $id]
            );
            adminFlash('success', 'Новину оновлено.');
            redirect('/admin/news.php');
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    Database::execute('UPDATE news SET is_published = 0 WHERE id = ?', 'i', [(int)$_GET['id']]);
    adminFlash('success', 'Новину деактивовано.');
    redirect('/admin/news.php');
}

$newsList = Database::fetchAll('SELECT * FROM news ORDER BY created_at DESC');
$editNews = ($action === 'edit' && isset($_GET['id'])) ? Database::fetchOne('SELECT * FROM news WHERE id = ?', 'i', [(int)$_GET['id']]) : null;

$adminTitle = 'Новини';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Новини</h2>
    <?php if ($action !== 'add' && !$editNews): ?>
    <a href="?action=add" class="btn btn-primary">+ Додати новину</a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $editNews): ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="post">
            <?= csrfField() ?>
            <?php if ($editNews): ?><input type="hidden" name="id" value="<?= (int)$editNews['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Заголовок (UK)</label><input type="text" name="title_uk" class="form-control" required value="<?= e($editNews['title_uk'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Заголовок (EN)</label><input type="text" name="title_en" class="form-control" value="<?= e($editNews['title_en'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Короткий опис (UK)</label><input type="text" name="excerpt_uk" class="form-control" value="<?= e($editNews['excerpt_uk'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Короткий опис (EN)</label><input type="text" name="excerpt_en" class="form-control" value="<?= e($editNews['excerpt_en'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Контент (UK)</label><textarea name="content_uk" class="form-control" rows="5"><?= e($editNews['content_uk'] ?? '') ?></textarea></div>
                <div class="col-md-6"><label class="form-label">Контент (EN)</label><textarea name="content_en" class="form-control" rows="5"><?= e($editNews['content_en'] ?? '') ?></textarea></div>
                <div class="col-12"><div class="form-check"><input type="checkbox" name="is_published" class="form-check-input" id="is_published"<?= ($editNews['is_published'] ?? 1) ? ' checked' : '' ?>><label class="form-check-label" for="is_published">Опубліковано</label></div></div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Зберегти</button>
                <a href="<?= url('admin/news.php') ?>" class="btn btn-secondary">Скасувати</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Заголовок</th><th>Дата</th><th>Перегляди</th><th>Статус</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($newsList as $n): ?>
            <tr>
                <td><?= (int)$n['id'] ?></td>
                <td><?= e($n['title_uk']) ?></td>
                <td><?= date('d.m.Y', strtotime($n['created_at'])) ?></td>
                <td><?= (int)$n['views'] ?></td>
                <td><?= $n['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' ?></td>
                <td>
                    <a href="?action=edit&id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline-primary">Ред.</a>
                    <a href="?action=delete&id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Деактивувати?')">×</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>