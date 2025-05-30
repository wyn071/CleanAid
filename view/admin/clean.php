<?php
session_start();
include("../../dB/config.php");
include("./includes/header.php");
include("./includes/topbar.php");
include("./includes/sidebar.php");

// Get current user ID
$user_id = $_SESSION['user_id'] ?? null;
$uploadedFile = $_SESSION['uploaded_filename'] ?? null;

// Fallback: Get latest uploaded file from database if session not set
if (!$uploadedFile && $user_id) {
    $query = $conn->prepare("SELECT fileName FROM beneficiarylist WHERE user_id = ? ORDER BY date_submitted DESC LIMIT 1");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $uploadedFile = $row['fileName'];
    }
}
?>

<main id="main" class="main">
  <section class="container py-5">
    <h2 class="fw-bold mb-4">Clean Data</h2>
    <p class="text-muted">Run data cleansing on your uploaded file to remove duplicates, fill missing values, and ensure clean records.</p>

    <!-- Uploaded File Info -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">Uploaded File</h5>
        <?php if ($uploadedFile): ?>
          <p class="mb-1"><strong>File:</strong> <?= htmlspecialchars($uploadedFile) ?></p>
          <p class="text-muted small">The file is ready for cleaning.</p>
        <?php else: ?>
          <p class="mb-0"><strong>File:</strong> No file uploaded yet.</p>
          <p class="text-muted small">Please upload a file from the Upload Data page first.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Start Cleaning Button -->
    <form method="post" action="clean_process.php">
      <button type="submit" class="btn btn-primary mb-3" <?= $uploadedFile ? '' : 'disabled' ?>>
        <i class="bi bi-brush"></i> Start Cleaning
      </button>
    </form>

    <!-- Cleansing Summary -->
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Cleansing Summary</h5>
        <?php if (isset($_SESSION['cleaning_result'])): ?>
          <ul>
            <li><strong>Duplicates Found:</strong> <?= $_SESSION['cleaning_result']['duplicates'] ?></li>
            <li><strong>Missing Fields:</strong> <?= $_SESSION['cleaning_result']['missing'] ?></li>
            <li><strong>Records Cleaned:</strong> <?= $_SESSION['cleaning_result']['cleaned'] ?></li>
          </ul>
        <?php else: ?>
          <p class="text-muted">No cleaning operation has been performed yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<?php include("./includes/footer.php"); ?>
