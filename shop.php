<?php
include 'includes/header.php';
include 'config/db.php';

$category = $_GET['category'] ?? '';
$subcategory = $_GET['subcategory'] ?? '';

// ================= QUERY PRODUK =================
$sql = "SELECT id, name, price, image, category, subcategory FROM products";
$params = [];

if ($category && $subcategory) {
    $sql .= " WHERE category = ? AND subcategory = ?";
    $params = [$category, $subcategory];
} elseif ($category) {
    $sql .= " WHERE category = ?";
    $params = [$category];
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ================= LABEL KATEGORI =================
$labelKategori = [
    'men'    => 'Pria',
    'women'  => 'Wanita',
    'pants'  => 'Celana',
    'oneset' => 'One Set'
];

// ================= LABEL SUBKATEGORI =================
$subKategoriMap = [
    'men' => [
        'kemeja_tenun' => 'Kemeja Tenun',
        'outer_pria'   => 'Outer Pria',
        'celana_pria'  => 'Celana Pria',
        'hem_lukis'    => 'Hem Lukis',
    ],
    'women' => [
        'blouse'        => 'Blouse',
        'dress'         => 'Dress',
        'gamis'         => 'Gamis',
        'kemeja_wanita' => 'Kemeja Wanita',
    ]
];

// ================= JUDUL HALAMAN =================
if ($category && $subcategory) {
    $judul = ($subKategoriMap[$category][$subcategory] ?? ucfirst($subcategory))
           . ' – '
           . ($labelKategori[$category] ?? ucfirst($category));
} elseif ($category) {
    $judul = $labelKategori[$category] ?? ucfirst($category);
} else {
    $judul = 'Semua Produk';
}
?>

<h1 class="page-title"><?= htmlspecialchars($judul) ?></h1>

<!-- ================= FILTER KATEGORI UTAMA ================= -->
<div class="category-filter">
    <a href="shop.php" class="filter-btn <?= !$category ? 'active' : '' ?>">Semua</a>
    <a href="shop.php?category=men" class="filter-btn <?= $category === 'men' ? 'active' : '' ?>">Pria</a>
    <a href="shop.php?category=women" class="filter-btn <?= $category === 'women' ? 'active' : '' ?>">Wanita</a>
    <a href="shop.php?category=pants" class="filter-btn <?= $category === 'pants' ? 'active' : '' ?>">Celana</a>
    <a href="shop.php?category=oneset" class="filter-btn <?= $category === 'oneset' ? 'active' : '' ?>">One Set</a>
</div>

<!-- ================= SUB KATEGORI (HANYA JIKA ADA) ================= -->
<?php if (isset($subKategoriMap[$category])): ?>
<div class="subcategory-filter">
    <?php foreach ($subKategoriMap[$category] as $key => $label): ?>
        <a href="shop.php?category=<?= $category ?>&subcategory=<?= $key ?>"
           class="<?= $subcategory === $key ? 'active' : '' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ================= PRODUK ================= -->
<section class="product-grid-full">
    <?php if (empty($products)): ?>
        <p class="no-product">Tidak ada produk untuk kategori ini.</p>
    <?php else: ?>
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="assets/img/<?= htmlspecialchars($p['image']) ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>"
                         onerror="this.src='assets/img/placeholder.png'">
                </a>
                <div class="product-info">
                    <h3><?= htmlspecialchars($p['name']) ?></h3>
                    <p>Rp <?= number_format((int)$p['price'], 0, ',', '.') ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php
include 'includes/header.php';
include 'config/db.php';

$category = $_GET['category'] ?? '';
$subcategory = $_GET['subcategory'] ?? '';

// ================= QUERY PRODUK (DENGAN STOCK) =================
$sql = "SELECT id, name, price, image, category, subcategory, stock, is_sale, sale_price FROM products";
$params = [];

if ($category && $subcategory) {
    $sql .= " WHERE category = ? AND subcategory = ?";
    $params = [$category, $subcategory];
} elseif ($category) {
    $sql .= " WHERE category = ?";
    $params = [$category];
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ================= LABEL KATEGORI =================
$labelKategori = [
    'men'    => 'Pria',
    'women'  => 'Wanita',
    'pants'  => 'Celana',
    'oneset' => 'One Set'
];

// ================= LABEL SUBKATEGORI =================
$subKategoriMap = [
    'men' => [
        'kemeja_tenun' => 'Kemeja Tenun',
        'outer_pria'   => 'Outer Pria',
        'celana_pria'  => 'Celana Pria',
        'hem_lukis'    => 'Hem Lukis',
    ],
    'women' => [
        'blouse'        => 'Blouse',
        'dress'         => 'Dress',
        'gamis'         => 'Gamis',
        'kemeja_wanita' => 'Kemeja Wanita',
    ]
];

// ================= JUDUL HALAMAN =================
if ($category && $subcategory) {
    $judul = ($subKategoriMap[$category][$subcategory] ?? ucfirst($subcategory))
           . ' – '
           . ($labelKategori[$category] ?? ucfirst($category));
} elseif ($category) {
    $judul = $labelKategori[$category] ?? ucfirst($category);
} else {
    $judul = 'Semua Produk';
}
?>

<style>
/* Stock Badge Styles */
.stock-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stock-available {
    background: #d4edda;
    color: #155724;
}

.stock-low {
    background: #fff3cd;
    color: #856404;
}

.stock-out {
    background: #f8d7da;
    color: #721c24;
}

/* Sale Badge */
.sale-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 2;
    text-transform: uppercase;
}

/* Product Card Enhancement */
.product-card {
    position: relative;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.product-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    transform: translateY(-5px);
}

.product-card.out-of-stock {
    opacity: 0.7;
}

.product-card.out-of-stock img {
    filter: grayscale(50%);
}

.product-info {
    padding: 15px;
}

.product-info h3 {
    font-size: 1rem;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 600;
}

.price-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.original-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
}

.sale-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #dc3545;
}

.crossed-price {
    font-size: 0.9rem;
    text-decoration: line-through;
    color: #6c757d;
}

.stock-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
}

.stock-count {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Out of Stock Overlay */
.out-of-stock-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 1rem;
    z-index: 3;
    text-transform: uppercase;
    letter-spacing: 1px;
}
</style>

<h1 class="page-title"><?= htmlspecialchars($judul) ?></h1>

<!-- ================= FILTER KATEGORI UTAMA ================= -->
<div class="category-filter">
    <a href="shop.php" class="filter-btn <?= !$category ? 'active' : '' ?>">Semua</a>
    <a href="shop.php?category=men" class="filter-btn <?= $category === 'men' ? 'active' : '' ?>">Pria</a>
    <a href="shop.php?category=women" class="filter-btn <?= $category === 'women' ? 'active' : '' ?>">Wanita</a>
    <a href="shop.php?category=pants" class="filter-btn <?= $category === 'pants' ? 'active' : '' ?>">Celana</a>
    <a href="shop.php?category=oneset" class="filter-btn <?= $category === 'oneset' ? 'active' : '' ?>">One Set</a>
</div>

<!-- ================= SUB KATEGORI (HANYA JIKA ADA) ================= -->
<?php if (isset($subKategoriMap[$category])): ?>
<div class="subcategory-filter">
    <?php foreach ($subKategoriMap[$category] as $key => $label): ?>
        <a href="shop.php?category=<?= $category ?>&subcategory=<?= $key ?>"
           class="<?= $subcategory === $key ? 'active' : '' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ================= PRODUK ================= -->
<section class="product-grid-full">
    <?php if (empty($products)): ?>
        <p class="no-product">Tidak ada produk untuk kategori ini.</p>
    <?php else: ?>
        <?php foreach ($products as $p): 
            $stock = (int)$p['stock'];
            $is_sale = (bool)$p['is_sale'];
            $sale_price = $p['sale_price'] ? (float)$p['sale_price'] : null;
            $original_price = (float)$p['price'];
            
            // Tentukan status stok
            if ($stock == 0) {
                $stock_class = 'stock-out';
                $stock_label = 'Habis';
            } elseif ($stock <= 5) {
                $stock_class = 'stock-low';
                $stock_label = 'Stok Menipis';
            } else {
                $stock_class = 'stock-available';
                $stock_label = 'Tersedia';
            }
        ?>
            <div class="product-card <?= $stock == 0 ? 'out-of-stock' : '' ?>">
                <?php if ($is_sale && $sale_price): ?>
                    <span class="sale-badge">Sale</span>
                <?php endif; ?>
                
                <?php if ($stock == 0): ?>
                    <div class="out-of-stock-overlay">Stok Habis</div>
                <?php endif; ?>
                
                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="assets/img/<?= htmlspecialchars($p['image']) ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>"
                         onerror="this.src='assets/img/placeholder.png'">
                </a>
                
                <div class="product-info">
                    <h3><?= htmlspecialchars($p['name']) ?></h3>
                    
                    <div class="price-section">
                        <?php if ($is_sale && $sale_price): ?>
                            <span class="sale-price">Rp <?= number_format($sale_price, 0, ',', '.') ?></span>
                            <span class="crossed-price">Rp <?= number_format($original_price, 0, ',', '.') ?></span>
                        <?php else: ?>
                            <span class="original-price">Rp <?= number_format($original_price, 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="stock-info">
                        <span class="stock-badge <?= $stock_class ?>">
                            <?= $stock_label ?>
                        </span>
                        <?php if ($stock > 0): ?>
                            <span class="stock-count">Stok: <?= $stock ?> pcs</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>