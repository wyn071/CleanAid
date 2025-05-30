<?php
session_start();

// Restrict access to admin only
if (!isset($_SESSION['auth']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Unauthorized access. Please login.";
    $_SESSION['code'] = "warning";
    header("Location: ../login.php");
    exit();
}

// Disable caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

include("../../dB/config.php");
include("./includes/header.php");
include("./includes/topbar.php");
include("./includes/sidebar.php");
?>

<main id="main" class="main flex-grow-1">
  <div class="container-fluid">

    <div class="pagetitle">
      <h1>Admin Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div>

    <section class="dashboard">
      <div class="row g-4">

        <!-- Welcome Card -->
        <div class="col-lg-12">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Welcome, <?= htmlspecialchars($_SESSION['authUser']['fullName']) ?>!</h5>
              <p class="text-muted">This is the DSWD CleanAid admin dashboard. Use the sidebar to navigate through system tools such as data upload, cleaning, and review.</p>
            </div>
          </div>
        </div>

        <!-- Recent Uploads -->
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Recent Uploads</h5>
              <ul class="list-group list-group-flush">
                <?php
                $recentUploads = $conn->query("SELECT filename, date_submitted, status FROM beneficiarylist ORDER BY date_submitted DESC LIMIT 5");
                if ($recentUploads && $recentUploads->num_rows > 0) {
                  while ($upload = $recentUploads->fetch_assoc()) {
                    $badge = match ($upload['status']) {
                      'success' => 'success',
                      'warning' => 'warning',
                      'error' => 'danger',
                      default => 'secondary'
                    };
                    echo "<li class='list-group-item d-flex justify-content-between align-items-start'>
                            <div>
                              <strong>" . htmlspecialchars($upload['filename']) . "</strong><br>
                              <small>" . date("M j, Y g:i A", strtotime($upload['date_submitted'])) . "</small>
                            </div>
                            <span class='badge bg-$badge mt-1 text-uppercase'>" . $upload['status'] . "</span>
                          </li>";
                  }
                } else {
                  echo "<li class='list-group-item text-muted'>No recent uploads found.</li>";
                }
                ?>
              </ul>
            </div>
          </div>
        </div>

        <!-- Cleansing Summary -->
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Cleansing Summary</h5>
              <?php
              $summaryQuery = $conn->query("
                SELECT 
                  COUNT(*) AS total_cleaned,
                  SUM(CASE WHEN flagged_reason = 'Exact duplicate (Name + Birthdate)' THEN 1 ELSE 0 END) AS total_duplicates,
                  SUM(CASE WHEN flagged_reason = 'Missing required field(s)' THEN 1 ELSE 0 END) AS total_missing
                FROM duplicaterecord
              ");

              if ($summaryQuery && $summary = $summaryQuery->fetch_assoc()):
              ?>
                <ul class="mb-0">
                  <li><strong>Total Records Cleaned:</strong> <?= $summary['total_cleaned'] ?></li>
                  <li><strong>Duplicates Found:</strong> <?= $summary['total_duplicates'] ?></li>
                  <li><strong>Missing Fields:</strong> <?= $summary['total_missing'] ?></li>
                </ul>
              <?php else: ?>
                <p class="text-muted">No cleansing data available yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>
</main>

<?php include("./includes/footer.php"); ?>
