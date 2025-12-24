<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: register.php");
    exit;
}

include '../config/db.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Order ID tidak ditemukan.";
    header("Location: orders.php");
    exit;
}

$order_id = (int)$_GET['id'];

// Ambil order + user
$stmt = $pdo->prepare("
    SELECT o.*, u.name AS customer_name, u.email, u.phone
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Pesanan tidak ditemukan.";
    header("Location: orders.php");
    exit;
}

// Ambil item
$item_stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$item_stmt->execute([$order_id]);
$items = $item_stmt->fetchAll();

$status_list = [
    'pending'      => 'Menunggu Pembayaran',
    'processing'   => 'Dikemas',
    'shipped'      => 'Dalam Pengiriman',
    'delivered'    => 'Selesai',
    'cancelled'    => 'Dibatalkan'
];

$action = $_GET['action'] ?? '';

// ✅ Generate kode resi: BTK-001-20251130
$order_number = str_pad($order_id, 3, "0", STR_PAD_LEFT); // misal: #3 → "003"
$date_part = date("Ymd"); // contoh: 20251130
$resi_code = "BTK-{$order_number}-{$date_part}";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Detail Pesanan #<?= $order_id ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; color: #333; }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-weight: 600; display: inline-block; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .info-box { background: #f9f9f9; padding: 15px; border-radius: 8px; }
        h3 { margin-top: 0; color: #444; }
        .item-list { margin: 20px 0; }
        .item-row { display: flex; gap: 15px; padding: 12px 0; border-bottom: 1px solid #eee; }
        .item-row img { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; }
        .btn-update { background: #10b981; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-back { background: #6b7280; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; }
        .btn-print { background: #6b7280; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; }
        .btn-update:hover { background: #059669; }
        .btn-back:hover { background: #4b5563; }
        .btn-print:hover { background: #4b5563; }
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
        .resi-box {
            background: #fff;
            padding: 20px;
            border: 2px dashed #3b82f6;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-size: 16px;
        }
        .resi-code {
            font-size: 1.4rem;
            font-weight: bold;
            color: #3b82f6;
            margin: 12px 0;
            letter-spacing: 1px;
        }
        .print-btn {
            margin-top: 20px;
        }

        /* ✅ PRINT STYLE — Hanya cetak bagian resi */
        @media print {
            body * {
                visibility: hidden;
            }
            .resi-box, .resi-box * {
                visibility: visible;
            }
            .resi-box {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 30px;
                box-sizing: border-box;
                font-size: 14pt;
                border: none;
            }
            .print-btn {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert-success">
            ✅ <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-error">
            ⚠️ <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <h1>📦 Detail Pesanan #<?= $order_id ?></h1>
    
    <h2>Status: <span class="status-badge status-<?= $order['status'] ?>"><?= $status_list[$order['status']] ?? ucfirst($order['status']) ?></span></h2>

    <div class="info-grid">
        <div class="info-box">
            <h3>👤 Pelanggan</h3>
            <p><strong>Nama:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>Telepon:</strong> <?= htmlspecialchars($order['phone']) ?></p>
        </div>
        <div class="info-box">
            <h3>📍 Alamat Pengiriman</h3>
            <p><strong>Atas Nama:</strong> <?= htmlspecialchars($order['recipient_name']) ?></p>
            <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>
            <p><?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['province']) ?> <?= htmlspecialchars($order['postal_code']) ?></p>
        </div>
    </div>

    <h3>🛒 Produk Dipesan (<?= count($items) ?> item)</h3>
    <div class="item-list">
        <?php foreach ($items as $item): ?>
            <div class="item-row">
                <img src="../assets/img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <div>
                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                    <p>Ukuran: <?= htmlspecialchars($item['size'] ?? '—') ?></p>
                    <p>Catatan: <?= htmlspecialchars($item['notes'] ?? '—') ?></p>
                    <p><?= $item['quantity'] ?> × Rp <?= number_format($item['price'], 0, ',', '.') ?> = 
                       <strong>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></strong></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="info-box">
        <h3>💰 Rincian Pembayaran</h3>
        <p><strong>Subtotal:</strong> Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></p>
        <p><strong>Ongkos Kirim:</strong> Rp <?= number_format($order['shipping_cost'], 0, ',', '.') ?></p>
        <p><strong>Total:</strong> <strong>Rp <?= number_format($order['total_amount'] ?? $order['total'] ?? 0, 0, ',', '.') ?></strong></p>
        <p><strong>Metode Bayar:</strong> <?= ucfirst($order['payment_method']) ?></p>
        <p><strong>Metode Kirim:</strong> <?= ucfirst($order['shipping_method']) ?></p>
        <p><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($order['created_at'])) ?></p>
    </div>

    <?php if ($action === 'update'): ?>
        <h3>⚙️ Update Status</h3>
        <form method="POST" action="update_order_status.php">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <select name="new_status" style="padding:8px; border-radius:6px; margin-right:10px;">
                <?php foreach ($status_list as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-update">Update Status</button>
        </form>
    <?php else: ?>
    <?php endif; ?>

    <!-- ✅ RESI BOX -->
    <div class="resi-box">
        <h3>🖨️ Cetak Resi Pengiriman</h3>
        <p><strong>Kode Resi:</strong></p>
        <div class="resi-code"><?= htmlspecialchars($resi_code) ?></div>
        <p><strong>Nama Penerima:</strong><br><?= htmlspecialchars($order['recipient_name']) ?></p>
        <p><strong>Alamat Lengkap:</strong><br><?= nl2br(htmlspecialchars($order['address'])) ?></p>
        <p><strong>Kota & Kode Pos:</strong><br><?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['province']) ?><br>Kode Pos: <?= htmlspecialchars($order['postal_code']) ?></p>
        <div class="print-btn">
            <button onclick="window.print()" class="btn-print">🖨️ Cetak Resi</button>
        </div>
    </div>

    <!-- ✅ BUTTON KEMBALI DI KANAN BAWAH -->
    <div style="text-align: right; margin-top: 40px;">
        <a href="orders.php" class="btn-back">← Kembali ke Daftar</a>
    </div>

</div>

</body>
</html>