<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
$pageTitle = 'Keranjang Belanja - Pasarin';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container section">
    <h2 class="section-title">Keranjang Belanja</h2>

    <div class="cart-layout">
        <div id="cart-items-container">
            <div class="loader">Memuat keranjang...</div>
        </div>

        <div class="cart-summary">
            <h3 style="margin-bottom:16px;">Ringkasan Belanja</h3>
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="cart-summary-subtotal">Rp 0</span>
            </div>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:16px;">
                Ongkos kirim dihitung di halaman checkout.
            </p>
            <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-block" id="checkout-btn">Checkout</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/cart.js"></script>
