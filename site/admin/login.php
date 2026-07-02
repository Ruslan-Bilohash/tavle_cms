<?php
/**
 * Bilen CMS Admin - Login
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/functions.php';

if (isAdmin()) {
    redirect('/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? null)) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = Database::fetchOne(
            'SELECT * FROM users WHERE username = ? AND is_active = 1',
            's', [$username]
        );

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            $redirect = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);
            if ($redirect) {
                redirect($redirect);
            }
            if ($user['role'] === 'admin' || $user['role'] === 'dealer') {
                redirect('/admin/');
            }
            redirect(url('my-listings.php'));
        }

        $error = 'Невірний логін або пароль.';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід — Bilen Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 12px; padding: 2rem; width: 100%; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,.2); }
        .login-card h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; text-align: center; }
        .btn-login { background: #e94560; border: none; color: #fff; }
        .btn-login:hover { background: #c73a52; color: #fff; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1><i class="bi bi-lock"></i> Bilen Admin</h1>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Логін</label>
                <input type="text" name="username" class="form-control" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Пароль</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login w-100">Увійти</button>
        </form>
        <p class="text-center text-muted mt-3 mb-0"><small>Demo: admin / admin123</small></p>
    </div>
</body>
</html>