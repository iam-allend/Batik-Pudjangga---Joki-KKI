<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    } elseif (empty($_FILES['images']['name'][0])) {
        $error = "Minimal upload 1 gambar produk.";
    } else {
        // Insert produk dulu (tanpa image utama)
        $stmt = $pdo->prepare("
            INSERT INTO products (name, price, image, category, description, is_new, is_sale, sale_price, sale_start, sale_end, stock) 
            VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name, $price, $category, $description,
            $is_new, $is_sale, $sale_price,
            $sale_start, $sale_end, $stock
        ]);
        
        $product_id = $pdo->lastInsertId();
        
        // Upload multiple images
        $uploaded_count = 0;
        $primary_image = null;
        $total_files = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = uniqid() . "_" . basename($_FILES['images']['name'][$i]);
                $target_file = "../assets/img/" . $file_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($imageFileType, $allowed) && $_FILES['images']['size'][$i] <= 2097152) {
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                        // Gambar pertama = primary
                        $is_primary = ($uploaded_count === 0) ? 1 : 0;
                        
                        if ($is_primary) {
                            $primary_image = $file_name;
                        }
                        
                        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$product_id, $file_name, $is_primary, $uploaded_count]);
                        
                        $uploaded_count++;
                    }
                }
            }
        }
        
        // Update primary image di tabel products
        if ($primary_image) {
            $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
            $stmt->execute([$primary_image, $product_id]);
        }
        
        if ($uploaded_count > 0) {
            $_SESSION['message'] = "Produk berhasil ditambahkan dengan $uploaded_count gambar!";
            header("Location: products.php");
            exit;
        } else {
            $error = "Produk ditambahkan tapi gagal upload gambar. Silakan edit produk untuk menambah gambar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
        
        .preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .preview-item {
            position: relative;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .preview-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }
        
        .preview-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #d4a373;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
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
            <h1>Tambah Produk Baru</h1>
            <p>Lengkapi form di bawah untuk menambahkan produk.</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="error" style="background:#d4edda; color:#155724; border-color:#c3e6cb;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-form">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Produk <span style="color:red;">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Kemeja Batik Premium">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Harga (Rp) <span style="color:red;">*</span></label>
                        <input type="number" name="price" min="1" step="0.01" required placeholder="250000">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category">
                            <option value="">Pilih Kategori</option>
                            <option value="men">Men (Pria)</option>
                            <option value="women">Women (Wanita)</option>
                            <option value="pants">Pants (Celana)</option>
                            <option value="oneset">One Set</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stock" value="0" min="0" placeholder="10">
                    <small style="color:#777;">Stok global (jika tidak pakai varian)</small>
                </div>

                <div class="form-group">
                    <label>Upload Gambar Produk <span style="color:red;">*</span></label>
                    <label class="file-input-wrapper">
                        <input type="file" name="images[]" id="imageInput" multiple accept="image/*" required onchange="previewImages()">
                        📁 Pilih Gambar (bisa multiple)
                    </label>
                    <small style="display:block; margin-top:10px; color:#777;">
                        Format: JPG, PNG, WEBP | Max 2MB per file | Gambar pertama akan menjadi gambar utama
                    </small>
                    <div id="fileNames" class="selected-files"></div>
                    <div id="previewContainer" class="preview-container"></div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_new" value="1">
                        Tandai sebagai Produk Baru
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_sale" value="1" id="is_sale_check">
                        Tampilkan di Sale
                    </label>
                </div>

                <div class="form-group" id="sale_price_field" style="display:none;">
                    <label>Harga Sale (Rp)</label>
                    <input type="number" name="sale_price" min="1" step="0.01" placeholder="199000">
                </div>

                <div class="form-group" id="sale_schedule_field" style="display:none;">
                    <label>Jadwal Promo (Opsional)</label>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <div>
                            <small>Mulai</small>
                            <input type="date" name="sale_start" style="width:140px;">
                        </div>
                        <div>
                            <small>Berakhir</small>
                            <input type="date" name="sale_end" style="width:140px;">
                        </div>
                    </div>
                </div>

                <div class="form-group full">
                    <label>Deskripsi</label>
                    <textarea name="description" placeholder="Deskripsi produk..." rows="6"></textarea>
                </div>

                <button type="submit" class="btn-submit">Tambah Produk</button>
                <a href="products.php" style="display:inline-block; margin-left:15px; padding:12px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:8px;">Batal</a>
            </form>
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
    
    // Preview images
    function previewImages() {
        const input = document.getElementById('imageInput');
        const display = document.getElementById('fileNames');
        const previewContainer = document.getElementById('previewContainer');
        
        // Clear previous previews
        previewContainer.innerHTML = '';
        
        if (input.files.length > 0) {
            let fileNames = [];
            
            for (let i = 0; i < input.files.length; i++) {
                fileNames.push(input.files[i].name);
                
                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    previewItem.appendChild(img);
                    
                    // Add badge for first image
                    if (i === 0) {
                        const badge = document.createElement('span');
                        badge.className = 'preview-badge';
                        badge.textContent = '★ UTAMA';
                        previewItem.appendChild(badge);
                    }
                    
                    previewContainer.appendChild(previewItem);
                };
                reader.readAsDataURL(input.files[i]);
            }
            
            display.innerHTML = '<strong>' + input.files.length + ' file dipilih:</strong><br>' + fileNames.join('<br>');
        } else {
            display.innerHTML = '';
        }
    }
    </script>
</body>
</html>