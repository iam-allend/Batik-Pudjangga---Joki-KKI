<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Ambil input pencarian
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if (!empty($query)) {
    // Query pencarian di seluruh produk
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE name LIKE :search OR category LIKE :search
        ORDER BY id DESC
    ");
    $stmt->execute(['search' => "%$query%"]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="page-title">
    <h1>
        <?php if (!empty($query)): ?>
            Hasil Pencarian: "<?= htmlspecialchars($query) ?>"
        <?php else: ?>
            Cari Produk
        <?php endif; ?>
    </h1>
</div>

<!-- Search Bar -->
<div style="max-width: 600px; margin: 0 auto 30px;">
    <form method="GET" action="search.php" style="display:flex; gap:10px;">
        <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
               placeholder="Cari produk..." style="flex:1; padding:12px; border:1px solid #ddd; border-radius:6px;">
        <button type="submit" style="padding:12px 20px; background:#d4a373; color:white; border:none; border-radius:6px; font-weight:600;">Cari</button>
    </form>
</div>

<section class="product-grid">
    <?php if (empty($query)): ?>
        <p class="no-product">Masukkan kata kunci untuk mencari produk.</p>
    <?php elseif (empty($products)): ?>
        <p class="no-product">Produk tidak ditemukan untuk "<?= htmlspecialchars($query) ?>".</p>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <img src="assets/img/<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                </a>
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <?php if (!empty($product['is_sale']) && !empty($product['sale_price'])): ?>
                        <p>
                            <span class="price-old">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <span class="price-new">Rp <?= number_format($product['sale_price'], 0, ',', '.') ?></span>
                        </p>
                    <?php else: ?>
                        <p>Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" action="add_to_wishlist.php" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" class="wishlist-btn">❤️</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="wishlist-btn">❤️</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>