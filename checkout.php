<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
$pageTitle = 'Checkout - Pasarin';
$csrfToken = generateCsrfToken();
require_once __DIR__ . '/includes/header.php';
?>

<section class="container section">
    <h2 class="section-title">Checkout</h2>

    <div class="cart-layout">
        <div>
            <div class="form-card" style="max-width:none; margin:0 0 20px;">
                <h3 style="margin-bottom:16px; text-align:left; color:var(--text);">Alamat & Pengiriman</h3>

                <form id="courier-form">
                    <div class="form-group">
                        <label for="destination_city_id">ID Kota Tujuan (sesuai API Ongkir)</label>
                        <input type="text" id="destination_city_id" placeholder="Contoh: 155 (Yogyakarta)" required>
                    </div>
                    <div class="form-group">
                        <label for="kurir">Kurir</label>
                        <select id="kurir">
                            <option value="jne">JNE</option>
                            <option value="jnt">J&T</option>
                            <option value="sicepat">SiCepat</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline">Hitung Ongkir</button>
                </form>

                <div id="ongkir-result" style="margin-top:16px;"></div>
            </div>

            <div class="form-card" style="max-width:none;">
                <h3 style="margin-bottom:16px; text-align:left; color:var(--text);">Detail Alamat Pengiriman</h3>
                <form id="checkout-form">
                    <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                    <input type="hidden" name="layanan" id="layanan" value="">
                    <input type="hidden" name="ongkir_value" id="ongkir_value" value="0">
                    <input type="hidden" name="kurir_final" id="kurir_final_hidden">

                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea name="alamat" id="alamat" rows="3" required placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos"></textarea>
                    </div>

                    <div id="form-alert" class="alert alert-error" style="display:none;"></div>

                    <button type="submit" class="btn btn-primary btn-block">Buat Pesanan</button>
                </form>
            </div>
        </div>

        <div class="cart-summary">
            <h3 style="margin-bottom:16px;">Ringkasan Pesanan</h3>
            <div id="checkout-items"></div>
            <hr style="margin:12px 0; border:none; border-top:1px solid var(--border);">
            <div class="summary-row">
                <span>Ongkos Kirim</span>
                <span id="checkout-ongkir-display">Rp 0</span>
            </div>
            <div class="summary-row summary-total">
                <span>Total</span>
                <span id="checkout-total">Rp 0</span>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/checkout.js"></script>
