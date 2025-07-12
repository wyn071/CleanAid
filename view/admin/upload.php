<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main id="main" class="main">
  <section class="container py-5">
    <h2 class="fw-bold mb-4">Data Upload</h2>
    <p class="text-muted mb-4">Upload your CSV file for beneficiary data processing</p>

    <!-- Drag and drop area -->
    <form method="POST" enctype="multipart/form-data" action="#" id="uploadForm">
      <div id="dropZone" class="border border-3 border-dashed rounded-4 bg-light text-center p-5 position-relative">
        <input type="file" id="fileInput" name="file[]" accept=".csv" multiple hidden>

        <!-- Plus icon -->
        <div class="display-1 text-muted" style="pointer-events: none;">+</div>
        <p class="text-muted mt-2 mb-0">Drag & drop CSV files here or click to upload</p>
      </div>

      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary">Upload Files</button>
      </div>
    </form>
  </section>
</main>

<script>
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('fileInput');

  // Clicking the zone opens file dialog
  dropZone.addEventListener('click', () => fileInput.click());

  // Visual feedback on drag
  ['dragenter', 'dragover'].forEach(event => {
    dropZone.addEventListener(event, e => {
      e.preventDefault();
      dropZone.classList.add('bg-info-subtle');
    });
  });

  ['dragleave', 'drop'].forEach(event => {
    dropZone.addEventListener(event, e => {
      e.preventDefault();
      dropZone.classList.remove('bg-info-subtle');
    });
  });

  // Handle dropped files
  dropZone.addEventListener('drop', e => {
    fileInput.files = e.dataTransfer.files;
  });
</script>

<?php include("./includes/footer.php"); ?>
