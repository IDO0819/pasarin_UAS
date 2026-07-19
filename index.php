<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Pasarin - Belanja Online Mudah & Murah';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container">
    <div class="hero">
        <h1>Belanja Apapun, Semua Ada di Pasarin</h1>
        <p>Ribuan produk pilihan dengan harga terbaik, langsung dari katalog terpercaya.</p>
    </div>
</section>

<section class="container section">
    <h2 class="section-title">Kategori Pilihan</h2>
    <div class="category-grid" id="category-grid">
        <div class="loader">Memuat kategori...</div>
    </div>
</section>

<section class="container section">
    <h2 class="section-title" id="product-section-title">Produk Pilihan Untukmu</h2>
    <div class="product-grid" id="product-grid">
        <div class="loader">Memuat produk...</div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/home.js"></script>
