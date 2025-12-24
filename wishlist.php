<?php
session_start();
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT w.id as wishlist_id, p.id as product_id, p.name, p.price, p.image
    FROM wishlists w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();
?>

<div class="wishlist-header">
    <div class="wishlist-header-top">
        <h1>Wishlist</h1>
    </div>
    <div class="wishlist-search">
        <input type="text" placeholder="Cari di Wishlist" id="search-wishlist" onkeyup="filterWishlist()">
    </div>
</div>

<section class="wishlist-section">
    <?php if (empty($wishlist_items)): ?>
        <p style="text-align: center; font-size: 1.2rem; color: #666; margin: 40px 0;">Wishlist-mu kosong.</p>
        <div style="text-align: center;">
            <a href="shop.php" class="btn-back">Lihat Produk</a>
        </div>
    <?php else: ?>
        <div class="wishlist-cards">
            <?php foreach ($wishlist_items as $item): ?>
                <div class="wishlist-card" data-name="<?= htmlspecialchars(strtolower($item['name'])) ?>">
                    
                    <div class="wishlist-card-img">
                        <img src="assets/img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>

                    <div class="wishlist-card-info">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="price">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>

                        <div class="wishlist-actions">
                            
                            <!-- FORM ADD TO CART -->
                            <form method="POST" action="add_to_cart.php" 
                                  onsubmit="return validateWishlistSize(this)" 
                                  style="flex: 1; margin-right: 10px;">
                                  
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="hidden" name="quantity" value="1">

                                <!-- Select Ukuran -->
                                <select name="size" class="wishlist-size" 
                                        style="width:100%; margin-bottom:8px; padding:6px;">
                                    <option value="">Pilih Ukuran</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL (+Rp15.000)</option>
                                </select>

                                <!-- Untuk hapus otomatis dari wishlist -->
                                <input type="hidden" name="from_wishlist" value="<?= $item['wishlist_id'] ?>">

                                <button type="submit" class="btn-add-to-cart">+ Keranjang</button>
                            </form>

                            <!-- BUTTON REMOVE -->
                            <form method="POST" action="remove_from_wishlist.php" style="margin: 0;">
                                <input type="hidden" name="wishlist_id" value="<?= $item['wishlist_id'] ?>">
                                <button type="submit" class="btn-remove-wishlist">🗑️</button>
                            </form>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function filterWishlist() {
    const input = document.getElementById('search-wishlist');
    const filter = input.value.toLowerCase();
    const cards = document.querySelectorAll('.wishlist-card');

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        card.style.display = name.includes(filter) ? 'flex' : 'none';
    });
}

// 🚨 Validasi ukuran wajib dipilih
function validateWishlistSize(form) {
    let size = form.querySelector("select[name='size']").value;
    if (size === "") {
        alert("Silakan pilih ukuran dulu sebelum masuk keranjang ❤️");
        return false;
    }
    return true;
}
</script>
