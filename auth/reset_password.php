<?php
session_start();
include __DIR__ . '/../dB/config.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['message'] = 'Reset token is missing.';
    $_SESSION['code'] = 'error';
    header('Location: ../login.php');
    exit();
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM user WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || strtotime($user['token_expiry']) < time()) {
    $_SESSION['message'] = 'Invalid or expired reset token.';
    $_SESSION['code'] = 'error';
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['message'] = 'Both password fields are required.';
        $_SESSION['code'] = 'error';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['message'] = 'Passwords do not match.';
        $_SESSION['code'] = 'error';
    } else {
        // Store password as plain text (NOT RECOMMENDED)
        $update = $conn->prepare("UPDATE user SET password = ?, reset_token = NULL, token_expiry = NULL WHERE user_id = ?");
        $update->bind_param("si", $new_password, $user['user_id']);
        $update->execute();

        $_SESSION['message'] = 'Password has been successfully updated.';
        $_SESSION['code'] = 'success';
        header('Location: ../login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | CleanAid</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="../assets/img/logo.png" rel="icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background: url('../assets/img/bg-login.png') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      font-family: Arial, sans-serif;
    }
    .card {
      padding: 30px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
      text-align: center;
    }
    .card img {
      width: 80px;
      margin-bottom: 20px;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }
    .card h4 {
      font-weight: bold;
      margin-bottom: 15px;
    }
    .card input {
      margin-bottom: 10px;
    }
    .card a {
      font-size: 14px;
      color: #dc3545;
      text-decoration: none;
    }
    .card a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="card">
    <img src="../assets/img/logo.png" alt="CleanAid Logo">
    <h4>Set New Password</h4>
    <form method="POST" novalidate>
      <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
      <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
      <div class="d-grid mt-3">
        <button type="submit" class="btn btn-danger">Reset Password</button>
      </div>
    </form>
    <div class="mt-3">
      <a href="../login.php"><i class="bi bi-arrow-left"></i> Back to Login</a>
    </div>
  </div>

  <?php if (isset($_SESSION['message']) && $_SESSION['code']): ?>
  <script>
    Swal.fire({
      icon: '<?= $_SESSION['code'] ?>',
      title: '<?= $_SESSION['message'] ?>',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  </script>
  <?php unset($_SESSION['message'], $_SESSION['code']); endif; ?>

</body>
</html>