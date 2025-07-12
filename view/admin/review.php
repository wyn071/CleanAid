<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main id="main" class="main">
  <section class="container py-5">
    <h2 class="fw-bold mb-4">Review Issues</h2>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">Summary</h5>
        <ul class="mb-0">
          <li><strong>Exact Duplicates:</strong> <!-- insert exact count here --></li>
          <li><strong>Possible Duplicates:</strong> <!-- insert possible count here --></li>
          <li><strong>Sounds-Like Duplicates:</strong> <!-- insert sound-like count here --></li>
          <li><strong>Total Records Processed:</strong> <!-- insert total processed here --></li>
        </ul>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Flagged Records</h5>

        <!-- Table placeholder -->
        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th>Beneficiary ID</th>
                <th>Full Name</th>
                <th>Birth Date</th>
                <th>Region</th>
                <th>Province</th>
                <th>City</th>
                <th>Barangay</th>
                <th>Marital Status</th>
                <th>Reason(s)</th>
              </tr>
            </thead>
            <tbody>
              <!-- Insert flagged record rows here dynamically -->
            </tbody>
          </table>
        </div>

        <!-- Download button placeholder -->
        <form method="post" action="#">
          <button type="submit" class="btn btn-outline-secondary mt-3">
            <i class="bi bi-download"></i> Download Flagged Records (CSV)
          </button>
        </form>
      </div>
    </div>
  </section>
</main>

<?php include("./includes/footer.php"); ?>
