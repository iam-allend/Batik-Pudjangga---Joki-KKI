<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login.";
    header("Location: login.php");
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) {
    $_SESSION['error'] = "Pesanan tidak ditemukan.";
    header("Location: orders.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Pesanan tidak ditemukan.";
    header("Location: orders.php");
    exit;
}

$item_stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$item_stmt->execute([$order_id]);
$items = $item_stmt->fetchAll();

// ✅ Safe status handling
$status = trim($order['status'] ?? '');
if (empty($status)) $status = 'pending';

$status_labels = [
    'pending'      => 'Menunggu Pembayaran',
    'processing'   => 'Dikemas',
    'shipped'      => 'Dalam Pengiriman',
    'delivered'    => 'Selesai',
    'cancelled'    => 'Dibatalkan'
];
$display_status = $status_labels[$status] ?? ucfirst($status);

$total = $order['total_amount'] ?? $order['total'] ?? 0;
?>

<div class="order-detail-header">
    <h1>Detail Pesanan #<?= $order_id ?></h1>
</div>

<div class="order-detail-section">
    <!-- STATUS -->
    <div class="status-box">
        <h3>Status Pesanan</h3>
        <span class="order-status <?= htmlspecialchars($status) ?>">
            <?= htmlspecialchars($display_status) ?>
        </span>
    </div>

    <!-- DATA PELANGGAN & ALAMAT -->
    <div class="order-info">
        <h3>Informasi Pengiriman</h3>
        <p><strong>Atas Nama:</strong> <?= htmlspecialchars($order['recipient_name'] ?? '—') ?></p>
        <p><strong>Telepon:</strong> <?= htmlspecialchars($order['phone'] ?? '—') ?></p>
        <p><strong>Alamat:</strong> <br><?= nl2br(htmlspecialchars($order['address'] ?? '—')) ?></p>
        <p><?= htmlspecialchars($order['city'] ?? '') ?>, <?= htmlspecialchars($order['province'] ?? '') ?> <?= htmlspecialchars($order['postal_code'] ?? '') ?></p>
    </div>

    <!-- ITEM -->
    <div class="order-items">
        <h3>Produk Dipesan (<?= count($items) ?> item)</h3>
        <?php foreach ($items as $item): 
            $final_price = $item['price'];
            $size = $item['size'] ?? '—';
            $notes = $item['notes'] ?? '';
        ?>
            <div class="order-item-card">
                <img src="assets/img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <div class="item-desc">
                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                    <p>Ukuran: <?= htmlspecialchars($size) ?></p>
                    <?php if ($notes): ?>
                        <p><small>Catatan: <?= htmlspecialchars($notes) ?></small></p>
                    <?php endif; ?>
                    <p>Harga: Rp <?= number_format($final_price, 0, ',', '.') ?> × <?= $item['quantity'] ?></p>
                </div>
                <div class="item-total">
                    Rp <?= number_format($final_price * $item['quantity'], 0, ',', '.') ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- RINGKASAN -->
    <div class="order-summary-box">
        <h3>Rincian Pembayaran</h3>
        <p>Subtotal: <span>Rp <?= number_format($order['subtotal'] ?? 0, 0, ',', '.') ?></span></p>
        <p>Ongkos Kirim: <span>Rp <?= number_format($order['shipping_cost'] ?? 0, 0, ',', '.') ?></span></p>
        <hr>
        <p class="total">Total: <strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></p>
        <p><strong>Metode Bayar:</strong> <?= $order['payment_method'] === 'transfer' ? 'Transfer Bank' : 'Bayar di Tempat (COD)' ?></p>
        <p><strong>Metode Kirim:</strong> <?= $order['shipping_method'] === 'express' ? 'Express' : 'Reguler' ?></p>
    </div>

    <!-- BUTTON KEMBALI — DI BAWAH SEMUA -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="orders.php" class="btn-back">← Kembali ke Riwayat Pesanan</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.order-detail-header {
    text-align: center;
    padding: 40px 20px;
    background: #f5f0e6;
    margin-top: 80px;
}
.order-detail-header h1 {
    font-size: 2rem;
    margin: 0 0 15px;
}
.order-detail-header .btn-back {
    display: inline-block;
    margin-top: 10px;
}
.btn-back {
    display: inline-block;
    background: #6d6f75;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 1rem;
}
.order-detail-section {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 20px;
}
.status-box, .order-info, .order-items, .order-summary-box {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.status-box h3,
.order-info h3,
.order-items h3,
.order-summary-box h3 {
    margin-top: 0;
    color: #333;
}
.order-status {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 1rem;
}
.order-status.pending { background: #fff3cd; color: #856404; }
.order-status.processing { background: #cce5ff; color: #004085; }
.order-status.shipped { background: #d1ecf1; color: #0c5460; }
.order-status.delivered { background: #d4edda; color: #155724; }
.order-status.cancelled { background: #f8d7da; color: #721c24; }

.order-item-card {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}
.order-item-card:last-child {
    border-bottom: none;
}
.order-item-card img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
.item-desc h4 {
    margin: 0 0 5px;
    font-size: 1.1rem;
}
.item-desc p {
    margin: 4px 0;
    font-size: 0.95rem;
    color: #555;
}
.item-total {
    margin-left: auto;
    font-weight: 600;
    font-size: 1.1rem;
    color: #333;
    align-self: center;
}
.order-summary-box p {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
}
.order-summary-box hr {
    border: 0;
    border-top: 1px dashed #ccc;
    margin: 15px 0;
}
.order-summary-box .total {
    font-size: 1.4rem;
    font-weight: 700;
    color: #c79a63;
    text-align: center;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .order-item-card {
        flex-direction: column;
    }
    .item-total {
        margin-left: 0;
        text-align: right;
        margin-top: 10px;
    }
}
</style>