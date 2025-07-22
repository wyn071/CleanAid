<?php
session_start();
include __DIR__ . '/../dB/config.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $_SESSION['message'] = 'Email is required.';
        $_SESSION['code'] = 'error';
        header('Location: ../auth/forgot_password.php');
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Save token and expiry in database
        $update = $conn->prepare("UPDATE user SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expires, $email);
        $update->execute();

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'cleanaidcaps@gmail.com';       // Sender email
            $mail->Password   = 'dzdfkcqfacnrfbsw';              // App password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('cleanaidcaps@gmail.com', 'CleanAid Support');
            $mail->addAddress($email);

            $resetLink = "http://localhost/CleanAid/auth/reset_password.php?token=$token";

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body    = "
                <p>Hello,</p>
                <p>You requested a password reset. Click the button below to proceed:</p>
                <p style='text-align: center;'>
                    <a href='$resetLink' style='
                        display: inline-block;
                        padding: 12px 25px;
                        background-color: #dc3545;
                        color: white;
                        text-decoration: none;
                        font-weight: bold;
                        border-radius: 6px;
                        font-size: 14px;
                    '>Reset Password</a>
                </p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this, you can safely ignore this email.</p>
            ";

            $mail->send();

            $_SESSION['reset_success'] = true;
            header('Location: ../auth/link_sent.php');
            exit();

        } catch (Exception $e) {
            $_SESSION['message'] = 'Mailer Error: ' . $mail->ErrorInfo;
            $_SESSION['code'] = 'error';
            header('Location: ../auth/forgot_password.php');
            exit();
        }

    } else {
        $_SESSION['message'] = 'No account found with that email.';
        $_SESSION['code'] = 'warning';
        header('Location: ../auth/forgot_password.php');
        exit();
    }
} else {
    header('Location: ../auth/forgot_password.php');
    exit();
}
