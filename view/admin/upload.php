<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main id="main" class="main bg-body-tertiary" style="min-height: 100vh;">
  <section class="container py-5">
    <div class="mb-4">
      <h2 class="fw-bold">Data Upload</h2>
      <p class="text-muted">Upload your file/s for beneficiary data processing</p>
    </div>

    <!-- Upload Box -->
    <form method="POST" enctype="multipart/form-data" action="#" id="uploadForm">
      <div id="dropZone" 
           class="border rounded-4 bg-white text-center p-5 position-relative shadow-sm" 
           style="border: 2px dashed #ccc; cursor: pointer; transition: background-color 0.2s ease;">
        <input type="file" id="fileInput" name="file[]" accept=".csv" multiple hidden>

        <!-- Plus icon -->
        <div class="display-1 text-muted" style="pointer-events: none;">+</div>
        <p class="text-muted mt-2 mb-0" style="pointer-events: none;">Click or drag & drop file/s here</p>
      </div>

      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary px-5">Upload Files</button>
      </div>
    </form>
  </section>
</main>

<footer class="text-center py-3 bg-dark text-light small">
  <div class="container">
    © 2025 CleanAid. Developed for DSWD Region X
  </div>
</footer>

<script>
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('fileInput');

  dropZone.addEventListener('click', () => fileInput.click());

  ['dragenter', 'dragover'].forEach(event => {
    dropZone.addEventListener(event, e => {
      e.preventDefault();
      dropZone.classList.add('bg-light');
    });
  });

  ['dragleave', 'drop'].forEach(event => {
    dropZone.addEventListener(event, e => {
      e.preventDefault();
      dropZone.classList.remove('bg-light');
    });
  });

  dropZone.addEventListener('drop', e => {
    fileInput.files = e.dataTransfer.files;
  });
</script>

<?php include("./includes/footer.php"); ?>
