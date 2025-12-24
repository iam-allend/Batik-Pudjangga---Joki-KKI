<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<div class="orders-header">
    <h1>Riwayat Pesanan</h1>
</div>

<div class="orders-section">

    <!-- ✅ Notifikasi sukses -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" id="auto-hide-alert">
            ✅ <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="no-orders">
            <p>Belum ada pesanan.</p>
            <a href="shop.php" class="btn-back">Mulai Belanja</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            // Ambil total dengan fallback
            $total = $order['total_amount'] ?? $order['total'] ?? 0;
            $status = $order['status'] ?? 'pending';
            $address = $order['address'] ?? '—';
            $payment_method = $order['payment_method'] ?? 'transfer';
            $shipping_method = $order['shipping_method'] ?? 'regular';
        ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <h3>Pesanan #<?= htmlspecialchars($order['id']) ?></h3>
                        <p class="order-date"><?= date('d M Y H:i', strtotime($order['created_at'] ?? 'now')) ?></p>
                    </div>
                    <span class="order-status <?= htmlspecialchars($status) ?>">
                        <?php
                        $status_labels = [
                            'pending'      => 'Menunggu Pembayaran',
                            'processing'   => 'Dikemas',
                            'shipped'      => 'Dalam Pengiriman',
                            'delivered'    => 'Selesai',
                            'cancelled'    => 'Dibatalkan'
                        ];
                        echo htmlspecialchars($status_labels[$status] ?? ucfirst($status));
                        ?>
                    </span>
                </div>
                
                <div class="order-summary">
                    <p><strong><?= (int)($order['item_count'] ?? 0) ?> produk</strong> • 
                    Total: <strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></p>
                    <p><strong>Metode Bayar:</strong> 
                        <?= $payment_method === 'transfer' ? 'Transfer Bank' : 'Bayar di Tempat (COD)' ?>
                    </p>
                    <p><strong>Metode Kirim:</strong> 
                        <?= $shipping_method === 'express' ? 'Express' : 'Reguler' ?>
                    </p>
                </div>
                
                <div class="order-address">
                    <p><strong>Alamat:</strong> <?= htmlspecialchars($address) ?></p>
                </div>
                
                <div class="order-actions">
                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn-view">Lihat Detail</a>
                    <?php if ($status === 'pending' && $payment_method === 'transfer'): ?>
                        <a href="payment.php?order_id=<?= $order['id'] ?>" class="btn-checkout">Bayar Sekarang</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const alert = document.getElementById('auto-hide-alert');
    if (alert) setTimeout(() => {
        alert.style.transition = 'opacity 0.3s';
        alert.style.opacity = '0';
        setTimeout(() => alert.style.display = 'none', 300);
    }, 3000);
});
</script>

<style>
.alert-success {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.orders-header {
    text-align: center;
    padding: 60px 20px;
    background-color: #f5f0e6;
    margin-top: 80px;
}
.orders-header h1 {
    font-size: 2.2rem;
    color: #333;
    margin: 0;
}

.orders-section {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.no-orders {
    text-align: center;
    padding: 40px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.no-orders p {
    font-size: 1.2rem;
    color: #555;
    margin-bottom: 20px;
}

.order-card {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}
.order-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.4rem;
}
.order-date {
    color: #777;
    font-size: 0.95rem;
    margin: 5px 0 0;
}

.order-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}
.order-status.pending { background: #fff3cd; color: #856404; }
.order-status.processing { background: #cce5ff; color: #004085; }
.order-status.shipped { background: #d1ecf1; color: #0c5460; }
.order-status.delivered { background: #d4edda; color: #155724; }
.order-status.cancelled { background: #f8d7da; color: #721c24; }

.order-summary,
.order-address {
    background: #f9f7f3;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.95rem;
    line-height: 1.5;
}

.order-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.btn-view, .btn-checkout {
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    display: inline-block;
}
.btn-view {
    background: #333;
    color: white;
}
.btn-view:hover {
    background: #555;
}
.btn-checkout {
    background: #d4a373;
    color: white;
}
.btn-checkout:hover {
    background: #b58360;
}

@media (max-width: 768px) {
    .order-header { flex-direction: column; align-items: flex-start; }
    .order-actions { width: 100%; }
    .btn-view, .btn-checkout { width: 100%; text-align: center; }
}
</style>