<?php
// Pastikan file ini tidak di-include dua kali
if (!defined('FOOTER_LOADED')) {
    define('FOOTER_LOADED', true);
} else {
    return;
}
?>

<!-- Footer -->
<footer>
    <div class="footer-content">
        <!-- Kolom 1: Logo -->
        <div class="footer-column footer-logo">
            <img src="assets/img/logo_pj.png" alt="Pudjangga Batik">
            <p>PUDJANGGA BATIK</p>
            <p style="font-size:0.85rem; color:#666; margin-top:10px;">
                🪡 Batik Art & Handmade with Love
            </p>
        </div>

        <!-- Kolom 2: PAYMENT METHODS -->
        <div class="footer-column">
            <h5 style="color: #000;">METODE PEMBAYARAN</h5>
            <div class="payment-methods-vertical">
                <img src="assets/img/seabank.png" alt="SeaBank">
                <img src="assets/img/jateng.png" alt="Bank Jateng">
                <img src="assets/img/bca.png" alt="BCA">
            </div>
        </div>

        <!-- Kolom 3: GET HELP — ✅ DI-UPDATE -->
        <div class="footer-column">
            <h5 style="color: #000;">Butuh Bantuan?</h5>
            <ul class="help-list">
                <li>
                    <a href="https://wa.me/6285930433717?text=Halo%20Pudjangga%20Batik,%20saya%20butuh%20bantuan!" 
                       target="_blank">
                        💬 Chat via WhatsApp
                    </a>
                    <small class="help-desc">Fast response (9 AM – 4 PM)</small>
                </li>
                <li>
                    <a href="orders.php">
                        📦 Cek Status Pesanan
                    </a>
                    <small class="help-desc">Kirim ID pesanan (misal: #123)</small>
                </li>
                <li>
                    <a href="shipping_info.php">
                        🚚 Info Pengiriman & Ongkir
                    </a>
                    <small class="help-desc">Estimasi, ekspedisi, biaya</small>
                </li>
                <li>
                    <a href="return_policy.php">
                        🔄 Kebijakan Retur & Ukuran
                    </a>
                    <small class="help-desc">Tukar ukuran? Baca di sini</small>
                </li>
            </ul>
        </div>

        <!-- Kolom 4: CONTACT -->
        <div class="footer-column">
            <h5 style="color: #000; text-align: center;">KONTAK</h5>
            <div class="contact-icons-center">
                <a href="mailto:faradisaolshop1@gmail.com" title="Email">
                    <img src="assets/img/gmail.png" alt="Email">
                </a>
                <a href="https://instagram.com/shopfaradisa" title="Instagram">
                    <img src="assets/img/ig.png" alt="Instagram">
                </a>
                <a href="https://wa.me/6285930433717" title="WhatsApp">
                    <img src="assets/img/wa.png" alt="WhatsApp">
                </a>
            </div>
        </div>

        <!-- Kolom 5: SUBSCRIBE -->
        <div class="footer-column footer-subscribe">
            <p>✨ Dapatkan <strong>diskon 20%</strong> untuk pesanan pertama!</p>
            <form action="signup.php" method="POST" class="newsletter-form">
                <input type="email" name="email" placeholder="Masukkan email..." required>
                <button type="submit">Kirim</button>
            </form>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="footer-alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="footer-alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Pudjangga Batik. All rights reserved.</p>
    </div>
</footer>

<!-- CSS Footer Updated -->
<style>
/* FOOTER - UPDATED */
footer {
    background: #f5f0e6;
    padding: 40px 20px 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    align-items: flex-start;
}

.footer-column {
    display: flex;
    flex-direction: column;
}

.footer-logo img {
    width: 80px;
    height: auto;
    margin-bottom: 10px;
}
.footer-logo p {
    font-size: 0.9rem;
    color: #333;
    margin: 0;
}

/* HELP LIST — NEW */
.help-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.help-list li {
    margin-bottom: 20px;
}
.help-list a {
    color: #333;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.help-list a:hover {
    color: #d4a373;
}
.help-desc {
    display: block;
    font-size: 0.8rem;
    color: #777;
    margin-top: 4px;
}

/* PAYMENT METHODS */
.payment-methods-vertical {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 15px;
}
.payment-methods-vertical img {
    width: 60px;
    height: auto;
    object-fit: contain;
    align-self: flex-start;
}

/* CONTACT ICONS */
.contact-icons-center {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 15px;
    align-items: center;
    justify-content: center;
    padding: 15px;
    background: #f5f0e6;
    border-radius: 8px;
    min-height: 150px;
}
.contact-icons-center img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s;
    border-radius: 8px;
    padding: 5px;
    background: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.contact-icons-center img:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* SUBSCRIBE */
.footer-subscribe p {
    font-size: 0.95rem;
    color: #333;
    margin: 0 0 15px;
    line-height: 1.5;
}

.newsletter-form {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}
.newsletter-form input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}
.newsletter-form button {
    padding: 12px 16px;
    background: #000;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
}
.newsletter-form button:hover {
    background: #333;
}

.footer-alert {
    padding: 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    margin-top: 5px;
}
.footer-alert.error {
    background: #f8d7da;
    color: #721c24;
}
.footer-alert.success {
    background: #d4edda;
    color: #155724;
}

/* FOOTER BOTTOM */
.footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
    margin-top: 40px;
    font-size: 0.9rem;
    color: #555;
}

/* RESPONSIVE */
@media (max-width: 992px) {
    .footer-content {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .payment-methods-vertical,
    .contact-icons-center {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
    }
}
@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
    }
    .newsletter-form {
        flex-direction: column;
    }
    .contact-icons-center {
        flex-direction: row;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        min-height: auto;
    }
    .help-list li {
        margin-bottom: 15px;
    }
}
</style>