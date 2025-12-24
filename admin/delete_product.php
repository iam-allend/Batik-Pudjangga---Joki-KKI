<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../config/db.php';

// Cek apakah ada ID produk
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "ID produk tidak valid.";
    header("Location: products.php");
    exit;
}

$product_id = (int)$_GET['id'];

// Ambil nama produk untuk pesan
$stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['message'] = "Produk tidak ditemukan.";
    header("Location: products.php");
    exit;
}

try {
    // Hapus produk
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    $_SESSION['message'] = "'" . htmlspecialchars($product['name']) . "' berhasil dihapus.";
} catch (Exception $e) {
    $_SESSION['message'] = "Gagal menghapus produk. Mungkin sedang digunakan di pesanan.";
}

header("Location: products.php");
exit;
?>