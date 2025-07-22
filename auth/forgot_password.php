<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Forgot Password | CleanAid</title>

  <!-- Favicons -->
  <link href="../assets/img/logo.png" rel="icon">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

  <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
    <h3 class="fw-bold text-center mb-3">Forgot Password</h3>
    <p class="text-center text-muted small mb-4">
      Enter your email address and we’ll send you a link to reset your password.
    </p>
    <form action="../controller/send_reset_link.php" method="POST" class="needs-validation" novalidate>
      <div class="mb-3">
        <input type="email" class="form-control" name="email" placeholder="Email Address" required>
        <div class="invalid-feedback">Please enter your email address.</div>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-danger">Send Reset Link</button>
      </div>
      <p class="text-center mt-3 small">
        <a href="../login.php"><i class="bi bi-arrow-left"></i> Back to Login</a>
      </p>
    </form>
  </div>

  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <?php if(isset($_SESSION['message']) && $_SESSION['code'] != ''): ?>
  <script>
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: '<?php echo $_SESSION['code']; ?>',
      title: '<?php echo $_SESSION['message']; ?>',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  </script>
  <?php
    unset($_SESSION['message']);
    unset($_SESSION['code']);
  endif;
  ?>
</body>
</html>
