<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Detail Produk - Pasarin';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container section">
    <div id="product-detail-container">
        <div class="loader">Memuat detail produk...</div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/product.js"></script>
