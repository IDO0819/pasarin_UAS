<?php
/**
 * REST API: Produk
 *
 * GET /api/products.php               -> daftar produk (limit, skip)
 * GET /api/products.php?id=1          -> detail produk
 * GET /api/products.php?q=phone       -> pencarian produk
 * GET /api/products.php?category=xxx  -> produk berdasarkan kategori
 *
 * Semua data diambil melalui DummyJsonService (bukan tabel MySQL).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/DummyJsonService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}

$service = new DummyJsonService();

// Detail produk berdasarkan ID
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $product = $service->getProduct($id);

    if ($product === null || isset($product['message'])) {
        jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
    }

    jsonResponse(['success' => true, 'data' => $product]);
}

// Pencarian produk
if (!empty($_GET['q'])) {
    $keyword = sanitize($_GET['q']);
    $result = $service->searchProducts($keyword);

    if ($result === null) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengambil data dari DummyJSON'], 502);
    }

    jsonResponse(['success' => true, 'data' => $result['products'] ?? [], 'total' => $result['total'] ?? 0]);
}

// Produk berdasarkan kategori
if (!empty($_GET['category'])) {
    $slug = sanitize($_GET['category']);
    $result = $service->getProductsByCategory($slug);

    if ($result === null) {
        jsonResponse(['success' => false, 'message' => 'Gagal mengambil data dari DummyJSON'], 502);
    }

    jsonResponse(['success' => true, 'data' => $result['products'] ?? [], 'total' => $result['total'] ?? 0]);
}

// Default: daftar produk dengan pagination
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
$skip  = isset($_GET['skip']) ? (int) $_GET['skip'] : 0;

$result = $service->getProducts($limit, $skip);

if ($result === null) {
    jsonResponse(['success' => false, 'message' => 'Gagal mengambil data dari DummyJSON'], 502);
}

jsonResponse([
    'success' => true,
    'data'    => $result['products'] ?? [],
    'total'   => $result['total'] ?? 0,
    'limit'   => $result['limit'] ?? $limit,
    'skip'    => $result['skip'] ?? $skip,
]);
