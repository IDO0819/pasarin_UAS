<?php
/**
 * REST API: Orders (Checkout & Riwayat Pesanan)
 *
 * GET  /api/orders.php               -> daftar order milik user login
 * GET  /api/orders.php?id=1          -> detail satu order
 * POST /api/orders.php               -> proses checkout (buat order baru dari isi cart)
 *      body: { alamat, kurir, layanan, ongkir, csrf_token }
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
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Detail satu order beserta itemnya
    if (isset($_GET['id'])) {
        $orderId = (int) $_GET['id'];

        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch();

        if (!$order) {
            jsonResponse(['success' => false, 'message' => 'Order tidak ditemukan'], 404);
        }

        $itemStmt = $pdo->prepare('SELECT product_id, product_name, price, qty, subtotal FROM order_items WHERE order_id = ?');
        $itemStmt->execute([$orderId]);
        $order['items'] = $itemStmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $order]);
    }

    // Daftar semua order milik user
    $stmt = $pdo->prepare('SELECT id, invoice, total, ongkir, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $input = getJsonInput();

    if (!verifyCsrfToken($input['csrf_token'] ?? null)) {
        jsonResponse(['success' => false, 'message' => 'Token CSRF tidak valid'], 403);
    }

    $alamat  = sanitize($input['alamat'] ?? '');
    $kurir   = sanitize($input['kurir'] ?? '');
    $layanan = sanitize($input['layanan'] ?? '');
    $ongkir  = (float) ($input['ongkir'] ?? 0);

    if (!$alamat || !$kurir) {
        jsonResponse(['success' => false, 'message' => 'Alamat dan kurir wajib diisi'], 422);
    }

    // Ambil isi cart user
    $cartStmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = ?');
    $cartStmt->execute([$userId]);
    $cart = $cartStmt->fetch();

    if (!$cart) {
        jsonResponse(['success' => false, 'message' => 'Keranjang kosong'], 400);
    }

    $itemsStmt = $pdo->prepare('SELECT product_id, qty FROM cart_items WHERE cart_id = ?');
    $itemsStmt->execute([$cart['id']]);
    $cartItems = $itemsStmt->fetchAll();

    if (empty($cartItems)) {
        jsonResponse(['success' => false, 'message' => 'Keranjang kosong'], 400);
    }

    // Sinkronkan harga terbaru dari DummyJSON & hitung total
    $orderItems = [];
    $subtotal   = 0;

    foreach ($cartItems as $item) {
        $product = $service->getProduct((int) $item['product_id']);

        if ($product === null || isset($product['message'])) {
            continue;
        }

        $price     = (float) ($product['price'] ?? 0);
        $qty       = (int) $item['qty'];
        $lineTotal = $price * $qty;
        $subtotal += $lineTotal;

        $orderItems[] = [
            'product_id'   => (int) $item['product_id'],
            'product_name' => $product['title'] ?? '-',
            'price'        => $price,
            'qty'          => $qty,
            'subtotal'     => $lineTotal,
        ];
    }

    if (empty($orderItems)) {
        jsonResponse(['success' => false, 'message' => 'Produk di keranjang sudah tidak tersedia'], 400);
    }

    $total   = $subtotal + $ongkir;
    $invoice = generateInvoice();

    try {
        $pdo->beginTransaction();

        $insertOrder = $pdo->prepare(
            'INSERT INTO orders (user_id, invoice, alamat, kurir, layanan, ongkir, total, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, "pending", NOW())'
        );
        $insertOrder->execute([$userId, $invoice, $alamat, $kurir, $layanan, $ongkir, $total]);
        $orderId = (int) $pdo->lastInsertId();

        $insertItem = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, price, qty, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach ($orderItems as $oi) {
            $insertItem->execute([$orderId, $oi['product_id'], $oi['product_name'], $oi['price'], $oi['qty'], $oi['subtotal']]);
        }

        // Kosongkan cart setelah checkout berhasil
        $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cart['id']]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Checkout gagal, silakan coba lagi'], 500);
    }

    jsonResponse(['success' => true, 'message' => 'Checkout berhasil', 'invoice' => $invoice, 'order_id' => $orderId]);
}

jsonResponse(['success' => false, 'message' => 'Method tidak diizinkan'], 405);
