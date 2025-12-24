<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_items'])) {
    $_SESSION['error'] = "Tidak ada item yang dipilih untuk checkout.";
    header("Location: cart.php");
    exit;
}

$checkout_items = $_SESSION['checkout_items'];
$subtotal = $_SESSION['checkout_total'];

// Ambil alamat default
$stmt = $pdo->prepare("SELECT * FROM address WHERE user_id = ? AND is_default = 1 LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$default_address = $stmt->fetch();

if (!$default_address) {
    $_SESSION['error'] = "Silakan tambahkan alamat terlebih dahulu.";
    header("Location: profile.php");
    exit;
}

// Ambil data daerah ongkir
$shipping_rows = $pdo->query("SELECT province, cost_regular, cost_express FROM shipping_zones")->fetchAll(PDO::FETCH_ASSOC);
$shippingData = [];
foreach ($shipping_rows as $row) {
    $shippingData[$row['province']] = [
        "cost_regular" => (int)$row['cost_regular'],
        "cost_express" => (int)$row['cost_express']
    ];
}
?>

<div class="checkout-header">
    <h1>Checkout</h1>
</div>

<div class="checkout-section">
    <div class="checkout-form">
        <h2>Alamat Pengiriman</h2>
        <div class="selected-address-card">
            <div class="address-info">
                <strong><?= $default_address['recipient_name'] ?></strong> | <?= $default_address['phone'] ?><br>
                <?= $default_address['address'] ?><br>
                <?= $default_address['city'] ?>, <?= $default_address['province'] ?> <?= $default_address['postal_code'] ?>
                <span class="badge-default">Utama</span>
            </div>
            <a href="my_address.php" class="btn-change-address">Ubah</a>
        </div>

        <form method="POST" action="process_order.php" id="checkout-form">
            <!-- Hidden -->
            <?php foreach ($default_address as $key => $val): ?>
                <?php if ($key != 'id' && $key != 'user_id' && $key != 'is_default'): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($val) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            
            <input type="hidden" name="subtotal" value="<?= $subtotal ?>">
            <input type="hidden" name="shipping_cost" id="shipping-cost-input" value="0">
            <input type="hidden" name="grand_total" id="grand-total-input" value="<?= $subtotal ?>">

            <h2>Metode Pengiriman</h2>
            <div class="form-group">
                <label>Jenis Pengiriman *</label>
                <select name="shipping_type" id="shipping-type" required>
                    <option value="regular">Reguler (3–7 hari)</option>
                    <option value="express">Express (1–3 hari)</option>
                </select>
            </div>

            <h2>Metode Pembayaran</h2>
            <div class="payment-methods">
                <label><input type="radio" name="payment_method" value="transfer" checked> Transfer Bank</label>
                <label><input type="radio" name="payment_method" value="cod"> COD (Bayar di Tempat)</label>
            </div>

            <button type="submit" class="btn-checkout" id="submit-btn">Buat Pesanan</button>
        </form>
    </div>

    <div class="order-summary">
        <h2>Ringkasan Pesanan</h2>
        <?php foreach ($checkout_items as $item): ?>
            <div class="order-item">
                <img src="assets/img/<?= htmlspecialchars($item['image']) ?>">
                <div>
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p><?= $item['quantity'] ?> × Rp <?= number_format($item['base_price'] + $item['extra_charge'], 0, ',', '.') ?></p>
                    <small>Ukuran: <?= htmlspecialchars($item['size']) ?></small>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="order-total">
            <p>Subtotal: Rp <?= number_format($subtotal, 0, ',', '.') ?></p>
            <p>Ongkos Kirim: <span id="shipping-cost-display">Hitung...</span></p>
            <p><strong>Total: <span id="grand-total-display">-</span></strong></p>
        </div>
    </div>
</div>

<script>
const shippingData = <?= json_encode($shippingData) ?>;
const subtotal = <?= $subtotal ?>;
const province = <?= json_encode($default_address['province']) ?>;

function updateCost() {
    const type = document.getElementById('shipping-type').value;
    const zone = shippingData[province] || { cost_regular: 0, cost_express: 0 };
    const cost = type === 'express' ? zone.cost_express : zone.cost_regular;
    const total = subtotal + cost;

    document.getElementById('shipping-cost-display').textContent = 'Rp ' + cost.toLocaleString('id-ID');
    document.getElementById('grand-total-display').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('shipping-cost-input').value = cost;
    document.getElementById('grand-total-input').value = total;
}
document.getElementById('shipping-type').addEventListener('change', updateCost);
updateCost();
</script>
