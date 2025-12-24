<?php
include 'includes/header.php';

// Ambil produk yang:
// - is_sale = 1
// - DAN (sale_start <= hari ini ATAU sale_start NULL)
// - DAN (sale_end >= hari ini ATAU sale_end NULL)
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE is_sale = 1
      AND (? >= COALESCE(sale_start, ?) OR sale_start IS NULL)
      AND (? <= COALESCE(sale_end, ?) OR sale_end IS NULL)
");
$stmt->execute([$today, $today, $today, $today]);
$sale_products = $stmt->fetchAll();
?>

<!-- Banner Sale Khusus -->
<div class="sale-banner">
    <h1>BLACK AND WHITE SERIES </h1>
    <p>Up to 20% Off — Limited Time Only!</p>
</div>

<!-- Produk Sale -->
<section class="product-grid-full">
    <?php if (empty($sale_products)): ?>
        <p class="no-product">Tidak ada produk dalam promo saat ini.</p>
    <?php else: ?>
        <?php foreach ($sale_products as $product): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $product['id'] ?>">
                    <img src="assets/img/<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                </a>
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p>
                        <span class="price-old">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                        <span class="price-new">Rp <?= number_format($product['sale_price'], 0, ',', '.') ?></span>
                    </p>
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