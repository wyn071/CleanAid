<?php
session_start();
require '../db/config.php'; // Ensure this includes $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $conn->prepare("UPDATE user SET reset_token = ?, reset_expires = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $token, $expires, $user['user_id']);
        $stmt->execute();
        $stmt->close();

        $resetLink = "http://localhost/CleanAid/auth/reset_password.php?token=$token";

        // Display styled confirmation
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
          <meta charset='UTF-8'>
          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
          <title>Reset Link | CleanAid</title>
          <link href='../assets/img/logo.png' rel='icon'>
          <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
          <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css' rel='stylesheet'>
          <link href='../assets/css/style.css' rel='stylesheet'>
        </head>
        <body class='d-flex align-items-center justify-content-center min-vh-100'>
          <div class='d-flex login-card flex-md-row flex-column shadow rounded overflow-hidden'>
            <div class='left col-md-6 text-center bg-light d-flex align-items-center justify-content-center p-4'>
              <img src='../assets/img/logo.png' alt='CleanAid Logo' class='img-fluid' style='max-height: 200px;'>
            </div>

            <div class='right col-md-6 p-4 text-center'>
              <h4 class='mb-3 fw-bold'>Reset Link Sent</h4>
              <p>Click below to reset your password:</p>
              <a class='btn btn-danger' href='$resetLink'>Reset Password</a>
              <p class='mt-3 small text-muted'>Link valid for 1 hour</p>
              <p class='mt-3'><a href='../login.php'>Back to Login</a></p>
            </div>
          </div>
        </body>
        </html>
        ";
        exit();
    } else {
        $_SESSION['message'] = "Email not found.";
        $_SESSION['code'] = "error";
        header("Location: ../auth/forgot_password.php");
        exit();
    }
}
?>
