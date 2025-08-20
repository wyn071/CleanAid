<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Register | CleanAid</title>

  <!-- Favicons -->
  <link href="assets/img/logo.png" rel="icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans|Nunito|Poppins" rel="stylesheet">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">

  <div class="d-flex register-card flex-md-row flex-column">
    <!-- Left Section -->
    <div class="left col-md-6 text-center bg-light">
      <img src="assets/img/logo.png" alt="CleanAid Logo">
    </div>

    <!-- Right Section -->
    <div class="right col-md-6">
      <h3 class="fw-bold text-center mb-4">Sign up</h3>
      <form action="./controller/registration.php" method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
          <input 
            type="text" 
            class="form-control" 
            name="name" 
            placeholder="Full Name" 
            required
            value="<?php echo isset($_SESSION['old_name']) ? htmlspecialchars($_SESSION['old_name']) : ''; ?>">
          <div class="invalid-feedback">Please enter your full name.</div>
        </div>
        <div class="mb-3">
          <input 
            type="email" 
            class="form-control" 
            name="email" 
            placeholder="Email" 
            required
            value="<?php echo isset($_SESSION['old_email']) ? htmlspecialchars($_SESSION['old_email']) : ''; ?>">
          <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>
        <div class="mb-3">
          <input 
            type="password" 
            class="form-control" 
            name="password" 
            placeholder="Password" 
            required
            value="<?php echo isset($_SESSION['old_password']) ? htmlspecialchars($_SESSION['old_password']) : ''; ?>">
          <div class="invalid-feedback">Please enter your password.</div>
        </div>
        <div class="mb-3">
          <input 
            type="password" 
            class="form-control" 
            name="cpassword" 
            placeholder="Confirm Password" 
            required
            value="<?php echo isset($_SESSION['old_cpassword']) ? htmlspecialchars($_SESSION['old_cpassword']) : ''; ?>">
          <div class="invalid-feedback">Please confirm your password.</div>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-danger" name="registration">Sign up</button>
        </div>
        <p class="text-center mt-3 small">
          Already have an account?
          <a href="./login.php">Login</a>
        </p>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <?php if (isset($_SESSION['message']) && $_SESSION['code'] != ''): ?>
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
    unset($_SESSION['message'], $_SESSION['code']);
    ?>
  <?php endif; ?>

</body>
</html>

<?php
// ✅ Clear old values AFTER displaying them
unset($_SESSION['old_name'], $_SESSION['old_email'], $_SESSION['old_password'], $_SESSION['old_cpassword']);
?>
