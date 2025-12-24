<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$quantity   = (int)($_POST['quantity'] ?? 1);
$size       = trim($_POST['size'] ?? '');
$notes      = trim($_POST['notes'] ?? '');
$wishlist_id = $_POST['from_wishlist'] ?? null;

if ($product_id <= 0 || $size === '') {
    $_SESSION['error'] = "Pilih ukuran terlebih dahulu.";
    header("Location: wishlist.php");
    exit;
}

// ambil harga
$stmt = $pdo->prepare("SELECT price, name FROM products WHERE id=?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

$final_price = $product['price'];
if ($size == "XXL") $final_price += 15000;

// cek cart existing (user + product + size)
$stmt = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id=? AND product_id=? AND size=?");
$stmt->execute([$_SESSION['user_id'], $product_id, $size]);
$existing = $stmt->fetch();

if ($existing) {

    // update qty
    $new_qty = $existing['quantity'] + $quantity;
    $stmt = $pdo->prepare("UPDATE carts SET quantity=?, price=? WHERE id=?");
    $stmt->execute([$new_qty, $final_price, $existing['id']]);

} else {

    // insert baru
    $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity, size, notes, price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity, $size, $notes, $final_price]);
}

// kalau dari wishlist → hapus otomatis
if ($wishlist_id) {
    $remove = $pdo->prepare("DELETE FROM wishlists WHERE id=? AND user_id=?");
    $remove->execute([$wishlist_id, $_SESSION['user_id']]);
}

$_SESSION['success'] = "'" . htmlspecialchars($product['name']) . "' masuk ke keranjang 🛒";
header("Location: cart.php");
exit;
?>
