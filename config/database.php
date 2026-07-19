<?php
/**
 * Konfigurasi Koneksi Database MySQL
 * Menggunakan PDO agar bisa memakai Prepared Statement (aman dari SQL Injection)
 */

// Ubah sesuai konfigurasi XAMPP di komputer Anda
define('DB_HOST', 'localhost');
define('DB_NAME', 'pasarin');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Mengembalikan koneksi PDO singleton.
 * Menggunakan pola singleton supaya koneksi database
 * tidak dibuat berulang kali pada satu request yang sama.
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Jangan tampilkan detail koneksi ke user demi keamanan
            http_response_code(500);
            die('Koneksi database gagal. Silakan periksa konfigurasi config/database.php');
        }
    }

    return $pdo;
}
