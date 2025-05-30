<?php
session_start();
include("../dB/config.php");

// Check if user is logged in
if (!isset($_SESSION['auth'])) {
    $_SESSION['message'] = "Login to access the dashboard";
    $_SESSION['code'] = "warning";
    header("Location: ../../login.php");
    exit();
}

// Check if user is admin
if ($_SESSION['role'] !== "admin") {
    $_SESSION['message'] = "You are not authorized as ADMIN!";
    $_SESSION['code'] = "warning";
    header("Location: ../../login.php");
    exit();
}
?>
