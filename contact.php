<?php
session_start();
include 'config/db.php'; // Pastikan path ini benar
include 'includes/header.php';

// Proses form jika dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validasi server-side
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        try {
            // Simpan ke database
            $stmt = $pdo->prepare("INSERT INTO report_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);

            // Set sukses ke session (untuk notif setelah redirect)
            $_SESSION['contact_success'] = true;
            header("Location: contact.php?sent=1");
            exit;
        } catch (Exception $e) {
            $error = "Gagal mengirim pesan. Silakan coba lagi.";
            // Untuk debug: echo $e->getMessage();
        }
    }
}

// Cek apakah ada notifikasi sukses dari redirect
$show_success = isset($_SESSION['contact_success']) && $_SESSION['contact_success'];
if ($show_success) {
    unset($_SESSION['contact_success']);
}
?>

<div class="contact-header">
    <h1>KONTAK KAMI</h1>
</div>

<section class="contact-section">
    <!-- ✅ NOTIFIKASI -->
    <?php if ($show_success): ?>
        <div id="success-alert" class="alert alert-success">
            ✅ Pesan Anda telah terkirim!
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div id="error-alert" class="alert alert-error">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="contact-form">
        <h2>Send Us a Message</h2>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Nama Anda" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            <input type="email" name="email" placeholder="Alamat Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <textarea name="message" placeholder="Pesan Anda" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            <button type="submit" class="btn-submit">Kirim Pesan</button>
        </form>
    </div>

    <div class="contact-info">
        <h2>Informasi Kita</h2>
        <div class="info-item">
            <strong>📍 Alamat:</strong>
            <p>Perumahan Binagriya Jl. Cengkeh no 11 Blok A, Kota Pekalongan, Jawa Tengah</p>
        </div>
        <div class="info-item">
            <strong>📞 Telfon:</strong>
            <p>+62 859-3043-3717</p>
        </div>
        <div class="info-item">
            <strong>✉️ Email:</strong>
            <p>faradisaolshop1@gmail.com</p>
        </div>
        <div class="info-item">
            <strong>🕒 Hours:</strong>
            <p>Senin - Kamis: 09.00 – 16.00 WIB</p>
            <p>Sabtu - Minggu: 09.00 – 16.00 WIB</p>
            <p>Jumat: Tutup</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- ✅ JavaScript untuk fade-out notifikasi -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('success-alert');
    const errorAlert = document.getElementById('error-alert');

    if (successAlert) {
        // Fade out & hide setelah 3 detik
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.3s';
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.style.display = 'none', 500);
        }, 3000);
    }

    if (errorAlert) {
        setTimeout(() => {
            errorAlert.style.transition = 'opacity 0.3s';
            errorAlert.style.opacity = '0';
            setTimeout(() => errorAlert.style.display = 'none', 500);
        }, 5000);
    }
});
</script>

<style>
/* === NOTIFIKASI === */
.alert {
    padding: 14px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-weight: 600;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* === CONTACT SECTION === */
.contact-section {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.contact-form,
.contact-info {
    background: white;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid #eaeaea;
}

.contact-form h2,
.contact-info h2 {
    font-size: 1.5rem;
    color: #2b2b2b;
    margin-bottom: 20px;
    text-align: center;
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 14px;
    margin-bottom: 16px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #c79a63;
}

.contact-form textarea {
    resize: vertical;
    min-height: 120px;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background: #c79a63;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1.05rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-submit:hover {
    background: #a97e4f;
}

/* Info Item */
.info-item {
    margin-bottom: 20px;
}
.info-item strong {
    color: #2b2b2b;
    font-size: 1.05rem;
}
.info-item p {
    margin: 6px 0 0 0;
    color: #555;
    line-height: 1.5;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .contact-section {
        grid-template-columns: 1fr;
    }
    .contact-header h1 {
        font-size: 2rem;
    }
}
</style>