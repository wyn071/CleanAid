<?php
require '../db/config.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid request.");
}

// 1. Look up the token in the database
$stmt = $conn->prepare("SELECT user_id, reset_expires FROM user WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("❌ Invalid or expired reset token (user not found).");
}

// 2. Check if token has expired
$currentTime = date("Y-m-d H:i:s");
if ($user['reset_expires'] < $currentTime) {
    die("❌ Token expired. Please request a new reset link.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | CleanAid</title>
  <link href="../assets/img/logo.png" rel="icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

  <div class="d-flex login-card flex-md-row flex-column shadow rounded overflow-hidden">
    <!-- Left Side -->
    <div class="left col-md-6 text-center bg-light d-flex align-items-center justify-content-center p-4">
      <img src="../assets/img/logo.png" alt="CleanAid Logo" class="img-fluid" style="max-height: 200px;">
    </div>

    <!-- Right Side -->
    <div class="right col-md-6 p-4">
      <h4 class="fw-bold text-center mb-4">Reset Your Password</h4>
      <form action="reset_password_action.php" method="POST" novalidate>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="New Password">
            </div>

            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password">
            </div>

            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['message']) && $_SESSION['code']): ?>
<script>
Swal.fire({
  icon: '<?php echo $_SESSION['code']; ?>',
  title: '<?php echo $_SESSION['message']; ?>',
  showConfirmButton: false,
  timer: 3000
});
</script>
<?php
  unset($_SESSION['message']);
  unset($_SESSION['code']);
endif;
?>
</html>
