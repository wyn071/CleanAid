<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Link Sent | CleanAid</title>
  <link href="../assets/img/logo.png" rel="icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
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
      margin-bottom: 10px;
    }
    .card p {
      font-size: 14px;
      color: #555;
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
    <h4>Reset Link Sent</h4>
    <p>Check your email for the password reset link.</p>
    <p>This link will expire in 1 hour.</p>
    <a href="../login.php"><i class="bi bi-arrow-left"></i> Back to Login</a>
  </div>

  <?php if (isset($_SESSION['reset_success'])): ?>
  <script>
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'Reset link sent to your email.',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  </script>
  <?php unset($_SESSION['reset_success']); ?>
  <?php endif; ?>
</body>
</html>
