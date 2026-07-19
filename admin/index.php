<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pdo = getDBConnection();

// Statistik ringkas untuk dashboard
$totalUsers  = $pdo->query('SELECT COUNT(*) AS total FROM users WHERE role = "customer"')->fetch()['total'];
$totalOrders = $pdo->query('SELECT COUNT(*) AS total FROM orders')->fetch()['total'];
$totalRevenue = $pdo->query('SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE status != "dibatalkan"')->fetch()['total'];
$pendingOrders = $pdo->query('SELECT COUNT(*) AS total FROM orders WHERE status = "pending"')->fetch()['total'];

$recentOrders = $pdo->query(
    'SELECT o.invoice, o.total, o.status, o.created_at, u.username
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 8'
)->fetchAll();

$activePage = 'dashboard';
$adminTitle = 'Dashboard Admin - Pasarin';
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 style="margin-bottom:20px;">Dashboard</h1>

<div class="stat-grid">
    <div class="stat-card">
        <div class="value"><?= (int) $totalUsers ?></div>
        <div class="label">Total Pelanggan</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= (int) $totalOrders ?></div>
        <div class="label">Total Pesanan</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= formatRupiah((float) $totalRevenue) ?></div>
        <div class="label">Total Pendapatan</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= (int) $pendingOrders ?></div>
        <div class="label">Pesanan Pending</div>
    </div>
</div>

<h2 style="margin-bottom:16px;">Pesanan Terbaru</h2>
<table>
    <thead>
        <tr>
            <th>Invoice</th>
            <th>Pelanggan</th>
            <th>Total</th>
            <th>Status</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recentOrders as $order): ?>
            <tr>
                <td><?= sanitize($order['invoice']) ?></td>
                <td><?= sanitize($order['username']) ?></td>
                <td><?= formatRupiah((float) $order['total']) ?></td>
                <td><span class="status-pill status-<?= sanitize($order['status']) ?>"><?= ucfirst(sanitize($order['status'])) ?></span></td>
                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($recentOrders)): ?>
            <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Belum ada pesanan.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
