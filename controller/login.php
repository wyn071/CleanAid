<?php
session_start();
include("../dB/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, name, role, email, password FROM user WHERE email = ? AND password = ? LIMIT 1");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // If match found
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $_SESSION['auth'] = true;
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['authUser'] = [
            'fullName' => $user['name'],
            'emailAddress' => $user['email']
        ];

        header("Location: /CleanAid/view/admin/dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = "Invalid email or password.";
        $_SESSION['code'] = "error";
        header("Location: ../login.php");
        exit();
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['code'] = "error";
    header("Location: ../login.php");
    exit();
}
