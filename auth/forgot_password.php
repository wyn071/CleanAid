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

  <div class="d-flex login-card flex-md-row flex-column shadow rounded overflow-hidden">
    <!-- Left Section -->
    <div class="left col-md-6 text-center bg-light d-flex align-items-center justify-content-center p-4">
      <img src="../assets/img/logo.png" alt="CleanAid Logo" class="img-fluid" style="max-height: 200px;">
    </div>

    <!-- Right Section -->
    <div class="right col-md-6 p-4">
      <h3 class="fw-bold text-center mb-4">Forgot Password</h3>
      <form action="../controller/send_reset_link.php" method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
          <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
          <div class="invalid-feedback">Please enter your registered email.</div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-danger">Send Reset Link</button>
        </div>

        <p class="text-center mt-3 small">
          <a href="../login.php">Back to Login</a>
        </p>
      </form>
    </div>
  </div>

  <!-- Bootstrap Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- SweetAlert for Session Messages -->
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
