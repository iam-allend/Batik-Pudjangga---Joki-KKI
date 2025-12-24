<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data cart
$stmt = $pdo->prepare("
    SELECT c.id as cart_id, c.quantity, c.size, c.notes, p.id as product_id, p.name, p.price, p.image
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();
?>

<div class="cart-header">
    <h1>Keranjang Belanja</h1>
</div>

<section class="cart-section">
    <!-- ✅ NOTIFIKASI SUKSES / ERROR -->
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

    <?php if (empty($cart_items)): ?>
        <p style="text-align: center; font-size: 1.2rem;">Keranjangmu kosong.</p>
        <div style="text-align: center; margin-top: 20px;">
            <a href="shop.php" class="btn-back">Lanjut Belanja</a>
        </div>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cart_items as $item): 
                // Hitung harga final (termasuk +15.000 untuk XXL)
                $base_price = $item['price'];
                $extra = !empty($item['size']) && strtoupper($item['size']) === 'XXL' ? 15000 : 0;
                $final_price = $base_price + $extra;
                $subtotal = $final_price * $item['quantity'];
            ?>
                <div class="cart-item">
                    <!-- ✅ Kotak kecil di samping kiri (checkbox) -->
                    <div class="item-select">
                        <input type="checkbox" name="selected_items[]" 
                               value="<?= $item['cart_id'] ?>" 
                               data-subtotal="<?= $subtotal ?>"
                               title="Pilih untuk checkout">
                    </div>

                    <img src="assets/img/<?= htmlspecialchars($item['image']) ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>"
                         class="cart-item-img">

                    <div class="item-info">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p>
                            Rp <?= number_format($base_price, 0, ',', '.') ?>
                            <?php if ($extra > 0): ?>
                                <span class="extra-charge">(+Rp <?= number_format(15000, 0, ',', '.') ?>)</span>
                            <?php endif; ?>
                            × <?= $item['quantity'] ?>
                        </p>
                        <?php if (!empty($item['size'])): ?>
                            <p><strong>Ukuran:</strong> <?= htmlspecialchars($item['size']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['notes'])): ?>
                            <p><strong>Catatan:</strong> <?= htmlspecialchars($item['notes']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="item-actions">
                        <form method="POST" action="edit_cart_item.php" style="display:inline;">
                            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                            <button type="submit" class="btn-edit">Edit</button>
                        </form>
                        <form method="POST" action="remove_from_cart.php" style="display:inline;">
                            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                            <button type="submit" class="btn-remove">Hapus</button>
                        </form>
                        <div class="item-subtotal">
                            <strong>Subtotal:</strong> Rp <?= number_format($subtotal, 0, ',', '.') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ✅ Form untuk checkout item terpilih -->
        <form method="POST" action="checkout_selected.php" id="checkout-form">
            <div class="cart-total">
                <h3>Total: <span id="dynamic-total">Rp 0</span></h3>
                <button type="submit" class="btn-checkout" id="checkout-btn" disabled>Checkout</button>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>

<!-- ✅ JavaScript untuk update total real-time -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
    const totalDisplay = document.getElementById('dynamic-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    const form = document.getElementById('checkout-form');

    function updateTotal() {
        let total = 0;
        const selectedIds = [];

        checkboxes.forEach(cb => {
            if (cb.checked) {
                total += parseInt(cb.dataset.subtotal) || 0;
                selectedIds.push(cb.value);
            }
        });

        totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
        checkoutBtn.disabled = total === 0;

        // Simpan daftar cart_id yang dipilih ke hidden input
        let hiddenInput = document.querySelector('input[name="selected_items_json"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_items_json';
            form.appendChild(hiddenInput);
        }
        hiddenInput.value = JSON.stringify(selectedIds);
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
    updateTotal(); // inisialisasi
});
</script>

<style>
/* === ALERTS === */
.alert-success {
    background: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
}
.alert-error {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
}

/* === ITEM SELECT (checkbox kecil di kiri) === */
.item-select {
    display: flex;
    align-items: center;
    justify-content: center;
}
.item-select input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #c79a63;
    cursor: pointer;
}

/* === IMAGE === */
.cart-item-img {
    width: 110px;
    height: 110px;
    object-fit: cover;
    border-radius: 12px;
}

/* === EXTRA CHARGE LABEL === */
.extra-charge {
    color: #c8333b;
    font-weight: 600;
    font-size: 0.9em;
}

/* === ITEM SUBTOTAL (di bawah tombol) === */
.item-subtotal {
    margin-top: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #2b2b2b;
    text-align: right;
}

/* === GRID LAYOUT === */
.cart-item {
    display: grid;
    grid-template-columns: 40px 100px 1fr 150px;
    align-items: flex-start;
    gap: 20px;
    background: white;
    border-radius: 14px;
    padding: 18px;
    border: 1px solid #eaeaea;
    transition: .3s ease;
}

.cart-item:hover {
    border-color: #c9b29a;
    box-shadow: 0 6px 20px rgba(0,0,0,0.07);
}

.item-info h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: #2b2b2b;
}
.item-info p {
    margin: 6px 0;
    font-size: .95rem;
    color: #4d4d4d;
}
.item-info strong {
    color: #2b2b2b;
}

.item-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-end;
}

.btn-edit,
.btn-remove {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    font-size: .88rem;
    cursor: pointer;
    font-weight: 500;
    transition: .3s;
}

.btn-edit {
    background: #6d6f75;
    color: #fff;
}
.btn-edit:hover {
    background: #56575b;
}

.btn-remove {
    background: #c8333b;
    color: white;
}
.btn-remove:hover {
    background: #a6282e;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 35px;
    background: #fff6ee;
    padding: 20px 25px;
    border-radius: 14px;
    border: 1px solid #ead9c7;
}

.cart-total h3 {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0;
}

.btn-checkout {
    background: #c79a63;
    padding: 12px 22px;
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 600;
    transition: .3s;
    border: none;
    cursor: pointer;
}
.btn-checkout:hover {
    background: #a97e4f;
}

@media (max-width: 768px) {
    .cart-item {
        grid-template-columns: 1fr;
        padding: 15px;
    }
    .item-select {
        display: none;
    }
    .cart-item > img {
        width: 90px;
        height: 90px;
    }
    .item-actions {
        grid-column: unset;
        justify-content: flex-end;
        flex-direction: row;
    }
    .item-subtotal {
        text-align: left;
        margin-top: 10px;
    }
    .cart-total {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>