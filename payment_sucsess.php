<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = $_GET['id'] ?? '';
if (!$order_id || !is_numeric($order_id)) {
    die("ID pesanan tidak valid.");
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}
?>

<div class="success-header">
    <h1>Pembayaran Berhasil!</h1>
</div>

<div class="success-section">
    <div class="success-card">
        <div style="text-align: center; padding: 40px 20px;">
            <!-- Icon Sukses -->
            <div style="width: 80px; height: 80px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px;">
                <span style="font-size: 40px; color: #155724;">✓</span>
            </div>
            
            <!-- Judul & Pesan -->
            <h2>Pesanan #<?= $order['id'] ?> Berhasil!</h2>
            <p style="color: #555; margin: 15px 0; line-height: 1.6;">
                Terima kasih telah berbelanja di <strong>Pudjangga Batik</strong>!<br>
                Kami akan segera memproses pesanan Anda.
            </p>
            
            <!-- Detail Pesanan -->
            <div style="background: #f9f7f3; padding: 20px; border-radius: 10px; margin: 25px 0; text-align: left;">
                <h3 style="color: #2c1e15; margin-bottom: 15px;">Detail Pesanan</h3>
                <p><strong>Total:</strong> Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></p>
                <p><strong>Metode Bayar:</strong> 
                    <?php 
                    if ($order['payment_method'] == 'transfer') {
                        echo 'Transfer Bank';
                    } else {
                        echo 'Bayar di Tempat (COD)';
                    }
                    ?>
                </p>
                <p><strong>Status:</strong> 
                    <span style="color: #155724; font-weight: 600;">Menunggu Konfirmasi</span>
                </p>
            </div>
            
            <!-- Tombol Aksi -->
            <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-top: 20px;">
                <a href="orders.php" class="btn-checkout">Lihat Riwayat Pesanan</a>
                <a href="shop.php" class="btn-back" style="background: #6c757d;">Lanjut Belanja</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- CSS Tambahan -->
<style>
.success-header {
    text-align: center;
    padding: 60px 20px;
    background-color: #f5f0e6;
    margin-top: 80px;
}
.success-header h1 {
    font-size: 2.2rem;
    color: #333;
    margin: 0;
}
.success-section {
    padding: 30px 20px;
    max-width: 800px;
    margin: 0 auto;
}
.success-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
.btn-back {
    display: inline-block;
    padding: 12px 25px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.3s;
}
.btn-back:hover {
    background: #5a6268;
}
</style>