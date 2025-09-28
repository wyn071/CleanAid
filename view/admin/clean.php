<?php
// clean.php
// Upload UI + AJAX progress for big files (streamed import + background analysis)

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CleanAid — Import Beneficiaries</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 2rem; }
    .card { max-width: 720px; padding: 1.5rem; border: 1px solid #e5e7eb; border-radius: 12px; }
    .row { margin-bottom: 1rem; }
    .btn { background: #2563eb; color: #fff; border: 0; padding: .6rem 1rem; border-radius: 8px; cursor: pointer; }
    .btn:disabled { opacity: .6; }
    progress { width: 100%; height: 16px; }
    .muted { color: #6b7280; font-size: .9rem; }
    .ok { color: #065f46; }
    .warn { color: #92400e; }
    .err { color: #991b1b; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
    .link { color: #2563eb; text-decoration: none; }
  </style>
</head>
<body>
  <div class="card">
    <h2>Import Beneficiaries (Large File Safe)</h2>
    <p class="muted">CSV with header: first_name,last_name,middle_name,ext_name,birth_date,region,province,city,barangay,marital_status</p>
    <form id="uploadForm" action="clean_process.php" method="POST" enctype="multipart/form-data" target="hidden_iframe">
      <div class="row">
        <input type="file" name="file" id="file" accept=".csv" required />
      </div>
      <div class="row">
        <button class="btn" id="btnUpload">Start Import</button>
      </div>
    </form>

    <div id="statusArea" style="display:none;">
      <h3>Status</h3>
      <div class="row"><progress id="pg" value="0" max="100"></progress></div>
      <div class="row mono"><span id="lbl"></span></div>
      <div class="row mono muted" id="detail"></div>
      <div class="row" id="reviewLink" style="display:none;"></div>
    </div>

    <iframe name="hidden_iframe" style="display:none;"></iframe>
  </div>

<script>
const form = document.getElementById('uploadForm');
const btn = document.getElementById('btnUpload');
const statusArea = document.getElementById('statusArea');
const pg = document.getElementById('pg');
const lbl = document.getElementById('lbl');
const detail = document.getElementById('detail');
const reviewLink = document.getElementById('reviewLink');

let processingId = null;
let listId = null;
let pollTimer = null;

form.addEventListener('submit', (e) => {
  statusArea.style.display = 'block';
  btn.disabled = true;
  lbl.textContent = 'Uploading & streaming rows into database...';
  pg.value = 5;
  detail.textContent = '';
});

window.addEventListener('message', (event) => {
  // Expect a JSON message from clean_process.php containing {ok, list_id, processing_id, note}
  try {
    const data = JSON.parse(event.data);
    if (!data) return;
    if (data.ok) {
      listId = data.list_id;
      processingId = data.processing_id;
      lbl.textContent = 'Import started. Background analysis will run shortly...';
      pg.value = 30;
      pollStatus();
    } else {
      lbl.textContent = 'Error: ' + (data.error || 'Unknown error');
      detail.textContent = data.note || '';
      pg.value = 0;
      btn.disabled = false;
    }
  } catch(_) {}
}, false);

function pollStatus() {
  if (!processingId) return;
  pollTimer = setInterval(async () => {
    try {
      const r = await fetch(`clean_process.php?action=progress&processing_id=${processingId}`);
      const j = await r.json();
      // j: {status, inserted_rows, total_rows, phase, note}
      if (j.total_rows > 0) {
        const pct = j.phase === 'IMPORT' ? Math.min(95, Math.floor((j.inserted_rows / j.total_rows) * 90) + 5) :
                   j.phase === 'ANALYZE' ? 96 :
                   j.phase === 'DONE' ? 100 : 50;
        pg.value = pct;
      }
      lbl.textContent = `Phase: ${j.phase} — Status: ${j.status}`;
      detail.textContent = j.note || '';

      if (j.phase === 'DONE' || j.status === 'DONE') {
        clearInterval(pollTimer);
        pg.value = 100;
        lbl.textContent = 'Completed!';
        reviewLink.style.display = 'block';
        reviewLink.innerHTML = `<a class="link" href="review.php?processing_id=${processingId}&list_id=${listId}">Open Review</a>`;
        btn.disabled = false;
      }
      if (j.status === 'FAILED') {
        clearInterval(pollTimer);
        lbl.textContent = 'Processing failed.';
        btn.disabled = false;
      }
    } catch (e) {
      // ignore transient errors
    }
  }, 1500);
}
</script>

<script>
// Let the hidden iframe send a postMessage back with JSON
// clean_process.php prints a tiny HTML that calls parent.postMessage(...)
</script>
</body>
</html>
