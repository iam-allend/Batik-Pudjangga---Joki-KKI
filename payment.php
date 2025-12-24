<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data pesanan
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// Generate VA berdasarkan bank (simulasi)
$order_id_padded = str_pad($order['id'], 6, '0', STR_PAD_LEFT);
$va_numbers = [
    'bca' => '8881' . $order_id_padded,
    'jateng' => '8882' . $order_id_padded,
    'seabank' => '8883' . $order_id_padded
];

// Format jatuh tempo (15 menit dari sekarang untuk timer)
$expiry_time = strtotime($order['created_at']) + (15 * 60);
$expiry_formatted = date('Y-m-d\TH:i:s', $expiry_time);
?>

<div class="payment-header">
    <h1>Detail Pembayaran</h1>
</div>

<div class="payment-section">
    <div class="payment-card">
        <h2>Transfer Bank</h2>
        <div class="payment-detail">
            <p><strong>Total Pembayaran:</strong></p>
            <p class="payment-amount">Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
            
            <p><strong>Batas Waktu Bayar:</strong></p>
            <p id="timer" class="payment-timer">05:00</p>
            
            <!-- Pilih Bank -->
            <div class="form-group">
                <label>Pilih Bank Tujuan:</label>
                <select id="bank-select" class="payment-select" onchange="updateVA()">
                    <option value="">Pilih Bank</option>
                    <option value="bca">BCA</option>
                    <option value="jateng">Bank Jateng</option>
                    <option value="seabank">SeaBank</option>
                </select>
            </div>
            
            <!-- Nomor VA -->
            <div id="va-display" class="va-box" style="display:none;">
                <p><strong>Nomor Virtual Account:</strong></p>
                <p id="va-number" class="va-number"></p>
                <button onclick="copyVA()" class="btn-copy">Salin Nomor</button>
            </div>
        </div>
    </div>

    <div class="payment-actions" style="margin-top: 30px; text-align: center;">
        <a href="payment-success.php?id=<?= $order['id'] ?>" class="btn-checkout">Saya Sudah Bayar</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- CSS Tambahan -->
<style>
.payment-section {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
.payment-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}
.payment-card h2 {
    color: #2c1e15;
    margin-bottom: 20px;
    font-size: 1.4rem;
}
.payment-amount {
    font-size: 1.8rem;
    font-weight: 700;
    color: #d4a373;
    margin: 10px 0;
}
.payment-timer {
    font-size: 1.3rem;
    font-weight: 700;
    color: #ff6b6b;
    margin: 10px 0;
}
.payment-select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    margin: 10px 0;
}
.va-box {
    margin-top: 20px;
    padding: 20px;
    background: #f9f7f3;
    border-radius: 10px;
    border-left: 4px solid #d4a373;
}
.va-number {
    font-size: 1.4rem;
    font-weight: 700;
    color: #2c1e15;
    word-break: break-all;
}
.btn-copy {
    margin-top: 10px;
    padding: 8px 16px;
    background: #d4a373;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}
.btn-copy:hover {
    background: #b58360;
}
.qris-image {
    max-width: 200px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.qris-text {
    margin: 15px 0 20px;
    color: #555;
}
.btn-download {
    display: inline-block;
    padding: 10px 20px;
    background: #2c1e15;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
}
.btn-download:hover {
    background: #555;
}
</style>

<script>
const vaNumbers = <?= json_encode($va_numbers) ?>;
const expiryTime = new Date('<?= $expiry_formatted ?>').getTime();

function updateVA() {
    const bank = document.getElementById('bank-select').value;
    const vaDisplay = document.getElementById('va-display');
    const vaNumber = document.getElementById('va-number');
    
    if (bank && vaNumbers[bank]) {
        vaNumber.textContent = vaNumbers[bank];
        vaDisplay.style.display = 'block';
    } else {
        vaDisplay.style.display = 'none';
    }
}

function copyVA() {
    const vaText = document.getElementById('va-number').textContent;
    navigator.clipboard.writeText(vaText).then(() => {
        alert('Nomor VA disalin ke clipboard!');
    });
}

// Timer Countdown
function startTimer() {
    const now = new Date().getTime();
    const distance = expiryTime - now;
    
    if (distance < 0) {
        document.getElementById('timer').textContent = 'Waktu Habis';
        return;
    }
    
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('timer').textContent = 
        minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    
    setTimeout(startTimer, 1000);
}

// Mulai timer
startTimer();
</script>