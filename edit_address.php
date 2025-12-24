<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$address_id = $_GET['id'] ?? null;

// Validasi ID
if (!$address_id) {
    die("Alamat tidak ditemukan.");
}

// Ambil data alamat yang mau diedit
$stmt = $pdo->prepare("SELECT * FROM address WHERE id = ? AND user_id = ?");
$stmt->execute([$address_id, $user_id]);
$address = $stmt->fetch();

if (!$address) {
    die("Alamat tidak ditemukan atau bukan milik Anda.");
}
?>

<div class="profile-header">
    <h1>Edit Alamat</h1>
</div>

<div class="profile-section">
    <div class="profile-card">
        <form id="edit-address-form" onsubmit="saveEditedAddress(event, <?= $address_id ?>)">
            <div class="form-group">
                <label>Nama Penerima *</label>
                <input type="text" id="edit-recipient_name" value="<?= htmlspecialchars($address['recipient_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Alamat Lengkap *</label>
                <textarea id="edit-address" required><?= htmlspecialchars($address['address']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Kota *</label>
                <input type="text" id="edit-city" value="<?= htmlspecialchars($address['city']) ?>" required>
            </div>
            <div class="form-group">
                <label>Provinsi *</label>
                <select id="edit-province" required>
                    <option value="">Pilih Provinsi</option>
                    <?php
                    $stmt = $pdo->query("SELECT DISTINCT province FROM shipping_zones ORDER BY province");
                    while ($row = $stmt->fetch()) {
                        $selected = ($row['province'] == $address['province']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['province']) . '" ' . $selected . '>' . htmlspecialchars($row['province']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Kode Pos *</label>
                <input type="text" id="edit-postal_code" value="<?= htmlspecialchars($address['postal_code']) ?>" required>
            </div>
            <div class="form-group">
                <label>Telepon *</label>
                <input type="text" id="edit-phone" value="<?= htmlspecialchars($address['phone']) ?>" required>
            </div>

            <button type="submit" class="btn-save">Simpan Perubahan</button>
            <a href="my-address.php" class="btn-cancel">Batal</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function saveEditedAddress(e, addressId) {
    e.preventDefault();
    
    const data = {
        recipient_name: document.getElementById('edit-recipient_name').value,
        address: document.getElementById('edit-address').value,
        city: document.getElementById('edit-city').value,
        province: document.getElementById('edit-province').value,
        postal_code: document.getElementById('edit-postal_code').value,
        phone: document.getElementById('edit-phone').value
    };

    fetch(`update_address.php?id=${addressId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            alert('Alamat berhasil diperbarui!');
            window.location.href = 'my-address.php';
        } else {
            alert('Gagal memperbarui alamat: ' + (res.message || 'Error tidak diketahui'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menyimpan.');
    });
}
</script>

<style>
.profile-card {
    max-width: 600px;
    margin: 0 auto;
    padding: 30px;
}

.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

.btn-save {
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    margin-right: 10px;
}
.btn-save:hover {
    background: #218838;
}

.btn-cancel {
    display: inline-block;
    padding: 12px 24px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 16px;
}
.btn-cancel:hover {
    background: #5a6268;
}
</style>