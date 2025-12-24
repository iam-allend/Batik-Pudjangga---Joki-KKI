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
    $stmt = $pdo->prepare("DELETE FROM address WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>