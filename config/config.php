<?php
/**
 * Konfigurasi Umum Aplikasi Pasarin
 */

// Mulai session di satu tempat saja supaya tidak duplikat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL aplikasi (sesuaikan jika folder project berbeda nama)
define('BASE_URL', '/pasarin');

// Konfigurasi API DummyJSON
define('DUMMYJSON_BASE_URL', 'https://dummyjson.com');

// Konfigurasi API Ongkos Kirim (contoh menggunakan Komerce/Ongkir open API)
// Ganti API_KEY dan BASE_URL sesuai provider ongkir yang dipakai
define('ONGKIR_API_KEY', 'GANTI_DENGAN_API_KEY_ANDA');
define('ONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter');
define('ONGKIR_ORIGIN_CITY_ID', '153'); // Contoh: Jakarta Pusat

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Tampilkan error hanya saat development. Set 0 saat presentasi/produksi.
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
