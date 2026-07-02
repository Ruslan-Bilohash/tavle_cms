<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'user');
    $active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if ($action === 'add' && $username && $email && $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        Database::insert(
            'INSERT INTO users (username, email, password, role, name, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'ssssssi', [$username, $email, $hash, $role, $name, $phone, $active]
        );
        adminFlash('success', 'Користувача додано.');
        redirect('/admin/users.php');
    }

    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $username && $email) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                Database::execute(
                    'UPDATE users SET username=?, email=?, password=?, role=?, name=?, phone=?, is_active=? WHERE id=?',
                    'ssssssii', [$username, $email, $hash, $role, $name, $phone, $active, $id]
                );
            } else {
                Database::execute(
                    'UPDATE users SET username=?, email=?, role=?, name=?, phone=?, is_active=? WHERE id=?',
                    'sssssii', [$username, $email, $role, $name, $phone, $active, $id]
                );
            }
            adminFlash('success', 'Користувача оновлено.');
            redirect('/admin/users.php');
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id !== (int)$_SESSION['user_id']) {
        Database::execute('UPDATE users SET is_active = 0 WHERE id = ?', 'i', [$id]);
        adminFlash('success', 'Користувача деактивовано.');
    }
    redirect('/admin/users.php');
}

$users = Database::fetchAll('SELECT * FROM users ORDER BY created_at DESC');
$editUser = ($action === 'edit' && isset($_GET['id'])) ? Database::fetchOne('SELECT * FROM users WHERE id = ?', 'i', [(int)$_GET['id']]) : null;

$adminTitle = 'Користувачі';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Користувачі</h2>
    <?php if ($action !== 'add' && !$editUser): ?>
    <a href="?action=add" class="btn btn-primary">+ Додати</a>
    <?php endif; ?>
</div>

<?php if ($action === 'add' || $editUser): ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="post">
            <?= csrfField() ?>
            <?php if ($editUser): ?><input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Логін</label><input type="text" name="username" class="form-control" required value="<?= e($editUser['username'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= e($editUser['email'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Ім'я</label><input type="text" name="name" class="form-control" required value="<?= e($editUser['name'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Телефон</label><input type="text" name="phone" class="form-control" value="<?= e($editUser['phone'] ?? '') ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Роль</label>
                    <select name="role" class="form-select">
                        <option value="user"<?= ($editUser['role'] ?? '') === 'user' ? ' selected' : '' ?>>User</option>
                        <option value="dealer"<?= ($editUser['role'] ?? '') === 'dealer' ? ' selected' : '' ?>>Dealer</option>
                        <option value="admin"<?= ($editUser['role'] ?? '') === 'admin' ? ' selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Пароль<?= $editUser ? ' (залишити пустим)' : '' ?></label><input type="password" name="password" class="form-control"<?= $editUser ? '' : ' required' ?>></div>
                <div class="col-12"><div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" id="is_active"<?= ($editUser['is_active'] ?? 1) ? ' checked' : '' ?>><label class="form-check-label" for="is_active">Активний</label></div></div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Зберегти</button>
                <a href="<?= url('admin/users.php') ?>" class="btn btn-secondary">Скасувати</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Логін</th><th>Ім'я</th><th>Email</th><th>Роль</th><th>Статус</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'dealer' ? 'info' : 'secondary') ?>"><?= e($u['role']) ?></span></td>
                <td><?= $u['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                <td>
                    <a href="?action=edit&id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-primary">Ред.</a>
                    <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                    <a href="?action=delete&id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Деактивувати?')">×</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>