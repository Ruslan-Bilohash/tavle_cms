<?php
/**
 * Bilen CMS Admin - Authentication guard
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/functions.php';
require_once __DIR__ . '/i18n.php';

function requireAdmin(): void
{
    if (!isLoggedIn() || !isAdmin()) {
        redirect('/admin/login.php');
    }
}

function adminFlash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function getAdminFlash(): ?array
{
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $flash;
}