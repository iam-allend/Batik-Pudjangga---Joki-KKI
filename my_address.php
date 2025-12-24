<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil semua alamat pengguna
$stmt = $pdo->prepare("SELECT * FROM address WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();
?>

<div class="profile-header">
    <h1>Daftar Alamat</h1>
</div>

<div class="profile-section">
    <div class="profile-card">
        <button class="btn-add-address" onclick="showNewAddressForm()">+ Tambah Alamat Baru</button>

        <!-- Form Tambah Alamat -->
        <div id="new-address-form" style="display:none; margin-top: 20px; padding: 20px; background: #f9f7f3; border-radius: 8px;">
            <h3>Tambah Alamat Baru</h3>
            <form id="add-address-form">
                <div class="form-group">
                    <label>Nama Penerima *</label>
                    <input type="text" id="new-recipient_name" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap *</label>
                    <textarea id="new-address" required></textarea>
                </div>
                <div class="form-group">
                    <label>Kota *</label>
                    <input type="text" id="new-city" required>
                </div>
                <div class="form-group">
                    <label>Provinsi *</label>
                    <select id="new-province" required>
                        <option value="">Pilih Provinsi</option>
                        <?php
                        $stmt = $pdo->query("SELECT DISTINCT province FROM shipping_zones ORDER BY province");
                        while ($row = $stmt->fetch()) {
                            echo '<option value="' . htmlspecialchars($row['province']) . '">' . htmlspecialchars($row['province']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kode Pos *</label>
                    <input type="text" id="new-postal_code" required>
                </div>
                <div class="form-group">
                    <label>Telepon *</label>
                    <input type="text" id="new-phone" required>
                </div>
                <button type="button" onclick="saveNewAddress()" class="btn-save">Simpan Alamat</button>
                <button type="button" onclick="hideNewAddressForm()" style="margin-left:10px;">Batal</button>
            </form>
        </div>

        <!-- Daftar Alamat -->
        <div class="address-list" style="margin-top: 30px;">
            <?php if ($addresses): ?>
                <?php foreach ($addresses as $addr): ?>
                    <div class="address-item <?= $addr['is_default'] ? 'default' : '' ?>">
                        <div class="address-info">
                            <strong><?= htmlspecialchars($addr['recipient_name']) ?></strong><br>
                            <?= htmlspecialchars($addr['address']) ?><br>
                            <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['province']) ?> <?= htmlspecialchars($addr['postal_code']) ?><br>
                            Telp: <?= htmlspecialchars($addr['phone']) ?>
                            <?php if ($addr['is_default']): ?>
                                <span class="badge-default">Utama</span>
                            <?php endif; ?>
                        </div>
                        <div class="address-actions">
                            <button type="button" onclick="editAddress(<?= $addr['id'] ?>)">Ubah</button>
                            <?php if (!$addr['is_default']): ?>
                                <button type="button" onclick="setAsDefault(<?= $addr['id'] ?>)">Jadikan Utama</button>
                            <?php endif; ?>
                            <button type="button" style="background:#dc3545;" onclick="deleteAddress(<?= $addr['id'] ?>)">Hapus</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Belum ada alamat. Silakan tambahkan alamat pengiriman.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function showNewAddressForm() {
    document.getElementById('new-address-form').style.display = 'block';
}

function hideNewAddressForm() {
    document.getElementById('new-address-form').style.display = 'none';
}

function saveNewAddress() {
    const data = {
        recipient_name: document.getElementById('new-recipient_name').value,
        address: document.getElementById('new-address').value,
        city: document.getElementById('new-city').value,
        province: document.getElementById('new-province').value,
        postal_code: document.getElementById('new-postal_code').value,
        phone: document.getElementById('new-phone').value
    };

    fetch('save_address.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            alert('Alamat berhasil disimpan!');
            location.reload();
        } else {
            alert('Gagal: ' + (res.message || 'Terjadi kesalahan'));
        }
    })
    .catch(err => {
        alert('Gagal menyimpan alamat. Cek koneksi internet.');
    });
}

function setAsDefault(id) {
    if (confirm('Jadikan alamat ini sebagai alamat utama?')) {
        fetch(`set_default_address.php?id=${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                location.reload();
            } else {
                alert('Gagal mengatur alamat utama.');
            }
        });
    }
}

function deleteAddress(id) {
    if (confirm('Yakin ingin menghapus alamat ini?')) {
        fetch(`delete_address.php?id=${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                location.reload();
            } else {
                alert('Gagal menghapus alamat.');
            }
        });
    }
}

function editAddress(id) {
    window.location.href = 'edit_address.php?id=' + id;
}
</script>

<style>
.btn-add-address {
    background: #d4a373;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-bottom: 20px;
}
.btn-add-address:hover {
    background: #b58360;
}

.address-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 15px;
    background: #f9f7f3;
    border-radius: 8px;
    margin-bottom: 15px;
    position: relative;
}

.address-info {
    flex: 1;
    font-size: 14px;
    line-height: 1.5;
}

.badge-default {
    background: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    margin-left: 8px;
}

.address-actions button {
    margin-left: 8px;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    background: #007bff;
    color: white;
    cursor: pointer;
    font-size: 12px;
}
.address-actions button:hover {
    opacity: 0.9;
}

.form-group {
    margin-bottom: 12px;
}
.form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: bold;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.btn-save {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}
</style>