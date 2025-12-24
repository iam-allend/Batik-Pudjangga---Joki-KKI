<?php
session_start();
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user (termasuk profile_image)
$stmt = $pdo->prepare("SELECT name, email, profile_image, phone, address, city, postal_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}
?>

<!-- Profile Header -->
<div class="profile-header">
    <h1>Profile Saya</h1>
</div>

<!-- Profile Content -->
<section class="profile-section">
    <div class="profile-card">
        <!-- Foto Profil & Nama -->
        <div class="profile-avatar">
            <?php if (!empty($user['profile_image'])): ?>
                <img src="assets/img/profiles/<?= htmlspecialchars($user['profile_image']) ?>" 
                     alt="Profile Picture" class="avatar-img">
            <?php else: ?>
                <img src="assets/img/ceye.jpeg" alt="Foto Profil" class="avatar-img">
            <?php endif; ?>
            <button class="avatar-edit" onclick="location.href='edit_profile.php'">📷</button>
        </div>
        <div class="profile-name">
            <?= htmlspecialchars($user['name'] ?? 'User') ?>
        </div>

        <!-- Menu -->
        <div class="profile-menu">
            <a href="edit_profile.php" class="menu-item">
                <span>👤</span>
                <span>Profile Saya</span>
                <span>→</span>
            </a>
            <a href="wishlist.php" class="menu-item">
                <span>❤️</span>
                <span>Favorite</span>
                <span>→</span>
            </a>
            <a href="orders.php" class="menu-item">
                <span>📦</span>
                <span>Riwayat Pemesanan</span>
                <span>→</span>
            </a>
            <a href="my_address.php" class="menu-item">
                <span>📍</span>
                <span>Alamat</span>
                <span>→</span>
            </a>
            <a href="contact.php" class="menu-item">
                <span>📞</span>
                <span>Kontak</span>
                <span>→</span>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
/* PROFILE PAGE - DESAIN GAMBAR */
.profile-header {
    text-align: center;
    padding: 60px 20px;
    background-color: #f5f0e6;
    margin-top: 80px;
}
.profile-header h1 {
    font-size: 2.2rem;
    color: #333;
    margin: 0;
}

.profile-section {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.profile-card {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    text-align: center;
}

/* FOTO PROFIL */
.profile-avatar {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
    background: #f9f7f3;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.avatar-edit {
    position: absolute;
    bottom: -5px;
    right: -5px;
    width: 30px;
    height: 30px;
    background: #d4a373;
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-edit:hover {
    background: #b58360;
}

/* NAMA */
.profile-name {
    font-size: 1.4rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 30px;
}

/* MENU */
.profile-menu {
    margin: 30px 0;
}
.menu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    background: #f9f7f3;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: background 0.2s;
}
.menu-item:hover {
    background: #f0e9e1;
}
.menu-item span:first-child {
    font-size: 1.2rem;
    margin-right: 10px;
}
.menu-item span:last-child {
    font-size: 1.1rem;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .profile-card {
        padding: 20px;
    }
    .profile-name {
        font-size: 1.2rem;
    }
    .menu-item {
        padding: 12px 15px;
        font-size: 0.95rem;
    }
}
</style>