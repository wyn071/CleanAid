<?php
session_start();

include("../../dB/config.php");
include("./includes/header.php");
include("./includes/topbar.php");
include("./includes/sidebar.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user data using updated column names
$query = "SELECT name, email, password FROM user WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}
?>

<!-- Main Content Start -->
<main id="main" class="main">

  <div class="pagetitle">
    <h1>My Profile</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Profile</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="profile">
    <div class="card">
      <div class="card-body pt-4">
        <h5 class="card-title">User Information</h5>

        <form>
          <div class="row mb-3">
            <label class="col-md-4 col-lg-3 col-form-label">Full Name</label>
            <div class="col-md-8 col-lg-9">
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" disabled>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-lg-3 col-form-label">Email</label>
            <div class="col-md-8 col-lg-9">
              <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-4 col-lg-3 col-form-label">Password</label>
            <div class="col-md-8 col-lg-9">
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['password']) ?>" disabled>
            </div>
          </div>
        </form>

      </div>
    </div>
  </section>
</main>
<!-- Main Content End -->

<?php include("./includes/footer.php"); ?>
