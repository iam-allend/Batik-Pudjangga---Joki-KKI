<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CARI FILE db.php SECARA OTOMATIS
$possible_paths = [
    'config/db.php',
    '../config/db.php',
    __DIR__ . '/../config/db.php'
];

$db_loaded = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        include $path;
        $db_loaded = true;
        break;
    }
}

if (!$db_loaded) {
    die("Error: File koneksi database tidak ditemukan. Pastikan folder 'config' ada di root project.");
}

$wishlist_count = 0;
$cart_count = 0;

if (isset($pdo) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $wishlist_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetchColumn() ?: 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pudjangga Batik</title>
    <base href="/batik_pudjangga/">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/img/logo_pj.png" alt="Pudjangga Batik">
                </a>
            </div>

            <ul class="nav-menu">
                <li>
                    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        Beranda
                    </a>
                </li>

                <!-- BELANJA DROPDOWN DENGAN SUBKATEGORI -->
                <li class="nav-dropdown">
                    <a href="shop.php" class="<?= basename($_SERVER['PHP_SELF']) == 'shop.php' ? 'active' : '' ?>">
                        Belanja
                    </a>
                    <ul class="dropdown-menu">
                        <!-- Kategori Utama (Link Langsung) -->
                        <li><a href="shop.php?category=men">Pria</a></li>
                        <li><a href="shop.php?category=women">Wanita</a></li>
                        <li><a href="shop.php?category=pants">Celana</a></li>
                        <li><a href="shop.php?category=oneset">One Set</a></li>

                        <!-- Subkategori Pria -->
                        <li class="dropdown-submenu">
                            <a>Pria &raquo;</a>
                            <ul class="dropdown-submenu-content">
                                <li><a href="shop.php?category=men&subcategory=kemeja_tenun">Kemeja Tenun</a></li>
                                <li><a href="shop.php?category=men&subcategory=outer_pria">Outer Pria</a></li>
                                <li><a href="shop.php?category=men&subcategory=celana_pria">Celana Pria</a></li>
                                <li><a href="shop.php?category=men&subcategory=hem_lukis">Hem Lukis</a></li>
                            </ul>
                        </li>

                        <!-- Subkategori Wanita -->
                        <li class="dropdown-submenu">
                            <a>Wanita &raquo;</a>
                            <ul class="dropdown-submenu-content">
                                <li><a href="shop.php?category=women&subcategory=blouse">Blouse</a></li>
                                <li><a href="shop.php?category=women&subcategory=dress">Dress</a></li>
                                <li><a href="shop.php?category=women&subcategory=gamis">Gamis</a></li>
                                <li><a href="shop.php?category=women&subcategory=kemeja_wanita">Kemeja Wanita</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="about.php" class="<?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">
                        Tentang Kami
                    </a>
                </li>
                <li>
                    <a href="contact.php" class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">
                        Kontak
                    </a>
                </li>
            </ul>

            <div class="nav-icons">
                <a href="search.php"><img src="assets/img/search.png" alt="Search"></a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="wishlist.php" class="nav-icon-badge">
                        <img src="assets/img/heart.png" alt="Wishlist">
                        <?php if ($wishlist_count > 0): ?>
                            <span class="badge"><?= $wishlist_count ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="cart.php" class="nav-icon-badge">
                        <img src="assets/img/cart.png" alt="Cart">
                        <?php if ($cart_count > 0): ?>
                            <span class="badge"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="user-dropdown">
                        <img src="assets/img/user.png" class="user-icon" alt="User">
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                Halo, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>!
                            </div>
                            <a href="profile.php">Profile</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php"><img src="assets/img/heart.png"></a>
                    <a href="login.php"><img src="assets/img/cart.png"></a>
                    <a href="login.php"><img src="assets/img/user.png"></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="main-content">