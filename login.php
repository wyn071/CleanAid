<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login | CleanAid</title>

  <!-- Favicons -->
  <link href="assets/img/logo.png" rel="icon">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

  <div class="d-flex login-card flex-md-row flex-column">
    <!-- Left Section -->
    <div class="left col-md-6 text-center bg-light">
      <img src="assets/img/logo.png" alt="CleanAid Logo">
    </div>

    <!-- Right Section -->
    <div class="right col-md-6">
      <h3 class="fw-bold text-center mb-4">Login</h3>
      <form action="./controller/login.php" method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
          <input type="email" class="form-control" name="email" placeholder="Email" required>
          <div class="invalid-feedback">Please enter your email address.</div>
        </div>
        <div class="mb-3">
          <input type="password" class="form-control" name="password" placeholder="Password" required>
          <div class="invalid-feedback">Please enter your password.</div>
        </div>
        <div class="d-flex justify-content-between mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember me</label>
          </div>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-danger" name="login">Login</button>
        </div>
        <p class="text-center mt-3 small">
          Don't have an account?
          <a href="registration.php">Sign up</a>
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
