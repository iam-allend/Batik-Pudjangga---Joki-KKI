<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../config/db.php';

// Reset semua sale
if (isset($_GET['reset_sale'])) {
    $stmt = $pdo->prepare("UPDATE products SET is_sale = 0, sale_price = NULL, sale_start = NULL, sale_end = NULL");
    $stmt->execute();
    $_SESSION['message'] = "Semua promo berhasil dihentikan!";
    header("Location: products.php");
    exit;
}

// Update stok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $product_id = $_POST['product_id'];
    $new_stock = (int)($_POST['stock'] ?? 0);
    $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->execute([$new_stock, $product_id]);
    $_SESSION['message'] = "Stok produk berhasil diperbarui!";
    header("Location: products.php");
    exit;
}

// Toggle "New"
if (isset($_GET['toggle_new'])) {
    $id = $_GET['toggle_new'];
    $stmt = $pdo->prepare("SELECT is_new FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();
    $new_status = $current ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE products SET is_new = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    header("Location: products.php?" . http_build_query($_GET));
    exit;
}

// Toggle "Sale"
if (isset($_GET['toggle_sale'])) {
    $id = $_GET['toggle_sale'];
    $stmt = $pdo->prepare("SELECT is_sale FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();
    $new_status = $current ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE products SET is_sale = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    header("Location: products.php?" . http_build_query($_GET));
    exit;
}

// === FILTERING & SEARCHING ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_sale = isset($_GET['filter_sale']) ? $_GET['filter_sale'] : 'all'; // all, yes, no
$filter_new = isset($_GET['filter_new']) ? $_GET['filter_new'] : 'all'; // all, yes, no
$filter_stock = isset($_GET['filter_stock']) ? $_GET['filter_stock'] : 'all'; // all, available, low, out
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id_desc'; // id_desc, id_asc, name_asc, name_desc, price_asc, price_desc, stock_asc, stock_desc

// Build query
$where_conditions = [];
$params = [];

// Search
if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Filter Sale
if ($filter_sale === 'yes') {
    $where_conditions[] = "is_sale = 1";
} elseif ($filter_sale === 'no') {
    $where_conditions[] = "is_sale = 0";
}

// Filter New
if ($filter_new === 'yes') {
    $where_conditions[] = "is_new = 1";
} elseif ($filter_new === 'no') {
    $where_conditions[] = "is_new = 0";
}

// Filter Stock
if ($filter_stock === 'available') {
    $where_conditions[] = "stock > 10";
} elseif ($filter_stock === 'low') {
    $where_conditions[] = "stock > 0 AND stock <= 10";
} elseif ($filter_stock === 'out') {
    $where_conditions[] = "stock = 0";
}

// Build WHERE clause
$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Sorting
$order_sql = match($sort_by) {
    'id_asc' => 'ORDER BY id ASC',
    'name_asc' => 'ORDER BY name ASC',
    'name_desc' => 'ORDER BY name DESC',
    'price_asc' => 'ORDER BY price ASC',
    'price_desc' => 'ORDER BY price DESC',
    'stock_asc' => 'ORDER BY stock ASC',
    'stock_desc' => 'ORDER BY stock DESC',
    default => 'ORDER BY id DESC'
};

// Execute query
$sql = "SELECT * FROM products $where_sql $order_sql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Hitung jumlah produk sale
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_sale = 1");
$sale_count = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 600;
        }
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            min-width: 150px;
        }
        .filter-group input[type="text"] {
            min-width: 250px;
        }
        .btn-filter {
            padding: 9px 20px;
            background: #d4a373;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-filter:hover {
            background: #c69363;
        }
        .btn-reset {
            padding: 9px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-reset:hover {
            background: #5a6268;
        }
        .result-info {
            padding: 10px 15px;
            background: #e9ecef;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #495057;
        }
        .result-info strong {
            color: #d4a373;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Produk</a>
        <a href="subscribers.php">Subscriber</a>
        <a href="report_messages.php">Report Messages</a>
        <a href="orders.php">Orders Check</a>
        <a href="sales_report.php">Laporan</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="welcome">
            <h1>Kelola Produk</h1>
            <p>Kelola stok, harga, dan status produk di sini.</p>
            <?php if ($sale_count > 0): ?>
                <p style="color:#d4a373; font-weight:600; margin-top:10px;">
                    🎉 Ada <strong><?= $sale_count ?></strong> produk sedang dalam promo!
                </p>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="error" style="background:#d4edda; color:#155724; border-color:#c3e6cb;">
                <?= htmlspecialchars($_SESSION['message']) ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Filter & Search Form -->
        <div class="filter-container">
            <h3 style="margin-top:0; margin-bottom:15px; color:#2c3e50;">🔍 Filter & Pencarian</h3>
            <form method="GET" action="products.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Cari Produk</label>
                        <input type="text" name="search" placeholder="Cari nama atau deskripsi..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Status Sale</label>
                        <select name="filter_sale">
                            <option value="all" <?= $filter_sale === 'all' ? 'selected' : '' ?>>Semua</option>
                            <option value="yes" <?= $filter_sale === 'yes' ? 'selected' : '' ?>>Sedang Sale</option>
                            <option value="no" <?= $filter_sale === 'no' ? 'selected' : '' ?>>Tidak Sale</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status New</label>
                        <select name="filter_new">
                            <option value="all" <?= $filter_new === 'all' ? 'selected' : '' ?>>Semua</option>
                            <option value="yes" <?= $filter_new === 'yes' ? 'selected' : '' ?>>Produk Baru</option>
                            <option value="no" <?= $filter_new === 'no' ? 'selected' : '' ?>>Bukan Baru</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Status Stok</label>
                        <select name="filter_stock">
                            <option value="all" <?= $filter_stock === 'all' ? 'selected' : '' ?>>Semua</option>
                            <option value="available" <?= $filter_stock === 'available' ? 'selected' : '' ?>>Stok Tersedia (>10)</option>
                            <option value="low" <?= $filter_stock === 'low' ? 'selected' : '' ?>>Stok Menipis (1-10)</option>
                            <option value="out" <?= $filter_stock === 'out' ? 'selected' : '' ?>>Habis (0)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Urutkan</label>
                        <select name="sort_by">
                            <option value="id_desc" <?= $sort_by === 'id_desc' ? 'selected' : '' ?>>Terbaru</option>
                            <option value="id_asc" <?= $sort_by === 'id_asc' ? 'selected' : '' ?>>Terlama</option>
                            <option value="name_asc" <?= $sort_by === 'name_asc' ? 'selected' : '' ?>>Nama A-Z</option>
                            <option value="name_desc" <?= $sort_by === 'name_desc' ? 'selected' : '' ?>>Nama Z-A</option>
                            <option value="price_asc" <?= $sort_by === 'price_asc' ? 'selected' : '' ?>>Harga Terendah</option>
                            <option value="price_desc" <?= $sort_by === 'price_desc' ? 'selected' : '' ?>>Harga Tertinggi</option>
                            <option value="stock_asc" <?= $sort_by === 'stock_asc' ? 'selected' : '' ?>>Stok Terendah</option>
                            <option value="stock_desc" <?= $sort_by === 'stock_desc' ? 'selected' : '' ?>>Stok Tertinggi</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-filter">Terapkan Filter</button>
                    <a href="products.php" class="btn-reset">Reset Filter</a>
                </div>
            </form>
        </div>

        <!-- Result Info -->
        <?php if (!empty($search) || $filter_sale !== 'all' || $filter_new !== 'all' || $filter_stock !== 'all'): ?>
            <div class="result-info">
                📊 Menampilkan <strong><?= count($products) ?></strong> produk
                <?php if (!empty($search)): ?>
                    untuk pencarian "<strong><?= htmlspecialchars($search) ?></strong>"
                <?php endif; ?>
                <?php if ($filter_sale !== 'all' || $filter_new !== 'all' || $filter_stock !== 'all'): ?>
                    dengan filter aktif
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Tombol Aksi Promo -->
        <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
            <?php if ($sale_count > 0): ?>
                <a href="?reset_sale=1" class="btn-action" style="background:#dc3545;" 
                   onclick="return confirm('Hentikan semua promo? Harga akan kembali normal.')">
                    Reset Semua Sale
                </a>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>New?</th>
                        <th>Sale?</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:20px;">
                                <?php if (!empty($search) || $filter_sale !== 'all' || $filter_new !== 'all' || $filter_stock !== 'all'): ?>
                                    Tidak ada produk yang sesuai dengan filter atau pencarian Anda.
                                <?php else: ?>
                                    Belum ada produk.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td data-label="Gambar">
                                <?php if ($p['image']): ?>
                                    <img src="../assets/img/<?= htmlspecialchars($p['image']) ?>" 
                                         alt="<?= htmlspecialchars($p['name']) ?>" 
                                         class="product-img">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td data-label="Nama"><?= htmlspecialchars($p['name']) ?></td>
                            <td data-label="Harga" class="price-col">
                                Rp <?= number_format($p['price'], 0, ',', '.') ?>
                                <?php if ($p['is_sale'] && !empty($p['sale_price'])): ?>
                                    <br><small style="color:#d4a373; font-weight:normal;">
                                        Sale: Rp <?= number_format($p['sale_price'], 0, ',', '.') ?>
                                        <?php if (!empty($p['sale_start']) || !empty($p['sale_end'])): ?>
                                            <br><small style="color:#6c757d;">
                                                <?php if ($p['sale_start']): ?>
                                                    Mulai: <?= date('d M Y', strtotime($p['sale_start'])) ?>
                                                <?php endif; ?>
                                                <?php if ($p['sale_end']): ?>
                                                    <?= $p['sale_start'] ? ' - ' : '' ?>
                                                    Akhir: <?= date('d M Y', strtotime($p['sale_end'])) ?>
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Stok">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="number" name="stock" value="<?= $p['stock'] ?? 0 ?>" min="0" size="5" 
                                           style="width:80px; padding:4px; <?= $p['stock'] == 0 ? 'border-color:#dc3545;' : ($p['stock'] <= 10 ? 'border-color:#ffc107;' : '') ?>">
                                    <button type="submit" name="update_stock" class="btn-action btn-edit" style="padding:4px 8px; margin-left:5px;">Simpan</button>
                                </form>
                                <?php if ($p['stock'] == 0): ?>
                                    <br><small style="color:#dc3545; font-weight:600;">Habis</small>
                                <?php elseif ($p['stock'] <= 10): ?>
                                    <br><small style="color:#ffc107; font-weight:600;">Stok Menipis!</small>
                                <?php endif; ?>
                            </td>
                            <td data-label="New?">
                                <a href="?toggle_new=<?= $p['id'] ?>&<?= http_build_query($_GET) ?>" class="btn-action <?= $p['is_new'] ? 'btn-edit' : '' ?>" 
                                   style="background:<?= $p['is_new'] ? '#28a745' : '#6c757d' ?>;">
                                    <?= $p['is_new'] ? 'Ya' : 'Tidak' ?>
                                </a>
                            </td>
                            <td data-label="Sale?">
                                <a href="?toggle_sale=<?= $p['id'] ?>&<?= http_build_query($_GET) ?>" class="btn-action <?= $p['is_sale'] ? 'btn-edit' : '' ?>" 
                                   style="background:<?= $p['is_sale'] ? '#dc3545' : '#6c757d' ?>;">
                                    <?= $p['is_sale'] ? 'Ya' : 'Tidak' ?>
                                </a>
                            </td>
                            <td data-label="Aksi" class="actions-col">
                                <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-action btn-edit">Edit</a>
                                <a href="delete_product.php?id=<?= $p['id'] ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('Hapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <a href="add_product.php" class="btn-action btn-edit" style="padding:10px 20px; text-decoration:none; display:inline-block;">+ Tambah Produk</a>
        </div>
    </div>
</body>
</html>