<?php
session_start();
include("../../dB/config.php");
include("./includes/header.php");
include("./includes/topbar.php");
include("./includes/sidebar.php");

$user_id = $_SESSION['user_id'] ?? null;
$uploadedFile = $_SESSION['uploaded_filename'] ?? null;

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
    <p class="text-muted">Run data cleansing to detect duplicates and inconsistencies in your uploaded file.</p>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">Uploaded File</h5>
        <?php if ($uploadedFile): ?>
          <p><strong>File:</strong> <?= htmlspecialchars($uploadedFile) ?></p>
          <p class="text-muted small">This file will be scanned for duplicate entries.</p>
        <?php else: ?>
          <p><strong>File:</strong> No file uploaded.</p>
          <p class="text-muted small">Please upload one from the Upload Data page first.</p>
        <?php endif; ?>
      </div>
    </div>

    <form method="post" action="../../controller/clean_process.php">
      <button type="submit" class="btn btn-primary mb-3" <?= $uploadedFile ? '' : 'disabled' ?>>
        <i class="bi bi-brush"></i> Start Cleaning
      </button>
    </form>

    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Cleansing Summary</h5>
        <?php if (isset($_SESSION['cleaning_result'])): ?>
          <ul>
            <li><strong>Exact Duplicates:</strong> <?= $_SESSION['cleaning_result']['exact'] ?? 0 ?></li>
            <li><strong>Sounds-Like Duplicates:</strong> <?= $_SESSION['cleaning_result']['sound'] ?? 0 ?></li>
            <li><strong>Possible Duplicates:</strong> <?= $_SESSION['cleaning_result']['possible'] ?? 0 ?></li>
            <li><strong>Records Cleaned:</strong> <?= $_SESSION['cleaning_result']['cleaned'] ?? 0 ?></li>
          </ul>
        <?php else: ?>
          <p class="text-muted">No cleaning has been performed yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<?php include("./includes/footer.php"); ?>
