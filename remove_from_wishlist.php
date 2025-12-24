<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_id'])) {
    $user_id = $_SESSION['user_id'];
    $wishlist_id = (int)$_POST['wishlist_id'];

    $stmt = $pdo->prepare("DELETE FROM wishlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$wishlist_id, $user_id]);

    header("Location: wishlist.php");
    exit;
}
?>