<?php
session_start();

// Jika sudah ada session user_id, arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: modul/dashboard/index.php");
    exit();
} else {
    // Jika belum login, arahkan ke halaman login
    header("Location: modul/auth/login.php");
    exit();
}
?>