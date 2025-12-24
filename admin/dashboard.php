<?php
session_start();
// ✅ CEK LOGIN ADMIN
if (!isset($_SESSION['admin_id'])) {
    // Belum login? Redirect ke register
    header("Location: register.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Batik Pudjangga</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Produk</a>
        <a href="subscribers.php">Subscriber</a>
        <a href="report_messages.php">Report Messages</a>
        <a href="orders.php">Orders Check</a>
        <a href="sales_report.php">Laporan</a>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="content">
        <div class="welcome">
            <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>!</h1>
            <p>Di sini kamu bisa mengelola produk, subscriber, dan konten website.</p>
        </div>
        
    </div>
</body>
</html>