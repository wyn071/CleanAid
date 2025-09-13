<?php
// view/admin/review.php
session_start();
require_once __DIR__ . '/../../dB/config.php';

/* ---------------------------- Store list ids ---------------------------- */
if (!empty($_POST['lists']) && is_array($_POST['lists'])) {
    $_SESSION['uploaded_lists'] = array_values(
        array_filter($_POST['lists'], fn($v) => ctype_digit((string)$v))
    );
}
$lists = $_SESSION['uploaded_lists'] ?? [];

/* ------------------------------- Helpers -------------------------------- */
function fetch_rows_for_list(mysqli $conn, int $listId): array {
    $stmt = $conn->prepare("
        SELECT beneficiary_id, list_id, first_name, last_name, middle_name, ext_name,
               birth_date, region, province, city, barangay, marital_status
        FROM beneficiary
        WHERE list_id = ?
        ORDER BY beneficiary_id ASC
    ");
    $stmt->bind_param("i", $listId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) $rows[] = $row;
    $stmt->close();
    return $rows;
}

function fetch_filename_for_list(mysqli $conn, int $listId): string {
    $stmt = $conn->prepare("SELECT fileName FROM beneficiarylist WHERE list_id = ?");
    $stmt->bind_param("i", $listId);
    $stmt->execute();
    $stmt->bind_result($fileName);
    $name = $stmt->fetch() ? ($fileName ?: "List #$listId") : "List #$listId";
    $stmt->close();
    return $name;
}

function run_python_analysis(array $rows): array {
    $scriptPath = __DIR__ . "/../../clean_data.py";
    $jsonInput  = json_encode($rows, JSON_UNESCAPED_UNICODE);

    $command = "python3 " . escapeshellarg($scriptPath);
    $spec = [ 0=>["pipe","r"], 1=>["pipe","w"], 2=>["pipe","w"] ];
    $proc = proc_open($command, $spec, $pipes);
    if (!is_resource($proc)) return ["error"=>"Failed to execute analysis script."];

    fwrite($pipes[0], $jsonInput); fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    proc_close($proc);

    $data = json_decode($out, true);
    if (!$data || isset($data['error'])) return ["error"=>$data['error'] ?? ("Analysis failed: $err")];
    return $data;
}

/* ---------------- Save flagged rows to DB ---------------- */
function save_flagged_rows(mysqli $conn, int $listId, array $flagged): void {
    if (empty($flagged)) return;

    // get the latest processing_id for the list_id
    $processingId = null;
    $stmt = $conn->prepare("SELECT processing_id FROM processing_engine WHERE list_id = ? ORDER BY processing_id DESC LIMIT 1");
    $stmt->bind_param("i", $listId);
    $stmt->execute();
    $stmt->bind_result($processingId);
    $stmt->fetch();
    $stmt->close();

    if (!$processingId) return;

    // delete old flagged for this processing_id
    $del = $conn->prepare("DELETE FROM duplicaterecord WHERE processing_id = ?");
    $del->bind_param("i", $processingId);
    $del->execute();
    $del->close();

    // insert new flagged
    $stmt = $conn->prepare("INSERT INTO duplicaterecord (beneficiary_id, processing_id, flagged_reason, status) VALUES (?,?,?,?)");
    foreach ($flagged as $r) {
        $status = 'unresolved';
        $stmt->bind_param("iiss", $r['beneficiary_id'], $processingId, $r['reason'], $status);
        $stmt->execute();
    }
    $stmt->close();
}

/* SINGLE-label reasons: Missing > Exact > Possible > Sounds-Like */
function build_flagged_rows(array $rows, array $data): array {
    $rows = array_values($rows);
    $flags = [];
    $set = function(int $idx, string $k) use (&$flags){ if(!isset($flags[$idx]))$flags[$idx]=[]; $flags[$idx][$k]=true; };

    $byId = [];
    foreach ($rows as $i=>$r) if(!empty($r['beneficiary_id'])) $byId[$r['beneficiary_id']][]=$i;

    foreach (($data['missing_data'] ?? []) as $m) {
        $matched=false;
        if (!empty($m['beneficiary_id']) && isset($byId[$m['beneficiary_id']])) {
            foreach ($byId[$m['beneficiary_id']] as $idx) { $set($idx,'missing'); $matched=true; }
        }
        if(!$matched){
            foreach($rows as $i=>$r){
                if (mb_strtolower($r['first_name']??'')===mb_strtolower($m['first_name']??'')
                 && mb_strtolower($r['last_name'] ??'')===mb_strtolower($m['last_name'] ??'')
                 && (($r['birth_date'] ?? null)==($m['birth_date'] ?? null))) {
                    $set($i,'missing');
                }
            }
        }
    }
    foreach (($data['exact_duplicates'] ?? []) as $p){ if(isset($p['row1_index']))$set((int)$p['row1_index'],'exact'); if(isset($p['row2_index']))$set((int)$p['row2_index'],'exact'); }
    foreach (($data['fuzzy_duplicates'] ?? []) as $p){ if(isset($p['row1_index']))$set((int)$p['row1_index'],'possible'); if(isset($p['row2_index']))$set((int)$p['row2_index'],'possible'); }
    foreach (($data['sounds_like_duplicates'] ?? []) as $p){ if(isset($p['row1_index']))$set((int)$p['row1_index'],'sounds'); if(isset($p['row2_index']))$set((int)$p['row2_index'],'sounds'); }

    $out=[];
    foreach($flags as $idx=>$f){
        if(!isset($rows[$idx])) continue;
        $r=$rows[$idx];
        if(!empty($f['missing']))      $label='Missing Data';
        elseif(!empty($f['exact']))    $label='Exact Duplicate';
        elseif(!empty($f['possible'])) $label='Possible Duplicate';
        elseif(!empty($f['sounds']))   $label='Sounds-Like Duplicate';
        else continue;

        $out[]=[
            'beneficiary_id'=>$r['beneficiary_id']??'',
            'list_id'=>$r['list_id']??'',
            'full_name'=>trim(($r['first_name']??'').' '.($r['middle_name']??'').' '.($r['last_name']??'')),
            'birth_date'=>$r['birth_date']??'',
            'region'=>$r['region']??'',
            'province'=>$r['province']??'',
            'city'=>$r['city']??'',
            'barangay'=>$r['barangay']??'',
            'marital_status'=>$r['marital_status']??'',
            'reason'=>$label,
        ];
    }
    return $out;
}

/* ----------------------- Combined CSV download (ALL) ----------------------- */
if (isset($_GET['download']) && $_GET['download']==='1') {
    $listIds = $lists; // from session
    $combined = [];

    foreach ($listIds as $idRaw) {
        if (!ctype_digit((string)$idRaw)) continue;
        $lid = (int)$idRaw;

        $rows    = fetch_rows_for_list($conn, $lid);
        $data    = run_python_analysis($rows);
        $flagged = is_array($data) ? build_flagged_rows($rows, $data) : [];

        // still save to DB per list
        save_flagged_rows($conn, $lid, $flagged);

        // add fileName so CSV shows origin
        $fileName = fetch_filename_for_list($conn, $lid);
        foreach ($flagged as &$r) { $r['source_file'] = $fileName; }
        unset($r);

        $combined = array_merge($combined, $flagged);
    }

    $safeName = 'all_lists_flagged_' . date('Ymd_His');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$safeName.'.csv');
    $out = fopen('php://output','w');
    fputcsv($out, ['beneficiary_id','list_id','source_file','full_name','birth_date','region','province','city','barangay','marital_status','reason']);
    foreach ($combined as $r) {
        fputcsv($out, [
            $r['beneficiary_id'],$r['list_id'],$r['source_file'] ?? '',
            $r['full_name'],$r['birth_date'],$r['region'],$r['province'],
            $r['city'],$r['barangay'],$r['marital_status'],$r['reason']
        ]);
    }
    fclose($out);
    exit;
}

/* ------------------- Process ALL lists and aggregate ------------------- */
$overall = [
    'total_records' => 0,
    'exact_duplicates_count' => 0,
    'fuzzy_duplicates_count' => 0,
    'sounds_like_count' => 0,
];
$allFlagged = [];
$perList = []; // for small per-list chips if you want to show

foreach ($lists as $listIdRaw) {
    if (!ctype_digit((string)$listIdRaw)) continue;
    $listId   = (int)$listIdRaw;
    $fileName = fetch_filename_for_list($conn, $listId);
    $rows     = fetch_rows_for_list($conn, $listId);
    $data     = run_python_analysis($rows);

    if (!is_array($data) || isset($data['error'])) {
        // still add counts we can
        $perList[] = ['list_id'=>$listId, 'fileName'=>$fileName, 'error'=>$data['error'] ?? 'Analysis failed.', 'summary'=>['total_records'=>count($rows),'exact_duplicates_count'=>0,'fuzzy_duplicates_count'=>0,'sounds_like_count'=>0]];
        $overall['total_records'] += count($rows);
        continue;
    }

    $summary = $data['summary'] ?? ['total_records'=>count($rows),'exact_duplicates_count'=>0,'fuzzy_duplicates_count'=>0,'sounds_like_count'=>0];
    $flagged = build_flagged_rows($rows, $data);

    // Save to DB per list
    save_flagged_rows($conn, $listId, $flagged);

    // Tag each flagged with source filename
    foreach ($flagged as &$r) { $r['source_file'] = $fileName; }
    unset($r);

    $allFlagged = array_merge($allFlagged, $flagged);
    $overall['total_records']             += (int)($summary['total_records'] ?? 0);
    $overall['exact_duplicates_count']    += (int)($summary['exact_duplicates_count'] ?? 0);
    $overall['fuzzy_duplicates_count']    += (int)($summary['fuzzy_duplicates_count'] ?? 0);
    $overall['sounds_like_count']         += (int)($summary['sounds_like_count'] ?? 0);

    $perList[] = ['list_id'=>$listId,'fileName'=>$fileName,'error'=>null,'summary'=>$summary];
}
?>
<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<style>
  /* --- kill the big gradient strips that "cut" the page --- */
.content-header { display: none !important; }
.content-wrapper::before,
.content-wrapper::after,
.content::before,
.content::after { content: none !important; display: none !important; }

/* --- WIDTH of your sidebar (adjust if yours is 256px) --- */
:root { --sidebar-w: 250px; }

/* Desktop */
@media (min-width: 992px) {
  .main-sidebar { width: var(--sidebar-w) !important; }
  .main-header, .content-wrapper, .main-footer { margin-left: var(--sidebar-w) !important; }
  body.sidebar-mini:not(.sidebar-collapse) .content-wrapper,
  body:not(.sidebar-collapse) .content-wrapper { margin-left: var(--sidebar-w) !important; }
}

/* Mobile/tablet */
@media (max-width: 991.98px) {
  .main-header, .content-wrapper, .main-footer { margin-left: 0 !important; }
}

.review-page { max-width: 1180px; padding: 18px 12px 28px; margin: 0 auto; }
.page-title  { font-size: 22px; font-weight: 700; margin-bottom: 10px; }

.ca-card  { border: 1px solid #e5e7eb; border-radius: 10px; background:#fff; }
.ca-body  { padding: 16px; }
.chip     { display:inline-block; font-size:.75rem; background:#f3f4f6; color:#374151; padding:4px 8px; border-radius:999px; }
.sum-list { margin: 6px 0 0; padding-left: 18px; }
.sum-list li { margin: 2px 0; }

.table-wrap { border:1px solid #e5e7eb; border-radius:8px; overflow:auto; }
.table-sm th, .table-sm td { padding:.45rem .6rem; font-size:.875rem; }
.table thead th { background:#f9fafb; position:sticky; top:0; z-index:1; }

.card-footer { padding:10px 16px; border-top:1px solid #e5e7eb; background:#fafafa;
               border-bottom-left-radius:10px; border-bottom-right-radius:10px; }

.mb-14 { margin-bottom:14px; }
.mb-28 { margin-bottom:28px; }

.badge-list { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
.badge { background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; border-radius:999px; padding:3px 8px; font-size:.72rem; }
.alert-tight { padding:8px 10px; margin:6px 0 0; }
</style>

<div class="content-wrapper">
  <section class="content">
    <div class="container-fluid review-page">
      <div class="page-title">Review Summary (All Lists)</div>

      <?php if (empty($lists)): ?>
        <div class="ca-card"><div class="ca-body">No uploaded lists found or processing failed.</div></div>
      <?php else: ?>

        <!-- ONE SUMMARY FOR ALL -->
        <div class="ca-card mb-14">
          <div class="ca-body">
            <div class="d-flex align-items-center" style="gap:8px;">
              <span class="chip">Summary</span>
              <small>Combined across <?= count($lists) ?> list(s)</small>
            </div>
            <ul class="sum-list">
              <li><strong>Total Records Processed:</strong> <?= (int)$overall['total_records'] ?></li>
              <li><strong>Exact Duplicates:</strong> <?= (int)$overall['exact_duplicates_count'] ?></li>
              <li><strong>Possible Duplicates:</strong> <?= (int)$overall['fuzzy_duplicates_count'] ?></li>
              <li><strong>Sounds-Like Duplicates:</strong> <?= (int)$overall['sounds_like_count'] ?></li>
            </ul>

            <!-- Optional: show small per-list chips -->
            <div class="badge-list">
              <?php foreach ($perList as $pl): ?>
                <span class="badge" title="List ID: <?= (int)$pl['list_id'] ?>">
                  <?= htmlspecialchars($pl['fileName']) ?>
                  <?php if (!empty($pl['error'])): ?> — error<?php endif; ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- ONE FLAGGED TABLE FOR ALL -->
        <div class="ca-card mb-28">
          <div class="ca-body">
            <div class="d-flex align-items-center mb-2" style="gap:8px;">
              <span class="chip">Flagged Records</span>
              <small>Combined</small>
            </div>

            <?php if (empty($allFlagged)): ?>
              <div class="alert alert-success alert-tight mb-0">No issues found 🎉</div>
            <?php else: ?>
              <div class="table-wrap">
                <table class="table table-sm table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Beneficiary ID</th>
                      <th>List ID</th>
                      <th>Source File</th>
                      <th>Full Name</th>
                      <th>Birth Date</th>
                      <th>Region</th>
                      <th>Province</th>
                      <th>City</th>
                      <th>Barangay</th>
                      <th>Marital Status</th>
                      <th>Reason</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($allFlagged as $r): ?>
                      <tr>
                        <td><?= htmlspecialchars($r['beneficiary_id']) ?></td>
                        <td><?= htmlspecialchars($r['list_id']) ?></td>
                        <td><?= htmlspecialchars($r['source_file'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['full_name']) ?></td>
                        <td><?= htmlspecialchars($r['birth_date']) ?></td>
                        <td><?= htmlspecialchars($r['region']) ?></td>
                        <td><?= htmlspecialchars($r['province']) ?></td>
                        <td><?= htmlspecialchars($r['city']) ?></td>
                        <td><?= htmlspecialchars($r['barangay']) ?></td>
                        <td><?= htmlspecialchars($r['marital_status']) ?></td>
                        <td><?= htmlspecialchars($r['reason']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <div class="card-footer">
            <!-- one combined download (no list_id needed) -->
            <a class="btn btn-sm btn-outline-primary" href="?download=1">
              Download All Flagged Records
            </a>
          </div>
        </div>

      <?php endif; ?>

    </div>
  </section>
</div>

<?php include("./includes/footer.php"); ?>
