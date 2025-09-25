<?php
session_start();
require_once __DIR__ . "/../../dB/config.php";

if (!isset($_GET['list_id'])) { http_response_code(400); echo "Missing list_id"; exit; }
$listId = (int)$_GET['list_id'];

// Latest processing for this list
$st = $pdo->prepare("SELECT processing_id, status, processing_date FROM processing_engine WHERE list_id=? ORDER BY processing_id DESC LIMIT 1");
$st->execute([$listId]);
$proc = $st->fetch(PDO::FETCH_ASSOC);

if (!$proc) {
    echo "<h2>No processing run found for list_id $listId.</h2>";
    exit;
}
$processingId = (int)$proc['processing_id'];

// Counts (derive from existing tables)
$st = $pdo->prepare("SELECT COUNT(*) FROM beneficiary WHERE list_id=?");
$st->execute([$listId]);
$total = (int)$st->fetchColumn();

$st = $pdo->prepare("SELECT COUNT(DISTINCT beneficiary_id) FROM duplicaterecord WHERE processing_id=?");
$st->execute([$processingId]);
$flaggedCount = (int)$st->fetchColumn();

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Review — List <?= htmlspecialchars($listId) ?></title>
  <style>
    body { font-family: system-ui, Arial, sans-serif; margin: 24px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; }
    th { background: #f5f5f5; text-align: left; }
    .badge { display:inline-block; padding:4px 8px; border-radius:10px; font-size:12px; }
    .ok { background:#e7f6ed; color:#1e7e34; }
    .err { background:#fdecea; color:#b02a37; }
  </style>
</head>
<body>
  <h1>Cleaning Results — List <?= htmlspecialchars($listId) ?></h1>

  <p>
    Status:
    <?php if ($proc['status']==='complete'): ?>
      <span class="badge ok">Complete</span>
    <?php elseif ($proc['status']==='running'): ?>
      <span class="badge">Running</span>
    <?php else: ?>
      <span class="badge err">Error</span>
    <?php endif; ?>
    &nbsp; Processing ID: <strong><?= htmlspecialchars($processingId) ?></strong>
    &nbsp; Date: <?= htmlspecialchars($proc['processing_date']) ?>
  </p>

  <h3>Summary</h3>
  <p>Total records: <strong><?= $total ?></strong></p>
  <p>Flagged beneficiaries (distinct): <strong><?= $flaggedCount ?></strong></p>

  <?php if ($flaggedCount === 0): ?>
    <p>No issues found for this run.</p>
  <?php else: ?>
    <h3>Flagged Rows (showing first 200)</h3>
    <table>
      <tr>
        <th>Beneficiary ID</th>
        <th>Reason</th>
        <th>First</th>
        <th>Middle</th>
        <th>Last</th>
        <th>Ext</th>
        <th>Birth Date</th>
        <th>Region</th>
        <th>Province</th>
        <th>City</th>
        <th>Barangay</th>
      </tr>
      <?php
        // join duplicaterecord -> beneficiary for details
        $q = $pdo->prepare("
          SELECT dr.beneficiary_id, dr.flagged_reason,
                 b.first_name, b.middle_name, b.last_name, b.ext_name,
                 b.birth_date, b.region, b.province, b.city, b.barangay
          FROM duplicaterecord dr
          JOIN beneficiary b ON b.beneficiary_id = dr.beneficiary_id
          WHERE dr.processing_id = ?
          GROUP BY dr.beneficiary_id, dr.flagged_reason,
                   b.first_name, b.middle_name, b.last_name, b.ext_name,
                   b.birth_date, b.region, b.province, b.city, b.barangay
          LIMIT 200
        ");
        $q->execute([$processingId]);
        while ($r = $q->fetch(PDO::FETCH_ASSOC)):
      ?>
        <tr>
          <td><?= htmlspecialchars($r['beneficiary_id']) ?></td>
          <td><?= htmlspecialchars($r['flagged_reason']) ?></td>
          <td><?= htmlspecialchars($r['first_name']) ?></td>
          <td><?= htmlspecialchars($r['middle_name']) ?></td>
          <td><?= htmlspecialchars($r['last_name']) ?></td>
          <td><?= htmlspecialchars($r['ext_name']) ?></td>
          <td><?= htmlspecialchars($r['birth_date']) ?></td>
          <td><?= htmlspecialchars($r['region']) ?></td>
          <td><?= htmlspecialchars($r['province']) ?></td>
          <td><?= htmlspecialchars($r['city']) ?></td>
          <td><?= htmlspecialchars($r['barangay']) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>
</body>
</html>
