<?php
/**
 * Kumpulan fungsi bantu (helper) yang dipakai di banyak halaman.
 */

/**
 * Membersihkan input string dari karakter berbahaya (XSS).
 */
function sanitize(?string $value): string
{
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Mengirim response JSON dan menghentikan eksekusi.
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Format angka menjadi format Rupiah, contoh: Rp 150.000
 */
function formatRupiah(float $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Redirect ke halaman lain lalu hentikan eksekusi.
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * Membuat invoice unik untuk setiap order.
 */
function generateInvoice(): string
{
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Mengambil input JSON dari body request (dipakai di REST API).
 */
function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
