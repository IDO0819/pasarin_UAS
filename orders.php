<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
$pageTitle = 'Pesanan Saya - Pasarin';

$pdo = getDBConnection();
$stmt = $pdo->prepare('SELECT id, invoice, total, ongkir, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section class="container section">
    <h2 class="section-title">Pesanan Saya</h2>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="icon">📦</div>
            <p>Kamu belum memiliki pesanan.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary" style="margin-top:12px;">Mulai Belanja</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Ongkir</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= sanitize($order['invoice']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                        <td><?= formatRupiah((float) $order['ongkir']) ?></td>
                        <td><strong><?= formatRupiah((float) $order['total']) ?></strong></td>
                        <td>
                            <span class="status-pill status-<?= sanitize($order['status']) ?>">
                                <?= ucfirst(sanitize($order['status'])) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
