<?php
/**
 * Header & sidebar untuk seluruh halaman admin.
 * $activePage dipakai untuk highlight menu sidebar yang aktif.
 */
$activePage = $activePage ?? '';
$adminTitle = $adminTitle ?? 'Admin - Pasarin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($adminTitle) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">
            <span class="brand-logo">P</span> Pasarin Admin
        </div>
        <nav>
            <a href="<?= BASE_URL ?>/admin/index.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
            <a href="<?= BASE_URL ?>/admin/orders.php" class="<?= $activePage === 'orders' ? 'active' : '' ?>">📦 Daftar Order</a>
            <a href="<?= BASE_URL ?>/admin/users.php" class="<?= $activePage === 'users' ? 'active' : '' ?>">👥 Daftar User</a>
            <a href="<?= BASE_URL ?>/index.php">🏬 Lihat Toko</a>
            <a href="<?= BASE_URL ?>/logout.php">🚪 Logout</a>
        </nav>
    </aside>
    <main class="admin-content">
