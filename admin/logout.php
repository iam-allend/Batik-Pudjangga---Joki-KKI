<?php
session_start();
session_destroy();
header("Location: register.php"); // Setelah logout, kembali ke register
exit;
?>