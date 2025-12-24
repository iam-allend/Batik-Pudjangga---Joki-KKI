<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../config/db.php';

$product_id = $_GET['id'] ?? null;
if (!$product_id || !is_numeric($product_id)) {
    die("ID produk tidak valid.");
}

// Ambil data produk
$stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) {
    die("Produk tidak ditemukan.");
}

// Tambah varian baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_variant'])) {
    $color = trim($_POST['color'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    
    if (!empty($color) && !empty($size) && $stock >= 0) {
        $stmt = $pdo->prepare("
            INSERT INTO product_variants (product_id, color, size, stock)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$product_id, $color, $size, $stock]);
        $_SESSION['message'] = "Varian berhasil ditambahkan!";
    }
    header("Location: product_variants.php?id=$product_id");
    exit;
}

// Update stok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $variant_id = $_POST['variant_id'];
    $stock = (int)($_POST['stock'] ?? 0);
    
    $stmt = $pdo->prepare("UPDATE product_variants SET stock = ? WHERE id = ? AND product_id = ?");
    $stmt->execute([$stock, $variant_id, $product_id]);
    $_SESSION['message'] = "Stok berhasil diperbarui!";
    header("Location: product_variants.php?id=$product_id");
    exit;
}

// Hapus varian
if (isset($_GET['delete'])) {
    $variant_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ? AND product_id = ?");
    $stmt->execute([$variant_id, $product_id]);
    $_SESSION['message'] = "Varian berhasil dihapus!";
    header("Location: product_variants.php?id=$product_id");
    exit;
}

// Ambil semua varian
$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id");
$stmt->execute([$product_id]);
$variants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Varian Produk - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Produk</a>
        <a href="subscribers.php">Subscriber</a>
        <a href="report_messages.php">Report Messages</a>
        <a href="sales_report.php">Laporan</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="welcome">
            <h1>Varian: <?= htmlspecialchars($product['name']) ?></h1>
            <p>Kelola warna, ukuran, dan stok produk di sini.</p>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="error" style="background:#d4edda; color:#155724; border-color:#c3e6cb;">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Form Tambah Varian -->
        <div class="admin-form">
            <h2>Tambah Varian Baru</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Warna</label>
                        <input type="text" name="color" required placeholder="Contoh: Hitam">
                    </div>
                    <div class="form-group">
                        <label>Ukuran</label>
                        <input type="text" name="size" required placeholder="Contoh: S">
                    </div>
                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stock" min="0" value="0" required>
                    </div>
                </div>
                <button type="submit" name="add_variant" class="btn-submit">Tambah Varian</button>
            </form>
        </div>

        <!-- Tabel Varian -->
        <div class="table-container" style="margin-top: 30px;">
            <h2>Daftar Varian</h2>
            <?php if (empty($variants)): ?>
                <p style="text-align:center; padding:20px;">Belum ada varian.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Warna</th>
                            <th>Ukuran</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($variants as $v): ?>
                        <tr>
                            <td data-label="Warna"><?= htmlspecialchars($v['color']) ?></td>
                            <td data-label="Ukuran"><?= htmlspecialchars($v['size']) ?></td>
                            <td data-label="Stok">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">
                                    <input type="number" name="stock" value="<?= $v['stock'] ?>" min="0" style="width:80px; padding:4px;">
                                    <button type="submit" name="update_stock" class="btn-action btn-edit" style="padding:4px 8px; margin-left:5px;">Simpan</button>
                                </form>
                            </td>
                            <td data-label="Aksi" class="actions-col">
                                <a href="?id=<?= $product_id ?>&delete=<?= $v['id'] ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('Hapus varian ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <a href="edit_product.php?id=<?= $product_id ?>" class="btn-action" style="background:#6c757d; color:white; padding:10px 20px; text-decoration:none;">Kembali ke Edit Produk</a>
        </div>
    </div>
</body>
</html>6