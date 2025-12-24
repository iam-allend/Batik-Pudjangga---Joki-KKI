<?php
session_start();
include 'config/db.php'; // <-- KONEKSI DATABASE
include 'includes/header.php';
?>

<!-- Banner New Year Sale -->
<div class="banner-sale">
    <a href="sale.php">
        <img src="assets/img/sale.png" alt="New Year Sale" class="small-banner">
    </a>
</div>

<!-- Category Product -->
<section class="category-section">
    <h2>KATEGORI PRODUK</h2>
    <div class="category-grid">
        <a href="shop.php?category=men" class="category-item">
            <img src="assets/img/men.png" alt="Men">
            <span>Pria</span>
        </a>
        <a href="shop.php?category=women" class="category-item">
            <img src="assets/img/women.png" alt="Women">
            <span>Wanita</span>
        </a>
        <a href="shop.php?category=pants" class="category-item">
            <img src="assets/img/pants.png" alt="Pants">
            <span>Celana</span>
        </a>
        <a href="shop.php?category=oneset" class="category-item">
            <img src="assets/img/oneset_rok_lukis.png" alt="Oneset">
            <span>One Set</span>
        </a>
    </div>
</section>

<!-- New Collection -->
<section class="new-collection">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Koleksi Terbaru</h2>
        <a href="/batik_pudjangga/new_collection.php" class="btn-view-all">Lihat Semua →</a>
    </div>
    <div class="product-grid">
        <?php
        // Ambil 3 produk terbaru yang is_new = 1
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_new = 1 ORDER BY id DESC LIMIT 3");
        $stmt->execute();
        $new_products = $stmt->fetchAll();

        if (empty($new_products)) {
            echo '<p class="no-product">Belum ada koleksi baru.</p>';
        } else {
            foreach ($new_products as $product) {
                echo '
                <div class="product-card">
                    <a href="product.php?id=' . $product['id'] . '">
                        <img src="assets/img/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">
                    </a>
                    <div class="product-info">
                        <h3>' . htmlspecialchars($product['name']) . '</h3>
                        <p>Rp ' . number_format($product['price'], 0, ',', '.') . '</p>
                        ' . (isset($_SESSION['user_id']) ? '
                        <form method="POST" action="add_to_wishlist.php" style="display:inline;">
                            <input type="hidden" name="product_id" value="' . $product['id'] . '">
                            <button type="submit" class="wishlist-btn">❤️</button>
                        </form>
                        ' : '
                        <a href="login.php" class="wishlist-btn">❤️</a>
                        ') . '
                    </div>
                </div>';
            }
        }
        ?>
    </div>
</section>

<?php
include 'includes/footer.php';
?>