<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';
include 'includes/header.php';

$product_id = $_GET['id'] ?? null;
if (!$product_id || !is_numeric($product_id)) {
    die("Produk tidak ditemukan.");
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        die("Produk dengan ID $product_id tidak ditemukan di database.");
    }
    
    // Ambil semua gambar produk
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id != ? ORDER BY RAND() LIMIT 3");
$stmt->execute([$product_id]);
$recommended = $stmt->fetchAll();
?>

<div class="product-detail">
    <div class="product-detail-container">
        <div class="product-images">
            <?php 
            // Jika ada gambar di product_images, gunakan yang primary atau pertama
            // Jika tidak ada, fallback ke image di tabel products
            if (!empty($product_images)): 
                $main_image = $product_images[0]['image_path'];
            else:
                $main_image = $product['image'];
            endif;
            ?>
            
            <img src="assets/img/<?= htmlspecialchars($main_image) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="product-main-img" id="main-img">
            
            <div class="product-thumbnails">
                <?php if (!empty($product_images)): ?>
                    <?php foreach ($product_images as $img): ?>
                        <img src="assets/img/<?= htmlspecialchars($img['image_path']) ?>" 
                             alt="Thumbnail" 
                             onclick="changeImage('assets/img/<?= htmlspecialchars($img['image_path']) ?>')"
                             class="thumbnail-img">
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback: tampilkan gambar utama saja -->
                    <img src="assets/img/<?= htmlspecialchars($product['image']) ?>" 
                         alt="Thumbnail" 
                         onclick="changeImage('assets/img/<?= htmlspecialchars($product['image']) ?>')"
                         class="thumbnail-img">
                <?php endif; ?>
            </div>
        </div>

        <div class="product-info-detail">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-price">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>

            <div class="product-description">
                <div class="dropdown-preorder">
                    <div class="dropdown-header" onclick="togglePreorder()">
                        <strong>Deskripsi</strong>
                        <span id="arrow">▼</span>
                    </div>

                    <div class="dropdown-content" id="preorderContent">
                        <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Deskripsi tidak tersedia.')) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="product-description">
                <p style="color:#d4a373; font-weight:500; margin-top:10px;">
                    📌 <strong>Sistem Pre-Order:</strong> Barang dibuat khusus setelah kamu order, semua model baju, celana atau oneset
                    itu motif nya di sesuaikan sama stock kain yang ready. 
                    Estimasi pengerjaan 7-14 hari kerja.
                </p>
            </div>

            <div class="product-specs">
                <div class="spec-row">
                    <span class="spec-label">Ukuran:</span>
                    <span class="spec-value">S, M, L, XL, XXL</span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Desain:</span>
                    <span class="spec-value">Motif di sesuaikan dengan stock kain yang ada</span>
                </div>
            </div>

            <div class="product-options">
                <div class="option-group">
                    <label>Ukuran </label>
                    <select name="size" id="size-select" required>
                        <option value="">Pilih Ukuran</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL (+Rp 15.000)</option>
                    </select>
                </div>

                <div class="option-group">
                    <label>Catatan & Request Desain (Opsional)</label>
                    <textarea id="notes-text" name="notes" 
                              placeholder="Contoh: 
                            - Lingkar dada 100cm, panjang 75cm
                            - Tambah saku di dada
                            - Ganti warna benang jahit jadi warna putih
                            - Model seperti di etalase tapi lengan pendek" 
                              style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-top:5px; height:120px;"></textarea>
                    <small style="color:#555; display:block; margin-top:5px;">
                        Jelaskan detail ukuran dan modifikasi yang diinginkan. 
                        Tim kami akan menghubungi jika ada yang perlu dikonfirmasi.
                    </small>
                    
                    <div style="margin-top:15px; padding:15px; background:#f9f7f3; border-radius:8px;">
                        <p style="margin:0 0 10px; color:#2c1e15;"><strong>Butuh bantuan desain?</strong></p>
                        <p style="margin:0 0 10px; color:#555; font-size:0.95rem;">
                            Kirim sketsa atau referensi desain kamu via WhatsApp!
                        </p>
                        <a href="https://wa.me/6285930433717?text=Halo%20Pudjangga%20Batik,%20saya%20mau%20konsultasi%20desain%20custom" 
                           target="_blank"
                           style="display:inline-block; background:#25D366; color:white; padding:8px 16px; border-radius:6px; text-decoration:none; font-weight:500;">
                            📱 Konsultasi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <div class="quantity-selector">
                <button class="quantity-btn" onclick="adjustQty(-1)">-</button>
                <input type="number" class="quantity-input" id="qty" value="1" min="1">
                <button class="quantity-btn" onclick="adjustQty(1)">+</button>
            </div>

            <div class="product-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="add_to_cart.php" onsubmit="return setCartValues()" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="quantity" id="cart-qty" value="1">
                        <input type="hidden" name="size" id="cart-size" value="">
                        <input type="hidden" name="notes" id="cart-notes" value="">
                        <button type="submit" class="btn-add-to-cart">Tambah ke Keranjang</button>
                    </form>
                    <form method="POST" action="add_to_wishlist.php" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" class="wishlist-btn-detail">❤️</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn-add-to-cart">Login untuk Belanja</a>
                    <a href="login.php" class="wishlist-btn-detail">❤️</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="recommended-section">
        <h2>Rekomendasi</h2>
        <div class="product-grid">
            <?php foreach ($recommended as $item): ?>
                <div class="product-card">
                    <a href="product.php?id=<?= $item['id'] ?>">
                        <img src="assets/img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    </a>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p>Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="add_to_wishlist.php" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="wishlist-btn">❤️</button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="wishlist-btn">❤️</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function adjustQty(change) {
    const input = document.getElementById('qty');
    let value = parseInt(input.value) + change;
    if (value < 1) value = 1;
    input.value = value;
    document.getElementById('cart-qty').value = value;
}

function changeImage(src) {
    const mainImg = document.getElementById('main-img');
    mainImg.style.opacity = '0';
    
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 200);
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-img').forEach(thumb => {
        thumb.classList.remove('active');
        if (thumb.src === src) {
            thumb.classList.add('active');
        }
    });
}

function setCartValues() {
    const size = document.getElementById('size-select').value;
    const notes = document.getElementById('notes-text').value.trim();
    
    if (!size) {
        alert('Silakan pilih ukuran terlebih dahulu.');
        return false;
    }
    
    document.getElementById('cart-size').value = size;
    document.getElementById('cart-notes').value = notes;
    return true;
}

// Set first thumbnail as active on load
document.addEventListener('DOMContentLoaded', function() {
    const firstThumb = document.querySelector('.thumbnail-img');
    if (firstThumb) {
        firstThumb.classList.add('active');
    }
});
</script>

<style>
.product-detail {
    max-width: 1200px;
    margin: 80px auto 40px;
    padding: 20px;
}

.product-detail-container {
    display: flex;
    gap: 40px;
    align-items: flex-start;
}

.product-images {
    flex: 0 0 50%;
    text-align: center;
}

.product-main-img {
    width: 100%;
    height: 500px;
    object-fit: cover;
    object-position: center;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    transition: opacity 0.3s ease, transform 0.3s ease;
    cursor: zoom-in;
}

.product-main-img:hover {
    transform: scale(1.02);
}

.product-thumbnails {
    display: flex;
    gap: 12px;
    justify-content: flex-start;
    flex-wrap: wrap;
    padding: 0 5px;
}

.thumbnail-img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.3s ease;
    opacity: 0.7;
}

.thumbnail-img:hover {
    border-color: #d4a373;
    opacity: 1;
    transform: translateY(-2px);
}

.thumbnail-img.active {
    border-color: #d4a373;
    opacity: 1;
    box-shadow: 0 4px 12px rgba(212, 163, 115, 0.4);
}

.product-info-detail {
    flex: 1;
}

.product-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 10px;
}

.product-price {
    font-size: 1.8rem;
    color: #d4a373;
    margin-bottom: 20px;
    font-weight: 600;
}

.product-description {
    margin-bottom: 0px;
    line-height: 1.6;
    color: #555;
}

.product-specs {
    margin: 20px 0;
}

.spec-row {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.spec-label {
    font-weight: 600;
    color: #333;
    margin-right: 10px;
    width: 80px;
}

.spec-value {
    color: #555;
    font-size: 1.1rem;
}

.product-options {
    margin: 20px 0;
}

.option-group {
    margin-bottom: 15px;
}

.option-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.option-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}

.option-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-top: 5px;
    height: 120px;
    font-size: 0.95rem;
    resize: vertical;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0 20px;
}

.quantity-btn {
    width: 40px;
    height: 40px;
    background: #f9f7f3;
    border: none;
    border-radius: 8px;
    font-size: 1.2rem;
    cursor: pointer;
    transition: background 0.3s;
}

.quantity-btn:hover {
    background: #d4a373;
    color: white;
}

.quantity-input {
    width: 60px;
    text-align: center;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}

.product-actions {
    margin: 20px 0;
}

.btn-add-to-cart {
    display: inline-block;
    padding: 12px 25px;
    background: #d4a373;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background 0.3s;
    border: none;
    cursor: pointer;
}

.btn-add-to-cart:hover {
    background: #b58360;
}

.wishlist-btn-detail {
    display: inline-block;
    padding: 8px 16px;
    background: #333;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background 0.3s;
    margin-left: 10px;
    vertical-align: middle;
    line-height: 1;
    border: none;
    cursor: pointer;
}

.wishlist-btn-detail:hover {
    background: #555;
}

@media (max-width: 992px) {
    .product-detail-container {
        flex-direction: column;
        gap: 20px;
    }
    .product-images {
        flex: 0 0 100%;
    }
    .product-info-detail {
        flex: 1;
    }
    .product-main-img {
        height: 400px;
    }
}

@media (max-width: 768px) {
    .product-main-img {
        height: 300px;
    }
    .thumbnail-img {
        width: 70px;
        height: 70px;
    }
    .wishlist-btn-detail {
        margin-left: 0;
        margin-top: 10px;
    }
}
</style>

<style>
.dropdown-preorder {
    margin-top: 12px;
}

.dropdown-header {
    cursor: pointer;
    color: #d4a373;
    font-weight: 500;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown-content {
    display: none;
    margin-top: 8px;
    padding: 10px;
    background: #fafafa;
    border-radius: 6px;
    font-size: 14px;
}
</style>

<script>
function togglePreorder() {
    const content = document.getElementById("preorderContent");
    const arrow = document.getElementById("arrow");

    if (content.style.display === "none" || content.style.display === "") {
        content.style.display = "block";
        arrow.innerHTML = "▲";
    } else {
        content.style.display = "none";
        arrow.innerHTML = "▼";
    }
}
</script>
