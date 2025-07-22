<?php
session_start();
$files = $_SESSION['uploaded_files'] ?? [];
?>

<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main id="main" class="main bg-body-tertiary" style="min-height: 100vh;">
  <section class="container py-5">
    <div class="mb-4">
      <h2 class="fw-bold">Clean Data</h2>
      <p class="text-muted">Run data cleansing to detect duplicates and inconsistencies in your uploaded file.</p>
    </div>

    <div class="bg-white border rounded-4 p-4 shadow-sm">
      <h5>Uploaded Files</h5>
      <ul class="list-unstyled">
        <?php if (!empty($files)): ?>
          <?php foreach ($files as $file): ?>
            <li class="mb-2">
              <img src="../../assets/img/xls.png" width="20" class="me-2">
              <?= htmlspecialchars($file) ?>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="text-muted">No files uploaded.</li>
        <?php endif; ?>
      </ul>
      <small class="text-muted">These files will be scanned for duplicate entries.</small>
    </div>

    <form method="POST" action="../../controller/clean_process.php" class="text-center mt-4">
      <button type="submit" class="btn btn-success px-5">
        <i class="bi bi-broom me-2"></i>Start Cleaning
      </button>
    </form>
  </section>
</main>

<?php include("./includes/footer.php"); ?>
