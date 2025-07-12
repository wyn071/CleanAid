<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main id="main" class="main">
  <section class="container py-5">
    <h2 class="fw-bold mb-4">Data Upload</h2>
    <p class="text-muted mb-4">Upload your CSV file for beneficiary data processing</p>

    <!-- Static version of the upload form (no actual backend logic) -->
    <form method="POST" enctype="multipart/form-data" action="#">
      <div class="border border-dashed rounded-3 p-5 text-center bg-light">
        <input type="file" name="file[]" accept=".csv" class="form-control mb-3" required multiple>
        <button type="submit" class="btn btn-primary">Upload File</button>
      </div>
    </form>
  </section>
</main>

<?php include("./includes/footer.php"); ?>
