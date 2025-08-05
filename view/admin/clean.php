<?php
session_start();
$files = $_SESSION['uploaded_files'] ?? [];
$errors = $_SESSION['upload_errors'] ?? [];
?>

<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main class="main bg-body-tertiary" style="min-height: 100vh;">
  <section class="container py-5">
    <h2 class="fw-bold">Clean Data</h2>
    <p class="text-muted">Run data cleansing to detect duplicates and inconsistencies in your uploaded file.</p>

    <!-- Uploaded Files -->
    <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 w-100">
      <h5 class="fw-semibold mb-3">Uploaded Files</h5>

      <?php if (!empty($files)): ?>
        <ul class="list-group list-group-flush mb-3">
          <?php foreach ($files as $file): ?>
            <li class="list-group-item d-flex align-items-center">
              <img src="https://cdn-icons-png.flaticon.com/512/4725/4725976.png" alt="xls icon" width="24" class="me-2">
              <span><?= htmlspecialchars($file) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="text-muted small">
          These files will be scanned for duplicate entries.
        </div>
      <?php else: ?>
        <div class="alert alert-warning mb-0">No uploaded files found.</div>
      <?php endif; ?>
    </div>

    <!-- Start Cleaning Button -->
    <?php if (!empty($files)): ?>
      <div class="text-center">
        <form method="POST" action="review.php">
          <?php foreach ($files as $file): ?>
            <input type="hidden" name="files[]" value="<?= htmlspecialchars($file) ?>">
          <?php endforeach; ?>
          <button type="submit" class="btn btn-primary px-4 rounded-pill">
            🧹 Start Cleaning
          </button>
        </form>
      </div>
    <?php endif; ?>

  </section>
</main>

<?php include("./includes/footer.php"); ?>
