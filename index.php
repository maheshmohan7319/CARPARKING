<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/admin_dashboard.php");
        exit();
    } else {
        header("Location: user_dashboard.php");
        exit();
    }
}

include 'login.php';
?>