<?php
/**
 * Header dan Navbar yang dipakai di semua halaman frontend.
 * Variabel $pageTitle bisa di-set sebelum include file ini.
 */
$pageTitle = $pageTitle ?? 'Pasarin - Belanja Online Mudah & Murah';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <script>window.PASARIN_BASE_URL = "<?= BASE_URL ?>";</script>
</head>
<body>
<header class="navbar">
    <div class="container navbar-inner">
        <a href="<?= BASE_URL ?>/index.php" class="brand">
            <span class="brand-logo">P</span>asarin
        </a>

        <form action="<?= BASE_URL ?>/index.php" method="GET" class="navbar-search">
            <input type="text" name="q" placeholder="Cari produk impian kamu..." value="<?= sanitize($_GET['q'] ?? '') ?>">
            <button type="submit" aria-label="Cari">🔍</button>
        </form>

        <nav class="navbar-links">
            <a href="<?= BASE_URL ?>/cart.php" class="nav-icon-link">
                🛒 <span>Keranjang</span>
            </a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/orders.php" class="nav-icon-link">📦 <span>Pesanan</span></a>
                <span class="nav-user">Halo, <?= sanitize($_SESSION['username']) ?></span>
                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline">Masuk</a>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Daftar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
