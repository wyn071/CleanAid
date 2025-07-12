<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main id="main" class="main">
  <section class="container py-5">
    <h2 class="fw-bold mb-4">Clean Data</h2>
    <p class="text-muted">Run data cleansing to detect duplicates and inconsistencies in your uploaded file.</p>

    <!-- Placeholder for alert message -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      This is a sample alert message.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">Uploaded File</h5>
        <p><strong>File:</strong> sample_file.csv</p>
        <p class="text-muted small">This file will be scanned for duplicate entries.</p>
      </div>
    </div>

    <form method="post" action="#">
      <button type="submit" class="btn btn-primary mb-3">
        <i class="bi bi-brush"></i> Start Cleaning
      </button>
    </form>

    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Cleansing Summary</h5>
        <ul>
          <li><strong>Exact Duplicates:</strong> </li>
          <li><strong>Sounds-Like Duplicates:</strong> </li>
          <li><strong>Possible Duplicates:</strong> </li>
          <li><strong>Records Cleaned:</strong> </li>
        </ul>
      </div>
    </div>
  </section>
</main>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<?php include("./includes/footer.php"); ?>
