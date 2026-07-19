<?php
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '/admin/index.php' : '/index.php');
}

$pageTitle = 'Daftar Akun - Pasarin';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h2>Buat Akun Baru</h2>
    <div id="form-alert" class="alert alert-error" style="display:none;"></div>

    <form id="register-form">
        <div class="form-group">
            <label for="username">Nama Lengkap</label>
            <input type="text" name="username" id="username" required minlength="3">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="password_confirm">Konfirmasi Password</label>
            <input type="password" name="password_confirm" id="password_confirm" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Daftar</button>
    </form>

    <div class="form-footer">
        Sudah punya akun? <a href="<?= BASE_URL ?>/login.php">Masuk di sini</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
