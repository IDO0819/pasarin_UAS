<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pdo = getDBConnection();
$users = $pdo->query(
    "SELECT u.id, u.username, u.email, u.role, u.created_at,
            (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS total_orders
     FROM users u
     ORDER BY u.created_at DESC"
)->fetchAll();

$activePage = 'users';
$adminTitle = 'Daftar User - Pasarin Admin';
require_once __DIR__ . '/includes/admin_header.php';
?>

<h1 style="margin-bottom:20px;">Daftar User</h1>

<table>
    <thead>
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Total Pesanan</th>
            <th>Bergabung</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= sanitize($user['username']) ?></td>
                <td><?= sanitize($user['email']) ?></td>
                <td><span class="badge"><?= ucfirst(sanitize($user['role'])) ?></span></td>
                <td><?= (int) $user['total_orders'] ?></td>
                <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
            <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Belum ada user.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
