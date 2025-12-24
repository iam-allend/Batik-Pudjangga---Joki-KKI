<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    
    // Handle upload foto
    $profile_image = $user['profile_image']; // pertahankan foto lama
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "assets/img/profiles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = "profile_" . $_SESSION['user_id'] . "_" . uniqid() . "_" . basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($imageFileType, $allowed) && move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_image = $file_name;
        }
    }

    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, phone = ?, address = ?, city = ?, postal_code = ?, profile_image = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $phone, $address, $city, $postal_code, $profile_image, $_SESSION['user_id']]);
    header("Location: profile.php?updated=1");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="profile-header">
    <h1>Edit Profile</h1>
</div>

<div class="profile-section">
    <form method="POST" enctype="multipart/form-data">
        <!-- Foto Profil dengan Icon -->
        <div class="form-group profile-picture-group">
            <label>Foto Profil</label>
            <div class="profile-picture-preview">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="assets/img/profiles/<?= htmlspecialchars($user['profile_image']) ?>" 
                         alt="Foto Profil" id="profile-preview">
                <?php else: ?>
                    <img src="assets/img/ceye.jpeg" alt="Foto Profil" id="profile-preview">
                <?php endif; ?>
                <div class="profile-picture-overlay">
                    <span>📷</span>
                </div>
                <input type="file" name="profile_image" id="profile-input" accept="image/*">
            </div>
        </div>

        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        </div>
        <div class="form-group">
            <label>Telepon</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="address" rows="4"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Kota</label>
            <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Kode Pos</label>
            <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>">
        </div>
        <button type="submit" class="btn-save">Simpan Perubahan</button>
        <a href="profile.php" class="btn-cancel">Batal</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<style>
/* EDIT PROFILE - FOTO PROFIL */
.profile-picture-group {
    margin-bottom: 30px;
}
.profile-picture-group label {
    display: block;
    margin-bottom: 15px;
    font-weight: 600;
    color: #333;
}

.profile-picture-preview {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    background: #f9f7f3;
    border: 2px solid #d4a373;
}
.profile-picture-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile-picture-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(212, 163, 115, 0.7);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    opacity: 0;
    transition: opacity 0.3s;
}
.profile-picture-preview:hover .profile-picture-overlay {
    opacity: 1;
}
#profile-input {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
}

/* FORM GROUP */
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}
.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

/* BUTTONS */
.btn-save {
    background: #d4a373;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 6px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}
.btn-save:hover {
    background: #b58360;
}
.btn-cancel {
    display: inline-block;
    background: #6c757d;
    color: white;
    text-decoration: none;
    padding: 12px 25px;
    border-radius: 6px;
    font-weight: 500;
    margin-left: 15px;
}
.btn-cancel:hover {
    background: #5a6268;
}
</style>

<script>
// Preview foto saat dipilih
document.getElementById('profile-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>