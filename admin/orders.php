<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pdo = getDBConnection();
$message = '';

// Update status pesanan (form sederhana, bukan AJAX, tetap pakai prepared statement & CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $message = 'Token CSRF tidak valid.';
    } else {
        $orderId   = (int) ($_POST['order_id'] ?? 0);
        $newStatus = sanitize($_POST['status'] ?? '');
        $validStatuses = ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

        if ($orderId && in_array($newStatus, $validStatuses, true)) {
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $orderId]);
            $message = 'Status pesanan berhasil diperbarui.';
        } else {
            $message = 'Data tidak valid.';
        }
    }
}

$orders = $pdo->query(
    'SELECT o.id, o.invoice, o.total, o.status, o.created_at, u.username, u.email
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC'
)->fetchAll();

$csrfToken = generateCsrfToken();
$activePage = 'orders';
$adminTitle = 'Daftar Order - Pasarin Admin';
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 style="margin-bottom:20px;">Daftar Order</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= sanitize($message) ?></div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Invoice</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Ubah Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= sanitize($order['invoice']) ?></td>
                <td><?= sanitize($order['username']) ?><br><small style="color:var(--text-muted);"><?= sanitize($order['email']) ?></small></td>
                <td><?= formatRupiah((float) $order['total']) ?></td>
                <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                <td><span class="status-pill status-<?= sanitize($order['status']) ?>"><?= ucfirst(sanitize($order['status'])) ?></span></td>
                <td>
                    <form method="POST" style="display:flex; gap:6px;">
                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <select name="status">
                            <?php foreach (['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'] as $status): ?>
                                <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline">Simpan</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
            <tr><td colspan="6" style="text-align:center; color:var(--text-muted);">Belum ada pesanan.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
