<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main class="main bg-body-tertiary" style="min-height: 100vh;">
  <section class="container py-5">
    <h2 class="fw-bold">Data Upload</h2>
    <p class="text-muted">Upload your file/s for beneficiary data processing</p>

    <form method="POST" enctype="multipart/form-data" action="../../controller/upload_process.php" id="uploadForm">
      <div id="dropZone" class="border rounded-4 text-center p-5 shadow-sm" style="border: 2px dashed #ccc;">
        <input type="file" id="fileInput" name="file[]" accept=".csv,.xls,.xlsx" multiple hidden>
        <div class="display-1 text-muted">+</div>
        <p class="text-muted">Click or drag & drop file/s here</p>
      </div>
      <div id="fileList" class="mt-3 text-muted small text-center"></div>
      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary px-5">Upload Files</button>
      </div>
    </form>
  </section>
</main>

<script>
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('fileInput');
  const fileList = document.getElementById('fileList');

  dropZone.addEventListener('click', () => fileInput.click());
  dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('bg-light'); });
  dropZone.addEventListener('dragleave', e => { e.preventDefault(); dropZone.classList.remove('bg-light'); });
  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('bg-light');
    fileInput.files = e.dataTransfer.files;
    showFiles(fileInput.files);
  });
  fileInput.addEventListener('change', () => showFiles(fileInput.files));
  function showFiles(files) {
    fileList.innerHTML = "<strong>Selected file(s):</strong><br>";
    for (let i = 0; i < files.length; i++) {
      fileList.innerHTML += `• ${files[i].name}<br>`;
    }
  }
</script>

<?php include("./includes/footer.php"); ?>
