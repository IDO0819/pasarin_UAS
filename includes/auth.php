<?php
/**
 * Fungsi bantu untuk otentikasi user dan proteksi CSRF sederhana.
 */

/**
 * Mengecek apakah user sedang login.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Mengecek apakah user yang login adalah admin.
 */
function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Memaksa user harus login. Jika belum, redirect ke halaman login.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

/**
 * Memaksa hanya admin yang boleh mengakses halaman.
 */
function requireAdmin(): void
{
    if (!isAdmin()) {
        redirect('/login.php');
    }
}

/**
 * Membuat token CSRF dan menyimpannya di session.
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Memverifikasi token CSRF yang dikirim dari form/AJAX.
 */
function verifyCsrfToken(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
