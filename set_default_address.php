<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User tidak login']);
    exit;
}

$addressId = $_GET['id'] ?? null;

if (!$addressId) {
    echo json_encode(['success' => false, 'message' => 'ID alamat tidak valid']);
    exit;
}

try {
    // Nonaktifkan semua alamat default
    $pdo->prepare("UPDATE address SET is_default = 0 WHERE user_id = ?")->execute([$_SESSION['user_id']]);

    // Aktifkan alamat yang dipilih
    $stmt = $pdo->prepare("UPDATE address SET is_default = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>