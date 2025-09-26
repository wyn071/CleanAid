<?php
session_start();
include('../../dB/config.php');

// Prefer ?lists= param over session
if (isset($_GET['lists'])) {
  $lists = array_filter(array_map('intval', explode(',', $_GET['lists'])));
  // ✅ store to session para consistent
  $_SESSION['uploaded_lists'] = $lists;
} else {
  $lists = $_SESSION['uploaded_lists'] ?? [];
}

// Helper to fetch file name for each list
function getListFileName(mysqli $conn, $listId) {
  $listId = (int)$listId;
  $res = mysqli_query($conn, "SELECT fileName FROM beneficiarylist WHERE list_id = {$listId} LIMIT 1");
  if ($res && $row = mysqli_fetch_assoc($res)) {
    return $row['fileName'];
  }
  return "List #{$listId}";
}
?>

<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main class="main bg-body-tertiary" style="min-height: 100vh;">
  <section class="container py-5">
    <h2 class="fw-bold">Clean Data</h2>
    <p class="text-muted">Run data cleansing to detect duplicates and inconsistencies in your uploaded data.</p>

    <!-- Uploaded Files -->
    <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 w-100">
      <div class="d-flex align-items-center justify-content-between">
        <h5 class="fw-semibold mb-3">Uploaded Files</h5>
        <?php if (!empty($lists)): ?>
          <span class="badge text-bg-light"><?= count($lists) ?> file(s)</span>
        <?php endif; ?>
      </div>

      <?php if (!empty($lists)): ?>
        <ul class="list-group list-group-flush mb-3">
          <?php foreach ($lists as $listId): ?>
            <?php $fileLabel = getListFileName($conn, $listId); ?>
            <li class="list-group-item d-flex align-items-center gap-2">
              <img src="https://cdn-icons-png.flaticon.com/512/4725/4725976.png" alt="xls icon" width="24">
              <span class="me-auto"><?= htmlspecialchars($fileLabel) ?></span>
              <code class="text-muted">list_id: <?= (int)$listId ?></code>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="text-muted small">These lists will be scanned for duplicate entries.</div>
      <?php else: ?>
        <div class="alert alert-warning mb-0">No uploaded lists found.</div>
      <?php endif; ?>
    </div>

    <!-- Start Cleaning Button -->
    <?php if (!empty($lists)): ?>
      <div class="text-center">
        <button id="startCleaningBtn" type="button" class="btn btn-success px-4 rounded-pill">
          Start Cleaning
        </button>
      </div>
    <?php endif; ?>
  </section>
</main>

<!-- Cleaning Progress Overlay -->
<div id="cleaningOverlay">
  <div class="loader-container">
    <div class="spinner"></div>
    <p class="loading-text">Starting cleaning...</p>
    <div class="progress-wrapper">
      <div id="cleanProgressBar"></div>
    </div>
    <p id="cleanProgressPercent">0%</p>
    <div class="mt-2 small text-muted" id="cleanExtraHint"></div>
  </div>
</div>

<style>
#cleaningOverlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255, 255, 255, 0.95);
  display: none;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  flex-direction: column;
}
.loader-container { text-align: center; max-width: 400px; width: 100%; }
.spinner {
  width: 60px; height: 60px;
  border: 6px solid #ddd;
  border-top: 6px solid #dc3545;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: auto;
}
.loading-text { margin: 15px 0; font-size: 18px; color: #333; font-weight: 500; }
.progress-wrapper {
  width: 100%; height: 12px; background: #eee;
  border-radius: 8px; overflow: hidden; margin: 10px 0;
}
#cleanProgressBar {
  height: 100%; width: 0%;
  background: linear-gradient(90deg, #dc3545, #ff6b6b);
  transition: width 0.3s ease;
}
#cleanProgressPercent { font-size: 14px; font-weight: 600; color: #dc3545; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
// Lists to process (from PHP session/GET)
const listsToProcess = <?php echo json_encode(array_values($lists)); ?>;

// Overlay helpers
function showOverlay() {
  document.getElementById('cleaningOverlay').style.display = 'flex';
  setProgress(0, 'Starting cleaning...');
}
function setProgress(percent, message, hint) {
  const bar = document.getElementById('cleanProgressBar');
  const pct = document.getElementById('cleanProgressPercent');
  const txt = document.querySelector('.loading-text');
  const hintEl = document.getElementById('cleanExtraHint');
  const p = Math.max(0, Math.min(100, Number(percent) || 0));
  bar.style.width = p + '%';
  pct.textContent = p + '%';
  if (message) txt.textContent = message;
  hintEl.textContent = hint || '';
}

// Kick a run
async function startRunForList(listId) {
  try {
    await fetch('clean_process.php?list_id=' + encodeURIComponent(listId), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'start', list_id: listId })
    });
  } catch (e) {
    console.error('Start failed', e);
  }
}

// Poll progress
async function pollRun(listId) {
  try {
    const r = await fetch('clean_process.php?progress=1&list_id=' + encodeURIComponent(listId), { cache: 'no-store' });
    return await r.json();
  } catch (e) {
    return { status: 'error', message: 'Invalid JSON from clean_process.php' };
  }
}

// Process one list
async function processOneList(listId, index, totalLists) {
  await startRunForList(listId);
  while (true) {
    const data = await pollRun(listId);
    if (data.status === 'error') {
      setProgress(100, `❌ Error on list ${listId}`, data.message || 'See server logs');
      throw new Error(data.message || 'Cleaning error');
    }
    let percent = 0;
    let msg = `Processing list ${index}/${totalLists}`;
    let hint = '';
    if (typeof data.total === 'number' && typeof data.processed === 'number' && data.total > 0) {
      percent = Math.floor((data.processed / data.total) * 100);
      hint = `${data.processed.toLocaleString()} / ${data.total.toLocaleString()} rows`;
    } else if (typeof data.percent === 'number') {
      percent = data.percent;
    }
    setProgress(percent, msg, hint);
    if (data.status === 'complete' || percent >= 100) {
      setProgress(100, `✅ List ${index}/${totalLists} complete`);
      return;
    }
    await new Promise(res => setTimeout(res, 800));
  }
}

// Orchestrate all lists
async function startCleaningAll() {
  if (!listsToProcess || !listsToProcess.length) {
    alert('No uploaded lists to clean.');
    return;
  }
  showOverlay();
  const btn = document.getElementById('startCleaningBtn');
  if (btn) btn.disabled = true;
  try {
    const totalLists = listsToProcess.length;
    for (let i = 0; i < totalLists; i++) {
      await processOneList(listsToProcess[i], i + 1, totalLists);
    }
    const lastListId = listsToProcess[listsToProcess.length - 1];
    setProgress(100, '🎉 Cleaning complete! Redirecting to review…');
    setTimeout(() => {
      window.location.href = 'review.php?list_id=' + encodeURIComponent(lastListId);
    }, 1000);
  } catch (e) {
    console.error(e);
    alert('Cleaning failed: ' + (e.message || 'Check console/logs'));
  } finally {
    if (btn) btn.disabled = false;
  }
}

// Wire up button
document.addEventListener('DOMContentLoaded', () => {
  const startBtn = document.getElementById('startCleaningBtn');
  if (startBtn) startBtn.addEventListener('click', startCleaningAll);
});
</script>
