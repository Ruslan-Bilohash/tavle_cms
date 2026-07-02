<?php
/** @var string $adminTitle */
require_once __DIR__ . '/i18n.php';
$adminTitle = $adminTitle ?? __a('dashboard');
$flash = getAdminFlash();
$adminLang = getAdminLang();
$self = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="<?= e($adminLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle) ?> — Bilen Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-width: 250px; --accent: #e30613; }
        body { background: #f4f6f9; }
        .admin-sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #1a1a2e;
            color: #fff;
            position: fixed;
            left: 0; top: 0;
            padding: 1rem 0;
            z-index: 1040;
            overflow-y: auto;
        }
        .admin-sidebar .brand { padding: 1rem 1.25rem; font-weight: 700; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,.1); margin-bottom: 1rem; }
        .admin-sidebar .brand i { color: var(--accent); }
        .admin-sidebar .nav-link { color: rgba(255,255,255,.75); padding: .6rem 1.25rem; display: flex; align-items: center; gap: .5rem; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.08); }
        .admin-sidebar .nav-link.active { border-left: 3px solid var(--accent); }
        .admin-content { margin-left: var(--sidebar-width); padding: 1rem 1.5rem 2rem; min-height: 100vh; }
        .admin-topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 12px; flex-wrap: wrap; }
        .stat-card { background: #fff; border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--accent); }
        .stat-card .stat-label { font-size: .85rem; color: #64748b; margin-top: .25rem; }
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.06); border-radius: 10px; }
        .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; font-weight: 600; }
        @media (max-width: 991px) {
            .admin-sidebar { transform: translateX(-100%); transition: transform .25s; }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-content { margin-left: 0; padding-top: .5rem; }
            .admin-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 1035; }
            .admin-overlay.show { display: block; }
        }
    </style>
    <script>window.BILEN_BASE = <?= json_encode(rtrim(BASE_PATH, '/')) ?>;</script>
</head>
<body<?= (defined('DEMO_BANNER') && DEMO_BANNER) ? ' class="has-demo-banner"' : '' ?>>
<?php if (defined('DEMO_BANNER') && DEMO_BANNER): ?>
<div class="im-demo-banner" role="note">
    <div class="container-fluid px-3 im-demo-banner-inner" style="min-height:36px;padding:6px 0">
        <i class="bi bi-info-circle-fill im-demo-banner-icon" aria-hidden="true"></i>
        <p class="im-demo-banner-text mb-0"><?= e(__a('demo_banner') ?: __t('demo_banner')) ?></p>
    </div>
</div>
<style>
.im-demo-banner{background:linear-gradient(90deg,#fef3c7,#fde68a,#fef3c7);border-bottom:1px solid #f59e0b;color:#78350f;font-size:13px;font-weight:600;position:sticky;top:0;z-index:1050}
.im-demo-banner-inner{display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;text-align:center}
.im-demo-banner-icon{color:#d97706}
body.has-demo-banner .admin-sidebar{top:40px;height:calc(100vh - 40px)}
body.has-demo-banner .admin-content{padding-top:calc(1rem + 0px)}
</style>
<?php endif; ?>
<div class="admin-overlay" id="adminOverlay"></div>
<nav class="admin-sidebar" id="adminSidebar">
    <div class="brand"><i class="bi bi-speedometer2"></i> Bilen Admin</div>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link<?= $self === 'index.php' ? ' active' : '' ?>" href="<?= url('admin/') ?>?lang=<?= $adminLang ?>"><i class="bi bi-house"></i> <?= __a('dashboard') ?></a></li>
        <li class="nav-item"><a class="nav-link<?= $self === 'cars.php' ? ' active' : '' ?>" href="<?= url('admin/cars.php') ?>?lang=<?= $adminLang ?>"><i class="bi bi-car-front"></i> <?= __a('listings') ?></a></li>
        <li class="nav-item"><a class="nav-link<?= $self === 'brands.php' ? ' active' : '' ?>" href="<?= url('admin/brands.php') ?>?lang=<?= $adminLang ?>"><i class="bi bi-tags"></i> <?= __a('brands') ?></a></li>
        <li class="nav-item"><a class="nav-link<?= $self === 'models.php' ? ' active' : '' ?>" href="<?= url('admin/models.php') ?>?lang=<?= $adminLang ?>"><i class="bi bi-list-ul"></i> <?= __a('models') ?></a></li>
        <li class="nav-item"><a class="nav-link<?= $self === 'users.php' ? ' active' : '' ?>" href="<?= url('admin/users.php') ?>?lang=<?= $adminLang ?>"><i class="bi bi-people"></i> <?= __a('users') ?></a></li>
        <li class="nav-item"><a class="nav-link<?= $self === 'news.php' ? ' active' : '' ?>" href="<?= url('admin/news.php') ?>?lang=<?= $adminLang ?>"><i class="bi bi-newspaper"></i> <?= __a('news') ?></a></li>
        <li class="nav-item"><a class="nav-link<?= $self === 'navigation.php' ? ' active' : '' ?>" href="<?= url('admin/navigation.php') ?>?lang=<?= $adminLang ?>"><i class="bi bi-menu-button-wide"></i> <?= __a('navigation') ?></a></li>
        <li class="nav-item"><a class="nav-link<?= $self === 'settings.php' ? ' active' : '' ?>" href="<?= url('admin/settings.php') ?>?lang=<?= $adminLang ?>"><i class="bi bi-gear"></i> <?= __a('settings') ?></a></li>
        <li class="nav-item mt-3"><a class="nav-link" href="<?= url() ?>" target="_blank"><i class="bi bi-box-arrow-up-right"></i> <?= __a('site') ?></a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('admin/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> <?= __a('logout') ?></a></li>
    </ul>
</nav>
<div class="admin-content">
    <div class="admin-topbar">
        <button class="btn btn-outline-secondary d-lg-none" type="button" id="adminMenuBtn"><i class="bi bi-list"></i></button>
        <div class="dropdown ms-auto">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><?= strtoupper($adminLang) ?></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <?php foreach (AVAILABLE_LANGS as $l): ?>
                <li><a class="dropdown-item<?= $l === $adminLang ? ' active' : '' ?>" href="<?= adminLangUrl($l) ?>"><?= strtoupper($l) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>