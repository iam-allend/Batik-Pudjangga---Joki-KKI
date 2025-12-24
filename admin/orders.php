<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: register.php");
    exit;
}

include '../config/db.php';

// Tampilkan notifikasi sukses/error
if (isset($_SESSION['success'])) {
    $success_msg = $_SESSION['success'];
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $error_msg = $_SESSION['error'];
    unset($_SESSION['error']);
}

$filter_status = $_GET['filter_status'] ?? '';
$where = $filter_status ? "WHERE o.status = :status" : "";
$query = "
    SELECT o.*, u.name AS user_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    $where
    ORDER BY o.created_at DESC
";

$stmt = $pdo->prepare($query);
if ($filter_status) {
    $stmt->bindValue(':status', $filter_status);
}
$stmt->execute();
$orders = $stmt->fetchAll();

$status_labels = [
    'pending'      => 'Menunggu Pembayaran',
    'processing'   => 'Dikemas',
    'shipped'      => 'Dalam Pengiriman',
    'delivered'    => 'Selesai',
    'cancelled'    => 'Dibatalkan'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Produk</a>
        <a href="subscribers.php">Subscriber</a>
        <a href="report_messages.php">Report Messages</a>
        <a href="orders.php">Orders Check</a>
        <a href="sales_report.php">Laporan</a>
        <a href="logout.php">Logout</a>
    </div>
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f2f3f7;
    margin: 0;
}
.admin-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 20px;
}
h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}
.order-filter {
    display: flex;
    gap: 10px;
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
select, button {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    background: #3b82f6;
    color: white;
    cursor: pointer;
    border: none;
}
.order-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 18px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.order-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.order-status {
    padding: 6px 12px;
    border-radius: 16px;
    font-size: .9rem;
    font-weight: bold;
}
.order-status.pending { background: #fff3cd; color: #856404; }
.order-status.processing { background: #cce5ff; color: #004085; }
.order-status.shipped { background: #d1ecf1; color: #0c5460; }
.order-status.delivered { background: #d4edda; color: #155724; }
.order-status.cancelled { background: #f8d7da; color: #721c24; }
.order-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.order-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-detail {
    background: #4b5563;
    padding: 8px 12px;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.btn-update {
    background: #10b981;
    padding: 8px 12px;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
/* NOTIFIKASI */
.alert-success {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 500;
}
.alert-error {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 500;
}
@media (max-width: 700px) {
    .order-details { grid-template-columns: 1fr; }
    .order-filter { flex-direction: column; }
}
</style>
</head>
<body>

<div class="admin-container">
    <h1>📦 Daftar Pesanan</h1>

    <!-- ✅ NOTIFIKASI SUKSES / ERROR -->
    <?php if (isset($success_msg)): ?>
        <div class="alert-success">
            ✅ <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="alert-error">
            ⚠️ <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <!-- Filter -->
    <form method="GET" class="order-filter">
        <select name="filter_status">
            <option value="">🔍 Semua Status</option>
            <?php foreach ($status_labels as $key => $label): ?>
                <option value="<?= htmlspecialchars($key) ?>" 
                    <?= $filter_status === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Terapkan</button>
    </form>

    <?php if (empty($orders)): ?>
        <div class="order-card" style="text-align:center;">
            <p>🚫 Tidak ada pesanan.</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            $status = $order['status'] ?? 'pending';
            $total = $order['total_amount'] ?? $order['total'] ?? 0;
            $payment_method = $order['payment_method'] ?? 'transfer';
            $shipping_method = $order['shipping_method'] ?? 'regular';
            $address = $order['address'] ?? '—';
        ?>
            <div class="order-card">
                <div class="order-header">
                    <strong>Order #<?= htmlspecialchars($order['id']) ?></strong>
                    <span class="order-status <?= htmlspecialchars($status) ?>">
                        <?= htmlspecialchars($status_labels[$status] ?? ucfirst($status)) ?>
                    </span>
                </div>

                <div class="order-details">
                    <div>
                        <p><strong>Pelanggan:</strong> <?= htmlspecialchars($order['user_name'] ?? '—') ?></p>
                        <p><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($order['created_at'] ?? 'now')) ?></p>
                        <p><strong>Total:</strong> Rp <?= number_format($total, 0, ',', '.') ?></p>
                        <p><strong>Alamat:</strong> <small><?= htmlspecialchars(substr($address, 0, 40)) ?>...</small></p>
                    </div>
                    <div>
                        <p><strong>Bayar:</strong> <?= ucfirst($payment_method) ?></p>
                        <p><strong>Pengiriman:</strong> <?= ucfirst($shipping_method) ?></p>
                    </div>
                </div>

                <!-- ACTION -->
                <div class="order-actions">
                    <form method="POST" action="update_order_status.php" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="new_status" onchange="this.form.submit()" style="padding:6px;">
                            <option value="">Ubah Status</option>
                            <?php foreach ($status_labels as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>" 
                                    <?= $status === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn-detail">Detail</a>
                    <a href="order_detail.php?id=<?= $order['id'] ?>&action=update" class="btn-update">Update</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="text-align:right; margin-top:20px;">
        <a href="dashboard.php" class="btn-detail" style="background:#3b82f6; display:inline-block;">
            ⬅ Kembali ke Dashboard
        </a>
    </div>
</div>

</body>
</html>