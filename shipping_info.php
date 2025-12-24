<?php include 'includes/header.php'; ?>

<div class="shipping-header">
    <h1>Info Pengiriman & Ongkos Kirim</h1>
</div>

<section class="shipping-section">
    <div class="info-box">
        <h3>🚚 Estimasi Waktu Pengiriman</h3>
        <p>Karena semua produk <strong>pre-order & handmade</strong>, proses produksi membutuhkan waktu:</p>
        <ul>
            <li><strong>Persiapan & Jahit:</strong> 2-3 hari kerja</li>
            <li>Exspress : 1 hari kerja dengan penambahan harga Rp. 15.000 - Rp. 25.000</li>
            <li><strong>Pengiriman:</strong></li>
            <ul style="margin-top:8px;">
                <li>Jawa: 1-2 hari</li>
                <li>Jawa Barat: 2-3 hari</li>
                <li>Jakarta : 1-2 hari</li>
                <li>Luar Jawa: 7-10 hari</li>

            </ul>
        </ul>
        <p style="background:#fff8f0; padding:12px; border-radius:8px; margin-top:15px;">
            <strong>📌 Total estimasi:</strong> 7–14 hari sejak pembayaran.
        </p>
    </div>

    <div class="info-box">
        <h3>📦 Ekspedisi yang Kami Gunakan</h3>
        <p>Kami mengirim via:</p>
        <ul>
            <li><strong>Wahana</strong></li>
            <li><strong>J&T Reguler</strong></li>
            <li><strong>Leon Parcel</strong></li>
        </ul>
        <p><strong>Resi akan dikirim via WhatsApp</strong> setelah pesanan dikemas dan dikirim.</p>
    </div>

    <div class="info-box">
        <h3>💰 Ongkos Kirim</h3>
        <p>Ongkir dihitung otomatis saat checkout berdasarkan provinsi tujuan.</p>
        <p><strong>Contoh estimasi (Reguler):</strong></p>
        <table style="width:100%; border-collapse:collapse; margin-top:10px;">
            <tr style="background:#f5f0e6;">
                <th style="text-align:left; padding:8px;">Provinsi</th>
                <th style="text-align:right; padding:8px;">Ongkir</th>
            </tr>
            <tr><td>Jawa Tengah</td><td style="text-align:right;">Rp 15.000</td></tr>
            <tr><td>DKI Jakarta</td><td style="text-align:right;">Rp 15.000 - Rp 20.000</td></tr>
            <tr><td>Jawa Barat</td><td style="text-align:right;">Rp 15.000 - Rp 20.000</td></tr>
            <tr><td>Luar Jawa</td><td style="text-align:right;">Rp 35.000 – Rp 60.000</td></tr>
        </table>
    </div>

    <div class="info-box" style="border-left: 4px solid #d4a373; background: #fff8f0;">
        <h3>❓ Masih Bingung?</h3>
        <p><a href="https://wa.me/6285930433717?text=Saya%20mau%20tanya%20tentang%20pengiriman" 
               target="_blank" 
               class="btn-see-collection" style="display:inline-block; margin-top:10px;">
               📱 Chat Kami via WhatsApp
            </a></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
.shipping-header {
    text-align: center;
    padding: 50px 20px;
    background: #f5f0e6;
    margin-top: 80px;
    font-family: 'Segoe UI', sans-serif;
}
.shipping-header h1 {
    font-size: 2.2rem;
    color: #333;
    margin: 0;
}
.shipping-section {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}
.info-box {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    font-family: 'Segoe UI', sans-serif;
}
.info-box h3 {
    color: #333;
    margin-top: 0;
    font-size: 1.4rem;
}
.info-box p {
    color: #555;
    line-height: 1.6;
}
.info-box ul {
    padding-left: 20px;
    margin: 10px 0;
}
.info-box li {
    margin-bottom: 8px;
    color: #555;
}
.info-box table {
    font-size: 0.95rem;
}
.info-box th,
.info-box td {
    padding: 10px 8px;
    border-bottom: 1px solid #eee;
}
.btn-see-collection {
    display: inline-block;
    padding: 10px 20px;
    background: #d4a373;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.3s;
}
.btn-see-collection:hover {
    background: #b58360;
}
</style>