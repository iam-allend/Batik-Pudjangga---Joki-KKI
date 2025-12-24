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
    header("Location: cart.php");
    exit;
}

// Ambil data JSON dari hidden input
$selected_json = $_POST['selected_items_json'] ?? '[]';
$selected_ids = json_decode($selected_json, true);

if (empty($selected_ids) || !is_array($selected_ids)) {
    $_SESSION['error'] = "Silakan pilih minimal 1 item untuk checkout.";
    header("Location: cart.php");
    exit;
}

// Sanitize IDs
$selected_ids = array_map('intval', $selected_ids);
$placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';

// ✅ Ambil data cart yang dipilih + validasi stok
$stmt = $pdo->prepare("
    SELECT 
        c.id as cart_id, 
        c.quantity, 
        c.size, 
        c.notes, 
        p.id as product_id, 
        p.name, 
        p.price, 
        p.image,
        p.stock
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.id IN ($placeholders) AND c.user_id = ?
");
$stmt->execute(array_merge($selected_ids, [$_SESSION['user_id']]));
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    $_SESSION['error'] = "Item tidak ditemukan atau sudah dihapus.";
    header("Location: cart.php");
    exit;
}

// ✅ VALIDASI STOK SEBELUM CHECKOUT
$stock_errors = [];
foreach ($items as $item) {
    if ($item['stock'] < $item['quantity']) {
        $stock_errors[] = "{$item['name']} (Stok tersedia: {$item['stock']}, Anda pilih: {$item['quantity']})";
    }
}

if (!empty($stock_errors)) {
    $_SESSION['error'] = "Stok tidak mencukupi untuk: " . implode(", ", $stock_errors);
    header("Location: cart.php");
    exit;
}

// ✅ Hitung total dan format data
$checkout_items = [];
$total = 0;

foreach ($items as $item) {
    $base_price = (float)$item['price'];
    $extra_charge = (!empty($item['size']) && strtoupper($item['size']) === 'XXL') ? 15000 : 0;
    $final_price = $base_price + $extra_charge;
    $subtotal = $final_price * $item['quantity'];
    $total += $subtotal;

    $checkout_items[] = [
        'cart_id' => $item['cart_id'],
        'product_id' => $item['product_id'],
        'name' => $item['name'],
        'image' => $item['image'],
        'base_price' => $base_price,
        'extra_charge' => $extra_charge,
        'quantity' => $item['quantity'],
        'size' => $item['size'],
        'notes' => $item['notes'] ?? '',
        'stock' => $item['stock'] // untuk tracking
    ];
}

// Simpan ke session
$_SESSION['checkout_items'] = $checkout_items;
$_SESSION['checkout_total'] = $total;

header("Location: checkout.php");
exit;
?>