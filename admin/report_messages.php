<?php
session_start();
// ✅ CEK LOGIN ADMIN
if (!isset($_SESSION['admin_id'])) {
    // Belum login? Redirect ke register
    header("Location: register.php");
    exit;
}

include '../config/db.php'; // sesuaikan path jika perlu

$stmt = $pdo->query("SELECT * FROM report_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Keluhan - Admin</title>
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

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .message-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .message-name {
            font-weight: bold;
            color: #2c2c2c;
        }

        .message-date {
            color: #888;
            font-size: 0.9rem;
        }

        .message-body {
            margin-top: 10px;
            line-height: 1.6;
            color: #444;
        }

        .message-email {
            color: #007bff;
            font-weight: 500;
        }

        .btn-back {
            display: inline-block;
            background: #6d6f75;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
        }

        .btn-back:hover {
            background: #56575b;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>📦 Daftar Pesan Kontak</h1>

        <?php if (empty($messages)): ?>
            <p style="text-align: center; color: #888;">Belum ada pesan masuk.</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message-card">
                    <div class="message-header">
                        <span class="message-name"><?= htmlspecialchars($msg['name']) ?></span>
                        <span class="message-date"><?= date('d M Y H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <div class="message-body">
                        <p><strong>Email:</strong> <span class="message-email"><?= htmlspecialchars($msg['email']) ?></span></p>
                        <p><strong>Pesan:</strong></p>
                        <pre style="background:#f9f9f9; padding:12px; border-radius:6px; font-family: inherit; white-space: pre-wrap;"><?= htmlspecialchars($msg['message']) ?></pre>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="dashboard.php" class="btn-back">Kembali ke Dashboard</a>
    </div>
</body>
</html>