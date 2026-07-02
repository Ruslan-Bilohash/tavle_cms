<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    $name = sanitize($_POST['name'] ?? '');
    $slug = slugify($name);
    $sort = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'add' && $name) {
        Database::insert('INSERT INTO brands (name, slug, sort_order, is_active) VALUES (?, ?, ?, ?)', 'ssii', [$name, $slug, $sort, $active]);
        adminFlash('success', 'Бренд додано.');
        redirect('/admin/brands.php');
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $name) {
            Database::execute('UPDATE brands SET name=?, slug=?, sort_order=?, is_active=? WHERE id=?', 'ssiii', [$name, $slug, $sort, $active, $id]);
            adminFlash('success', 'Бренд оновлено.');
            redirect('/admin/brands.php');
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    Database::execute('UPDATE brands SET is_active = 0 WHERE id = ?', 'i', [(int)$_GET['id']]);
    adminFlash('success', 'Бренд деактивовано.');
    redirect('/admin/brands.php');
}

$brands = Database::fetchAll('SELECT b.*, COUNT(c.id) as car_count FROM brands b LEFT JOIN cars c ON c.brand_id = b.id GROUP BY b.id ORDER BY b.sort_order, b.name');
$editBrand = ($action === 'edit' && isset($_GET['id'])) ? Database::fetchOne('SELECT * FROM brands WHERE id = ?', 'i', [(int)$_GET['id']]) : null;

$adminTitle = 'Бренди';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Бренди</h2>
    <?php if ($action !== 'add' && !$editBrand): ?>
    <a href="?action=add" class="btn btn-primary">+ Додати бренд</a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $editBrand): ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="post">
            <?= csrfField() ?>
            <?php if ($editBrand): ?><input type="hidden" name="id" value="<?= (int)$editBrand['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Назва</label>
                    <input type="text" name="name" class="form-control" required value="<?= e($editBrand['name'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Сортування</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= (int)($editBrand['sort_order'] ?? 0) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active"<?= ($editBrand['is_active'] ?? 1) ? ' checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Активний</label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Зберегти</button>
                <a href="<?= url('admin/brands.php') ?>" class="btn btn-secondary">Скасувати</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Назва</th><th>Slug</th><th>Авто</th><th>Статус</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($brands as $b): ?>
            <tr>
                <td><?= (int)$b['id'] ?></td>
                <td><?= e($b['name']) ?></td>
                <td><code><?= e($b['slug']) ?></code></td>
                <td><?= (int)$b['car_count'] ?></td>
                <td><?= $b['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                <td>
                    <a href="?action=edit&id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-primary">Ред.</a>
                    <a href="?action=delete&id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Деактивувати?')">×</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>