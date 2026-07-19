<?php
/**
 * REST API: Keranjang Belanja
 *
 * GET    /api/cart.php              -> lihat isi keranjang (data produk disinkron dari DummyJSON)
 * POST   /api/cart.php  {product_id, qty}          -> tambah/update item di keranjang
 * PUT    /api/cart.php  {product_id, qty}           -> ubah qty item
 * DELETE /api/cart.php  {product_id}                -> hapus item dari keranjang
 *
 * Tabel MySQL (cart, cart_items) HANYA menyimpan product_id & qty.
 * Nama produk & harga SELALU diambil ulang dari DummyJSON saat ditampilkan.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/DummyJsonService.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Anda harus login terlebih dahulu'], 401);
}

$pdo     = getDBConnection();
$userId  = (int) $_SESSION['user_id'];
$service = new DummyJsonService();

/**
 * Mengambil (atau membuat) baris cart milik user yang sedang login.
 */
function getOrCreateCartId(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    $cart = $stmt->fetch();

    if ($cart) {
        return (int) $cart['id'];
    }

    $insert = $pdo->prepare('INSERT INTO cart (user_id, created_at) VALUES (?, NOW())');
    $insert->execute([$userId]);
    return (int) $pdo->lastInsertId();
}

$method = $_SERVER['REQUEST_METHOD'];
$cartId = getOrCreateCartId($pdo, $userId);

switch ($method) {
    case 'GET':
        $stmt = $pdo->prepare('SELECT product_id, qty FROM cart_items WHERE cart_id = ? ORDER BY id DESC');
        $stmt->execute([$cartId]);
        $items = $stmt->fetchAll();

        $result   = [];
        $subtotal = 0;

        foreach ($items as $item) {
            // Sinkronisasi data produk dari DummyJSON, bukan dari MySQL
            $product = $service->getProduct((int) $item['product_id']);

            if ($product === null || isset($product['message'])) {
                continue; // Produk mungkin sudah tidak tersedia di DummyJSON
            }

            $price = (float) ($product['price'] ?? 0);
            $qty   = (int) $item['qty'];
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;

            $result[] = [
                'product_id' => (int) $item['product_id'],
                'title'      => $product['title'] ?? '-',
                'thumbnail'  => $product['thumbnail'] ?? '',
                'price'      => $price,
                'stock'      => $product['stock'] ?? 0,
                'qty'        => $qty,
                'line_total' => $lineTotal,
            ];
        }

        jsonResponse(['success' => true, 'data' => $result, 'subtotal' => $subtotal]);
        break;

    case 'POST':
        $input     = getJsonInput();
        $productId = (int) ($input['product_id'] ?? 0);
        $qty       = max(1, (int) ($input['qty'] ?? 1));

        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'product_id tidak valid'], 422);
        }

        // Pastikan produk benar-benar ada di DummyJSON sebelum disimpan
        $product = $service->getProduct($productId);
        if ($product === null || isset($product['message'])) {
            jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan di DummyJSON'], 404);
        }

        $stmt = $pdo->prepare('SELECT id, qty FROM cart_items WHERE cart_id = ? AND product_id = ?');
        $stmt->execute([$cartId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = (int) $existing['qty'] + $qty;
            $update = $pdo->prepare('UPDATE cart_items SET qty = ? WHERE id = ?');
            $update->execute([$newQty, $existing['id']]);
        } else {
            $insert = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, qty, created_at) VALUES (?, ?, ?, NOW())');
            $insert->execute([$cartId, $productId, $qty]);
        }

        jsonResponse(['success' => true, 'message' => 'Produk ditambahkan ke keranjang']);
        break;

    case 'PUT':
        $input     = getJsonInput();
        $productId = (int) ($input['product_id'] ?? 0);
        $qty       = (int) ($input['qty'] ?? 0);

        if ($productId <= 0 || $qty <= 0) {
            jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 422);
        }

        $update = $pdo->prepare('UPDATE cart_items SET qty = ? WHERE cart_id = ? AND product_id = ?');
        $update->execute([$qty, $cartId, $productId]);

        jsonResponse(['success' => true, 'message' => 'Jumlah item diperbarui']);
        break;

    case 'DELETE':
        $input     = getJsonInput();
        $productId = (int) ($input['product_id'] ?? 0);

        if ($productId <= 0) {
            jsonResponse(['success' => false, 'message' => 'product_id tidak valid'], 422);
        }

        $delete = $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?');
        $delete->execute([$cartId, $productId]);

        jsonResponse(['success' => true, 'message' => 'Item dihapus dari keranjang']);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
}
