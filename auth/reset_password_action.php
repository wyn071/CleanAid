<?php
session_start();
require '../db/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm)) {
        $_SESSION['message'] = "Please fill all fields.";
        $_SESSION['code'] = "error";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['code'] = "error";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    // Check if token exists and is valid
    $stmt = $conn->prepare("SELECT user_id, reset_expires FROM user WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $_SESSION['message'] = "Invalid or expired token.";
        $_SESSION['code'] = "error";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    $currentTime = date("Y-m-d H:i:s");
    if ($user['reset_expires'] < $currentTime) {
        $_SESSION['message'] = "Token expired.";
        $_SESSION['code'] = "error";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    // Update password without hashing (as per your request)
    $hashedPassword = $password;

    $stmt = $conn->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_expires = NULL WHERE user_id = ?");
    $stmt->bind_param("si", $hashedPassword, $user['user_id']);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Password reset successfully!";
    $_SESSION['code'] = "success";
    header("Location: ../login.php");
    exit();
}
?>
