<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User tidak login']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$required = ['recipient_name', 'address', 'city', 'province', 'postal_code', 'phone'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => 'Field ' . $field . ' wajib diisi']);
        exit;
    }
}

try {
    // Nonaktifkan alamat default sebelumnya
    $pdo->prepare("UPDATE address SET is_default = 0 WHERE user_id = ?")->execute([$_SESSION['user_id']]);

    // Simpan alamat baru sebagai default
    $stmt = $pdo->prepare("
        INSERT INTO address (user_id, recipient_name, address, city, province, postal_code, phone, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");

    $result = $stmt->execute([
        $_SESSION['user_id'],
        trim($data['recipient_name']),
        trim($data['address']),
        trim($data['city']),
        trim($data['province']),
        trim($data['postal_code']),
        trim($data['phone'])
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan alamat']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>