<?php
include 'includes/header.php';
include 'config/db.php';


// Ambil produk yang termasuk koleksi baru
// Bisa pakai flag is_new atau created_at terbaru
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE is_new = 1 
    ORDER BY id DESC 
    LIMIT 12
");
$stmt->execute();
$new_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="new-collection-full-header">
    <h1>New Collection</h1>
    <p>Temukan koleksi terbaru kami yang eksklusif dan stylish.</p>
</div>

<section class="product-grid-full">
    <?php if (empty($new_products)): ?>
        <p class="no-product">Belum ada produk baru.</p>
    <?php else: ?>
        <?php foreach ($new_products as $product): ?>
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
