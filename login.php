<?php
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '/admin/index.php' : '/index.php');
}

$pageTitle = 'Login - Pasarin';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-card">
    <h2>Masuk ke Pasarin</h2>
    <div id="form-alert" class="alert alert-error" style="display:none;"></div>

    <form id="login-form">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>

    <div class="form-footer">
        Belum punya akun? <a href="<?= BASE_URL ?>/register.php">Daftar sekarang</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/auth.js"></script>
