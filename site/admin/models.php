<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$brands = Database::fetchAll('SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    $brandId = (int)($_POST['brand_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $slug = slugify($name);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'add' && $brandId && $name) {
        Database::insert('INSERT INTO models (brand_id, name, slug, is_active) VALUES (?, ?, ?, ?)', 'issi', [$brandId, $name, $slug, $active]);
        adminFlash('success', 'Модель додано.');
        redirect('/admin/models.php');
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $brandId && $name) {
            Database::execute('UPDATE models SET brand_id=?, name=?, slug=?, is_active=? WHERE id=?', 'issii', [$brandId, $name, $slug, $active, $id]);
            adminFlash('success', 'Модель оновлено.');
            redirect('/admin/models.php');
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    Database::execute('UPDATE models SET is_active = 0 WHERE id = ?', 'i', [(int)$_GET['id']]);
    adminFlash('success', 'Модель деактивовано.');
    redirect('/admin/models.php');
}

$models = Database::fetchAll(
    'SELECT m.*, b.name as brand_name FROM models m JOIN brands b ON m.brand_id = b.id ORDER BY b.name, m.name'
);
$editModel = ($action === 'edit' && isset($_GET['id'])) ? Database::fetchOne('SELECT * FROM models WHERE id = ?', 'i', [(int)$_GET['id']]) : null;

$adminTitle = 'Моделі';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Моделі</h2>
    <?php if ($action !== 'add' && !$editModel): ?>
    <a href="?action=add" class="btn btn-primary">+ Додати модель</a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $editModel): ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="post">
            <?= csrfField() ?>
            <?php if ($editModel): ?><input type="hidden" name="id" value="<?= (int)$editModel['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Бренд</label>
                    <select name="brand_id" class="form-select" required>
                        <option value="">—</option>
                        <?php foreach ($brands as $b): ?>
                        <option value="<?= (int)$b['id'] ?>"<?= ($editModel['brand_id'] ?? '') == $b['id'] ? ' selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Назва моделі</label>
                    <input type="text" name="name" class="form-control" required value="<?= e($editModel['name'] ?? '') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active"<?= ($editModel['is_active'] ?? 1) ? ' checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Активна</label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Зберегти</button>
                <a href="<?= url('admin/models.php') ?>" class="btn btn-secondary">Скасувати</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Бренд</th><th>Модель</th><th>Slug</th><th>Статус</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($models as $m): ?>
            <tr>
                <td><?= (int)$m['id'] ?></td>
                <td><?= e($m['brand_name']) ?></td>
                <td><?= e($m['name']) ?></td>
                <td><code><?= e($m['slug']) ?></code></td>
                <td><?= $m['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                <td>
                    <a href="?action=edit&id=<?= (int)$m['id'] ?>" class="btn btn-sm btn-outline-primary">Ред.</a>
                    <a href="?action=delete&id=<?= (int)$m['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Деактивувати?')">×</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>