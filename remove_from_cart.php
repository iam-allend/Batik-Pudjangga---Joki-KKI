<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = (int)$_POST['cart_id'];

    $stmt = $pdo->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);

    header("Location: cart.php");
    exit;
}
?>