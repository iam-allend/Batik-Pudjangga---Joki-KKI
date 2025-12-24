<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $pdo->prepare("SELECT name, email, phone, address, city, postal_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Ambil keranjang
$stmt = $pdo->prepare("
    SELECT c.quantity, p.id as product_id, p.name, p.price, p.image
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    die("Keranjang kosong.");
}

// Hitung subtotal
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));
?>

<!-- Checkout Header -->
<div class="checkout-header">
    <h1>Checkout</h1>
</div>

<!-- Checkout Content -->
<section class="checkout-section">
    <div class="checkout-container">
        <!-- Form Checkout -->
        <form method="POST" action="process_order.php" id="checkout-form">
            <input type="hidden" name="subtotal" value="<?= $subtotal ?>">
            <input type="hidden" name="shipping_cost" id="shipping-cost-input" value="0">
            <input type="hidden" name="grand_total" id="grand-total-input" value="<?= $subtotal ?>">

            <!-- Alamat Pengiriman -->
            <div class="checkout-shipping">
                <h2>Alamat Pengiriman</h2>
                <div class="shipping-info">
                    <div class="info-row">
                        <span class="info-label">Nama Penerima:</span>
                        <span class="info-value"><?= htmlspecialchars($user['name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Telepon:</span>
                        <span class="info-value"><?= htmlspecialchars($user['phone'] ?? '-') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alamat:</span>
                        <span class="info-value"><?= htmlspecialchars($user['address'] ?? '-') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Kota:</span>
                        <span class="info-value"><?= htmlspecialchars($user['city'] ?? '-') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Kode Pos:</span>
                        <span class="info-value"><?= htmlspecialchars($user['postal_code'] ?? '-') ?></span>
                    </div>
                </div>

                <!-- Edit Address Button -->
                <div style="margin-top: 20px;">
                    <a href="edit_profile.php" class="btn-edit-address">Edit Alamat</a>
                </div>
            </div>

            <!-- Metode Pengiriman -->
            <div class="checkout-shipping-method">
                <h2>Metode Pengiriman</h2>
                <div class="form-group">
                    <label>Provinsi Tujuan *</label>
                    <select name="province" id="province-select" required onchange="updateShippingCost()">
                        <option value="">Pilih Provinsi</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, province FROM shipping_zones ORDER BY province");
                        while ($row = $stmt->fetch()) {
                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['province']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jenis Pengiriman *</label>
                    <select name="shipping_type" id="shipping-type" required onchange="updateShippingCost()">
                        <option value="regular">Reguler (3-7 hari)</option>
                        <option value="express">Express (1-3 hari)</option>
                    </select>
                </div>
            </div>

            <!-- Metode Pembayaran — DESAIN BARU MIRIP SHOPEE -->
            <div class="checkout-payment">
                <h2>Metode Pembayaran</h2>
                <div class="payment-methods-new">
                    <label>
                        <input type="radio" name="payment_method" value="transfer" checked>
                        Transfer Bank
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="cod">
                        Bayar di Tempat (COD)
                    </label>
                </div>
                <button type="submit" class="btn-create-order">Buat Pesanan</button>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h2>Ringkasan Pesanan</h2>
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <img src="assets/img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div>
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p><?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="order-total">
                    <p>Subtotal: Rp <?= number_format($subtotal, 0, ',', '.') ?></p>
                    <p>Ongkos Kirim: Rp <span id="shipping-cost-display">0</span></p>
                    <p><strong>Total: Rp <span id="grand-total-display"><?= number_format($subtotal, 0, ',', '.') ?></strong></p>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function updateShippingCost() {
    const provinceId = document.getElementById('province-select').value;
    const shippingType = document.getElementById('shipping-type').value;
    
    if (!provinceId || !shippingType) {
        document.getElementById('shipping-cost-input').value = '0';
        document.getElementById('shipping-cost-display').textContent = '0';
        document.getElementById('grand-total-input').value = '<?= $subtotal ?>';
        document.getElementById('grand-total-display').textContent = '<?= number_format($subtotal, 0, ',', '.') ?>';
        return;
    }
    
    // Ambil biaya pengiriman dari server (via AJAX)
    fetch(`get_shipping_cost.php?province_id=${provinceId}&type=${shippingType}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cost = data.cost;
                document.getElementById('shipping-cost-input').value = cost;
                document.getElementById('shipping-cost-display').textContent = cost.toLocaleString('id-ID');
                const grandTotal = <?= $subtotal ?> + cost;
                document.getElementById('grand-total-input').value = grandTotal;
                document.getElementById('grand-total-display').textContent = grandTotal.toLocaleString('id-ID');
            } else {
                alert('Gagal menghitung ongkos kirim.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
}
</script>

<style>
/* CHECKOUT HEADER */
.checkout-header {
    text-align: center;
    padding: 60px 20px;
    background-color: #f5f0e6;
    margin-top: 80px;
}
.checkout-header h1 {
    font-size: 2.2rem;
    color: #333;
    margin: 0;
}

/* CHECKOUT SECTION */
.checkout-section {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

/* CHECKOUT CONTAINER */
.checkout-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

/* SHIPPING ADDRESS */
.checkout-shipping {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.checkout-shipping h2 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 20px;
}

.shipping-info {
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.info-label {
    font-weight: 600;
    color: #333;
    margin-right: 10px;
    width: 120px;
}

.info-value {
    color: #555;
    font-size: 1.1rem;
}

.btn-edit-address {
    display: inline-block;
    padding: 8px 16px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: background 0.3s;
}
.btn-edit-address:hover {
    background: #5a6268;
}

/* SHIPPING METHOD */
.checkout-shipping-method {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-top: 20px;
}

.checkout-shipping-method h2 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 500;
    color: #333;
}
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}

/* PAYMENT METHOD — DESAIN BARU MIRIP SHOPEE */
.checkout-payment {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-top: 20px;
}

.checkout-payment h2 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 20px;
}

.payment-methods-new {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.payment-methods-new label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 1rem;
}

.payment-methods-new input[type="radio"] {
    width: 16px;
    height: 16px;
    margin-right: 8px;
    accent-color: #d4a373;
}

.btn-create-order {
    background: #c97b42;
    color: white;
    border: none;
    padding: 14px 28px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 17px;
    transition: all 0.3s ease;
    margin-top: 25px;
    width: 100%;
    max-width: 220px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-create-order:hover {
    background: #b56e38;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

/* ORDER SUMMARY */
.order-summary {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-top: 20px;
}

.order-summary h2 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 20px;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding: 15px;
    background: #f9f7f3;
    border-radius: 8px;
}
.order-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}
.order-item div h3 {
    margin: 0 0 5px;
    color: #333;
    font-size: 1rem;
}
.order-item div p {
    margin: 0;
    color: #555;
    font-size: 0.95rem;
}

.order-total {
    margin-top: 20px;
    padding: 15px;
    background: #f9f7f3;
    border-radius: 8px;
}
.order-total p {
    margin: 5px 0;
    color: #333;
    font-size: 1rem;
}
.order-total p strong {
    font-size: 1.1rem;
}

/* RESPONSIVE */
@media (max-width: 992px) {
    .checkout-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .checkout-header {
        padding: 40px 20px;
    }
    .checkout-section {
        padding: 20px;
    }
    .checkout-container {
        gap: 20px;
    }
    .order-item {
        flex-direction: column;
        align-items: flex-start;
    }
    .order-item img {
        width: 80px;
        height: 80px;
    }
}
</style>