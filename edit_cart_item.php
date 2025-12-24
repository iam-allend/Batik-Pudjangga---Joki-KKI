<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses tidak diizinkan.");
}

$cart_id = (int)($_POST['cart_id'] ?? 0);

// Ambil data item keranjang
$stmt = $pdo->prepare("
    SELECT c.quantity, c.size, c.notes, p.id as product_id, p.name, p.price
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.id = ? AND c.user_id = ?
");
$stmt->execute([$cart_id, $_SESSION['user_id']]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error'] = "Item keranjang tidak ditemukan.";
    header("Location: cart.php");
    exit;
}

// Proses update
if (isset($_POST['update'])) {
    $quantity = (int)($_POST['quantity'] ?? 1);
    $size = trim($_POST['size'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($quantity <= 0 || empty($size)) {
        $_SESSION['error'] = "Silakan isi jumlah dan ukuran dengan benar.";
    } else {
        $stmt = $pdo->prepare("UPDATE carts SET quantity = ?, size = ?, notes = ? WHERE id = ?");
        $stmt->execute([$quantity, $size, $notes, $cart_id]);
        $_SESSION['success'] = "Item keranjang berhasil diperbarui!";
        header("Location: cart.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Item Keranjang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>


<!-- FORM CONTAINER -->
<div class="edit-cart-container">

    <h1>Edit Item Keranjang</h1>
    <p>Sesuaikan jumlah, ukuran atau catatan sesuai kebutuhanmu.</p>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error" style="background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:10px;">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success" style="background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:10px;">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="cart_id" value="<?= $cart_id ?>">

        <label>Nama Produk</label>
        <input type="text" value="<?= htmlspecialchars($item['name']) ?>" disabled>

        <label>Harga</label>
        <input type="text" value="Rp <?= number_format($item['price'],0,',','.') ?>" disabled>

        <label>Jumlah</label>
        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" required>

        <label>Ukuran *</label>
        <select name="size" required>
            <option value="">Pilih Ukuran</option>
            <option value="S" <?= $item['size']=='S'?'selected':'' ?>>S</option>
            <option value="M" <?= $item['size']=='M'?'selected':'' ?>>M</option>
            <option value="L" <?= $item['size']=='L'?'selected':'' ?>>L</option>
            <option value="XL" <?= $item['size']=='XL'?'selected':'' ?>>XL</option>
            <option value="XXL" <?= $item['size']=='XXL'?'selected':'' ?>>XXL (+Rp 15.000)</option>
        </select>

        <label>Catatan (Opsional)</label>
        <textarea name="notes" rows="4" placeholder="Contoh: Lingkar dada 100cm">
<?= htmlspecialchars($item['notes']) ?></textarea>

        <div class="edit-cart-buttons">
            <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
            <a href="cart.php" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>

</body>
</html>

<?php include 'includes/footer.php'; ?>