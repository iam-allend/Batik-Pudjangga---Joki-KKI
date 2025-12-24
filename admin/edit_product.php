<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../config/db.php';

// Ambil ID produk
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID produk tidak valid.");
}
$product_id = (int)$_GET['id'];

// Ambil data produk
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak ditemukan.");
}

// Ambil semua gambar produk
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
$stmt->execute([$product_id]);
$product_images = $stmt->fetchAll();

$message = '';
$error = '';

// Handle Delete Image
if (isset($_GET['delete_image'])) {
    $image_id = (int)$_GET['delete_image'];
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->execute([$image_id, $product_id]);
    $image = $stmt->fetch();
    
    if ($image) {
        // Hapus file fisik
        $file_path = "../assets/img/" . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt->execute([$image_id]);
        
        $message = "Gambar berhasil dihapus!";
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
        $stmt->execute([$product_id]);
        $product_images = $stmt->fetchAll();
    }
}

// Handle Set Primary Image
if (isset($_GET['set_primary'])) {
    $image_id = (int)$_GET['set_primary'];
    
    // Reset semua jadi bukan primary
    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
    $stmt->execute([$product_id]);
    
    // Set yang dipilih jadi primary
    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?");
    $stmt->execute([$image_id, $product_id]);
    
    // Update image utama di tabel products
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $primary_image = $stmt->fetchColumn();
    
    if ($primary_image) {
        $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
        $stmt->execute([$primary_image, $product_id]);
    }
    
    $message = "Gambar utama berhasil diubah!";
    
    // Refresh data
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll();
}

// Proses update produk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_sale = isset($_POST['is_sale']) ? 1 : 0;
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $sale_start = !empty($_POST['sale_start']) ? $_POST['sale_start'] : null;
    $sale_end = !empty($_POST['sale_end']) ? $_POST['sale_end'] : null;
    $stock = (int)($_POST['stock'] ?? 0);

    if (empty($name) || $price <= 0) {
        $error = "Nama dan harga wajib diisi dengan benar.";
    } else {
        // Update produk tanpa mengubah image (image dikelola terpisah)
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, price = ?, category = ?, description = ?, 
                is_new = ?, is_sale = ?, sale_price = ?, 
                sale_start = ?, sale_end = ?, stock = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $name, $price, $category, $description,
            $is_new, $is_sale, $sale_price,
            $sale_start, $sale_end, $stock, $product_id
        ]);
        $message = "Produk berhasil diperbarui!";
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
    }
}

// Proses upload multiple images
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_images'])) {
    $uploaded_count = 0;
    $errors = [];
    
    if (!empty($_FILES['images']['name'][0])) {
        $total_files = count($_FILES['images']['name']);
        
        // Get current max sort_order
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $max_sort = $stmt->fetchColumn();
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = uniqid() . "_" . basename($_FILES['images']['name'][$i]);
                $target_file = "../assets/img/" . $file_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                // Validasi tipe file
                if (!in_array($imageFileType, $allowed)) {
                    $errors[] = "File {$_FILES['images']['name'][$i]} bukan format gambar yang valid.";
                    continue;
                }
                
                // Validasi ukuran file (max 2MB)
                if ($_FILES['images']['size'][$i] > 2097152) {
                    $errors[] = "File {$_FILES['images']['name'][$i]} terlalu besar (max 2MB).";
                    continue;
                }
                
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                    $max_sort++;
                    
                    // Jika ini gambar pertama, set sebagai primary
                    $is_primary = empty($product_images) && $uploaded_count === 0 ? 1 : 0;
                    
                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$product_id, $file_name, $is_primary, $max_sort]);
                    
                    // Update image utama di tabel products jika ini primary
                    if ($is_primary) {
                        $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
                        $stmt->execute([$file_name, $product_id]);
                    }
                    
                    $uploaded_count++;
                }
            }
        }
        
        if ($uploaded_count > 0) {
            $message = "$uploaded_count gambar berhasil diupload!";
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
            $stmt->execute([$product_id]);
            $product_images = $stmt->fetchAll();
        }
        
        if (!empty($errors)) {
            $error = implode("<br>", $errors);
        }
    } else {
        $error = "Silakan pilih minimal 1 gambar untuk diupload.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .image-item {
            position: relative;
            border: 2px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            transition: border-color 0.3s;
        }
        
        .image-item.primary {
            border-color: #d4a373;
            box-shadow: 0 0 10px rgba(212, 163, 115, 0.3);
        }
        
        .image-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .image-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px;
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        
        .image-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #d4a373;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .btn-image {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            color: white;
        }
        
        .btn-primary-set {
            background: #28a745;
        }
        
        .btn-image-delete {
            background: #dc3545;
        }
        
        .upload-section {
            background: #f9f7f3;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
            background: #d4a373;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .file-input-wrapper:hover {
            background: #b58360;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .selected-files {
            margin-top: 15px;
            color: #555;
            font-size: 0.95rem;
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
            <h1>Edit Produk: <?= htmlspecialchars($product['name']) ?></h1>
            <p>Perbarui informasi produk dan kelola gambar.</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="error" style="background:#d4edda; color:#155724; border-color:#c3e6cb;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Galeri Gambar Produk -->
        <div class="upload-section">
            <h2 style="margin-top:0; color:#2c1e15;">Galeri Gambar Produk</h2>
            <p style="color:#555; margin-bottom:20px;">Kelola gambar produk. Klik "Set Utama" untuk menentukan gambar yang tampil di katalog.</p>
            
            <?php if (empty($product_images)): ?>
                <div style="text-align:center; padding:40px; background:white; border-radius:8px;">
                    <p style="color:#999; font-size:1.1rem;">Belum ada gambar. Upload gambar pertama di bawah.</p>
                </div>
            <?php else: ?>
                <div class="image-gallery">
                    <?php foreach ($product_images as $img): ?>
                        <div class="image-item <?= $img['is_primary'] ? 'primary' : '' ?>">
                            <img src="../assets/img/<?= htmlspecialchars($img['image_path']) ?>" alt="Product Image">
                            <?php if ($img['is_primary']): ?>
                                <span class="image-badge">★ UTAMA</span>
                            <?php endif; ?>
                            <div class="image-actions">
                                <?php if (!$img['is_primary']): ?>
                                    <a href="?id=<?= $product_id ?>&set_primary=<?= $img['id'] ?>" 
                                       class="btn-image btn-primary-set"
                                       onclick="return confirm('Set gambar ini sebagai gambar utama?')">
                                        Set Utama
                                    </a>
                                <?php endif; ?>
                                <a href="?id=<?= $product_id ?>&delete_image=<?= $img['id'] ?>" 
                                   class="btn-image btn-image-delete"
                                   onclick="return confirm('Hapus gambar ini?')">
                                    Hapus
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Form Upload Gambar Baru -->
            <form method="POST" enctype="multipart/form-data" style="margin-top:30px;">
                <h3 style="color:#2c1e15; margin-bottom:15px;">Upload Gambar Baru</h3>
                <label class="file-input-wrapper">
                    <input type="file" name="images[]" id="imageInput" multiple accept="image/*" onchange="displayFileNames()">
                    📁 Pilih Gambar (bisa multiple)
                </label>
                <div id="fileNames" class="selected-files"></div>
                <button type="submit" name="upload_images" class="btn-submit" style="margin-top:15px;">Upload Gambar</button>
                <small style="display:block; margin-top:10px; color:#777;">
                    Format: JPG, PNG, WEBP | Max 2MB per file | Bisa upload beberapa gambar sekaligus
                </small>
            </form>
        </div>

        <!-- Form Edit Produk -->
        <div class="admin-form">
            <h2 style="margin-top:0;">Informasi Produk</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="price" value="<?= $product['price'] ?>" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" placeholder="Contoh: One Set, Pants">
                    </div>
                </div>

                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0">
                    <small style="color:#777;">Stok global (jika tidak pakai varian)</small>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_new" value="1" <?= $product['is_new'] ? 'checked' : '' ?>>
                        Tandai sebagai Produk Baru
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_sale" value="1" <?= $product['is_sale'] ? 'checked' : '' ?> id="is_sale_check">
                        Tampilkan di Sale
                    </label>
                </div>

                <div class="form-group" id="sale_price_field" style="display:<?= $product['is_sale'] ? 'block' : 'none' ?>;">
                    <label>Harga Sale (Rp)</label>
                    <input type="number" name="sale_price" value="<?= $product['sale_price'] ?? '' ?>" min="1" step="0.01" placeholder="Misal: 199000.00">
                </div>

                <div class="form-group" id="sale_schedule_field" style="display:<?= $product['is_sale'] ? 'block' : 'none' ?>;">
                    <label>Jadwal Promo (Opsional)</label>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <div>
                            <small>Mulai</small>
                            <input type="date" name="sale_start" value="<?= $product['sale_start'] ?? '' ?>" style="width:140px;">
                        </div>
                        <div>
                            <small>Berakhir</small>
                            <input type="date" name="sale_end" value="<?= $product['sale_end'] ?? '' ?>" style="width:140px;">
                        </div>
                    </div>
                </div>

                <div class="form-group full">
                    <label>Deskripsi</label>
                    <textarea name="description" placeholder="Deskripsi produk..."><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <button type="submit" name="update_product" class="btn-submit">Simpan Perubahan</button>
                <a href="products.php" style="display:inline-block; margin-left:15px; padding:12px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:8px;">Kembali</a>
            </form>
        </div>

        <!-- Link ke Varian Produk -->
        <div style="margin-top: 25px; padding: 20px; background: #f9f7f3; border-radius: 10px; text-align: center;">
            <h3 style="margin: 0 0 15px; color: #2c1e15;">Kelola Varian Produk</h3>
            <p style="margin: 0 0 20px; color: #555;">Atur warna, ukuran, dan stok per varian di sini.</p>
            <a href="product_variants.php?id=<?= $product_id ?>" class="btn-action" style="background:#d4a373; color:white; padding:12px 25px; text-decoration:none; display:inline-block;">
                Kelola Varian (Warna & Ukuran)
            </a>
        </div>
    </div>

    <script>
    // Toggle field sale
    document.getElementById('is_sale_check').addEventListener('change', function() {
        const priceField = document.getElementById('sale_price_field');
        const scheduleField = document.getElementById('sale_schedule_field');
        if (this.checked) {
            priceField.style.display = 'block';
            scheduleField.style.display = 'block';
        } else {
            priceField.style.display = 'none';
            scheduleField.style.display = 'none';
        }
    });
    
    // Display selected file names
    function displayFileNames() {
        const input = document.getElementById('imageInput');
        const display = document.getElementById('fileNames');
        
        if (input.files.length > 0) {
            let fileNames = [];
            for (let i = 0; i < input.files.length; i++) {
                fileNames.push(input.files[i].name);
            }
            display.innerHTML = '<strong>File dipilih:</strong><br>' + fileNames.join('<br>');
        } else {
            display.innerHTML = '';
        }
    }
    </script>
</body>
</html>