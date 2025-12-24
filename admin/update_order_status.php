<?php
session_start();

// ✅ Pastikan session aktif
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    // Jika tidak login, redirect ke login
    header("Location: register.php");
    exit;
}

include '../config/db.php';

// Ambil data POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Akses tidak diizinkan.";
    header("Location: orders.php");
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);
$new_status = $_POST['new_status'] ?? '';

// Validasi
$allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if ($order_id <= 0 || !in_array($new_status, $allowed)) {
    $_SESSION['error'] = "Data tidak valid.";
    header("Location: orders.php");
    exit;
}

try {
    // ✅ Update status + updated_at
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $result = $stmt->execute([$new_status, $order_id]);

    if ($result && $stmt->rowCount() > 0) {
        $_SESSION['success'] = "Status pesanan #{$order_id} berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Tidak ada perubahan. Status mungkin sudah sama.";
    }

} catch (Exception $e) {
    // ⚠️ Log error untuk debug
    error_log("Update order error: " . $e->getMessage());
    $_SESSION['error'] = "Gagal memperbarui status. Silakan coba lagi.";
}

// ✅ Redirect ke daftar pesanan (bukan detail)
header("Location: orders.php");
exit;
?>