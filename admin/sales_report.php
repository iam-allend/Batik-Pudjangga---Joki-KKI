<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../config/db.php';

// Total pendapatan: hitung dari order_items
$stmt = $pdo->query("
    SELECT COALESCE(SUM(oi.price * oi.quantity), 0)
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'paid'
");
$total_revenue = $stmt->fetchColumn();

// Total order
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'");
$total_orders = $stmt->fetchColumn();

// Produk terlaris
$stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'paid'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 1
");
$best_seller = $stmt->fetch();
$best_seller_name = $best_seller ? $best_seller['name'] : '–';

// Order terbaru
$stmt = $pdo->query("
    SELECT o.id, u.name as customer, 
           (SELECT SUM(oi2.price * oi2.quantity) FROM order_items oi2 WHERE oi2.order_id = o.id) as total,
           o.created_at, o.status
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status = 'paid'
    ORDER BY o.created_at DESC
    LIMIT 10
");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Admin</title>
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
    <div class="content">
        <div class="welcome">
            <h1>Laporan Penjualan</h1>
            <p>Ringkasan penjualan toko kamu.</p>
        </div>

        <div class="report-summary">
            <div class="report-card">
                <h3>Total Order</h3>
                <div class="value"><?= $total_orders ?></div>
            </div>
            <div class="report-card">
                <h3>Pendapatan</h3>
                <div class="value">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
            </div>
            <div class="report-card">
                <h3>Produk Terlaris</h3>
                <div class="value"><?= htmlspecialchars($best_seller_name) ?></div>
            </div>
        </div>

        <div class="table-container" style="margin-top: 30px;">
            <h2 style="margin: 20px 0; color: #2c1e15;">Detail Order Terbaru</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Order</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td data-label="ID Order">#<?= $order['id'] ?></td>
                        <td data-label="Pelanggan"><?= htmlspecialchars($order['customer']) ?></td>
                        <td data-label="Total" class="price-col">Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                        <td data-label="Tanggal"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                        <td data-label="Status">
                            <span style="padding:4px 10px; border-radius:20px; font-size:0.85rem;
                                background: #d4edda; color: #155724;">
                                Lunas
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>