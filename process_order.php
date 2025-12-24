<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu.";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Akses tidak diizinkan.";
    header("Location: checkout.php");
    exit;
}

// ✅ Ambil item dari SESSION (sudah difilter di checkout_selected.php)
if (!isset($_SESSION['checkout_items']) || empty($_SESSION['checkout_items'])) {
    $_SESSION['error'] = "Tidak ada item yang dipilih untuk checkout.";
    header("Location: cart.php");
    exit;
}
$cart_items = $_SESSION['checkout_items'];

// Validasi data form
$required = ['recipient_name', 'address', 'city', 'postal_code', 'phone', 'province', 'payment_method', 'shipping_type', 'subtotal', 'grand_total'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "Data alamat tidak lengkap.";
        header("Location: checkout.php");
        exit;
    }
}

$recipient_name = trim($_POST['recipient_name']);
$address = trim($_POST['address']);
$city = trim($_POST['city']);
$postal_code = trim($_POST['postal_code']);
$phone = trim($_POST['phone']);
$province = trim($_POST['province']);
$payment_method = $_POST['payment_method'];
$shipping_type = $_POST['shipping_type'];
$subtotal = (float)$_POST['subtotal'];
$grand_total = (float)$_POST['grand_total'];

// Ambil ongkir resmi
$stmt = $pdo->prepare("SELECT cost_regular, cost_express FROM shipping_zones WHERE province = ? LIMIT 1");
$stmt->execute([$province]);
$zone = $stmt->fetch();

if (!$zone) {
    $_SESSION['error'] = "Ongkos kirim untuk {$province} tidak tersedia.";
    header("Location: checkout.php");
    exit;
}

$expected_cost = $shipping_type === 'express' ? (int)$zone['cost_express'] : (int)$zone['cost_regular'];
$expected_grand = $subtotal + $expected_cost;

if (abs($grand_total - $expected_grand) > 100) {
    $grand_total = $expected_grand;
}

// ✅ Set status
$status = $payment_method === 'cod' ? 'processing' : 'pending';

try {
    $pdo->beginTransaction();

    // ✅ VALIDASI STOK SEBELUM PROSES ORDER
    $stockCheckStmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
    foreach ($cart_items as $item) {
        $stockCheckStmt->execute([$item['product_id']]);
        $product = $stockCheckStmt->fetch();
        
        if (!$product) {
            throw new Exception("Produk dengan ID {$item['product_id']} tidak ditemukan.");
        }
        
        if ($product['stock'] < $item['quantity']) {
            throw new Exception("Stok {$product['name']} tidak mencukupi. Tersedia: {$product['stock']}, Diminta: {$item['quantity']}");
        }
    }

    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, total_amount, recipient_name, address, city, postal_code, phone, province,
            subtotal, shipping_cost, shipping_method, payment_method, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $grand_total,
        $recipient_name,
        $address,
        $city,
        $postal_code,
        $phone,
        $province,
        $subtotal,
        $expected_cost,
        $shipping_type,
        $payment_method,
        $status
    ]);

    $order_id = $pdo->lastInsertId();

    // ✅ Insert items & kurangi stok (DIPERBAIKI)
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, price, quantity, size, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    // ✅ UPDATE STOK DENGAN CARA YANG BENAR
    $updateStockStmt = $pdo->prepare("
        UPDATE products 
        SET stock = CASE 
            WHEN stock >= ? THEN stock - ?
            ELSE 0
        END
        WHERE id = ?
    ");

    foreach ($cart_items as $item) {
        // Hitung harga yang akan disimpan
        $base_price = isset($item['base_price']) ? (float)$item['base_price'] : (float)$item['price'];
        $extra_charge = isset($item['extra_charge']) ? (float)$item['extra_charge'] : 0;
        $price_to_store = $base_price + $extra_charge;
        
        // Simpan ke order_items
        $itemStmt->execute([
            $order_id,
            $item['product_id'],
            $price_to_store,
            $item['quantity'],
            $item['size'] ?? null,
            $item['notes'] ?? null
        ]);

        // ✅ KURANGI STOK - FIXED: parameter harus benar
        $qty = (int)$item['quantity'];
        $updateStockStmt->execute([
            $qty,  // untuk pengecekan CASE WHEN stock >= ?
            $qty,  // untuk pengurangan stock - ?
            $item['product_id']
        ]);
        
        // ✅ LOGGING untuk debugging (opsional, bisa dihapus nanti)
        error_log("Stok dikurangi: Product ID {$item['product_id']}, Quantity: {$qty}");
    }

    // ✅ Hanya hapus cart_id yang dipilih
    $selected_cart_ids = array_column($cart_items, 'cart_id');
    if (!empty($selected_cart_ids)) {
        $placeholders = str_repeat('?,', count($selected_cart_ids) - 1) . '?';
        $deleteStmt = $pdo->prepare("DELETE FROM carts WHERE id IN ($placeholders) AND user_id = ?");
        $deleteStmt->execute(array_merge($selected_cart_ids, [$_SESSION['user_id']]));
    }

    $pdo->commit();

    // Bersihkan session
    unset($_SESSION['checkout_items'], $_SESSION['checkout_total']);

    if ($payment_method === 'cod') {
        $_SESSION['success'] = "Pesanan berhasil dibuat! Stok telah dikurangi. Status: Dikemas.";
        header("Location: orders.php");
    } else {
        header("Location: payment.php?order_id=" . $order_id);
    }
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Order error: " . $e->getMessage());
    $_SESSION['error'] = "Gagal membuat pesanan: " . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>