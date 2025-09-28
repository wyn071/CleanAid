<?php
// review.php
require_once 'db.php';

$processingId = isset($_GET['processing_id']) ? (int)$_GET['processing_id'] : 0;
$listId = isset($_GET['list_id']) ? (int)$_GET['list_id'] : 0;

if ($processingId <= 0 || $listId <= 0) {
    http_response_code(400);
    echo "processing_id and list_id are required.";
    exit;
}

// Get status
$st = $conn->prepare("SELECT status, processing_date FROM processing_engine WHERE processing_id=?");
$st->bind_param('i', $processingId);
$st->execute();
$eng = $st->get_result()->fetch_assoc();
$st->close();

// Count flagged
$c = $conn->prepare("SELECT COUNT(*) AS c FROM duplicaterecord WHERE processing_id=?");
$c->bind_param('i', $processingId);
$c->execute();
$flagCount = (int)$c->get_result()->fetch_assoc()['c'];
$c->close();

// Fetch flagged with beneficiary details
$sql = "
SELECT dr.duplicate_id, dr.flagged_reason, b.beneficiary_id,
       b.first_name, b.middle_name, b.last_name, b.ext_name,
       b.birth_date, b.region, b.province, b.city, b.barangay
FROM duplicaterecord dr
JOIN beneficiary b ON b.beneficiary_id = dr.beneficiary_id
WHERE dr.processing_id=?
ORDER BY dr.duplicate_id ASC
LIMIT 1000
";
$st = $conn->prepare($sql);
$st->bind_param('i', $processingId);
$st->execute();
$res = $st->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) { $rows[] = $r; }
$st->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CleanAid — Review Duplicates</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 2rem; }
    .grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1rem; }
    .card { grid-column: span 12; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 12px; }
    .muted { color: #6b7280; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: .5rem .6rem; border-bottom: 1px solid #f3f4f6; text-align: left; font-size: .95rem; }
    th { background: #f9fafb; }
    .pill { display:inline-block; padding: .2rem .5rem; border-radius: 999px; background: #eef2ff; color: #3730a3; font-size: .8rem; }
  </style>
</head>
<body>
  <div class="grid">
    <div class="card">
      <h2>Processing #<?= htmlspecialchars($processingId) ?> — List #<?= htmlspecialchars($listId) ?></h2>
      <p class="muted">
        Status: <span class="pill"><?= htmlspecialchars($eng['status'] ?? 'UNKNOWN') ?></span> •
        Date: <?= htmlspecialchars($eng['processing_date'] ?? '') ?> •
        Flagged: <strong><?= $flagCount ?></strong>
      </p>
      <p><a href="clean.php">← Import another file</a></p>
    </div>

    <div class="card">
      <h3>Flagged Records (first 1000)</h3>
      <?php if (!$rows): ?>
        <p class="muted">No duplicates were flagged for this run.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Beneficiary</th>
              <th>DOB</th>
              <th>Location</th>
              <th>Reason</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $i => $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['duplicate_id']) ?></td>
                <td>
                  <?= htmlspecialchars(trim(($r['last_name'] ?? '').', '.($r['first_name'] ?? '').' '.($r['middle_name'] ?? ''))) ?>
                  <?= $r['ext_name'] ? ' '.htmlspecialchars($r['ext_name']) : '' ?>
                  <div class="muted">ID: <?= htmlspecialchars($r['beneficiary_id']) ?></div>
                </td>
                <td><?= htmlspecialchars($r['birth_date']) ?></td>
                <td><?= htmlspecialchars($r['region'] . ', ' . $r['province'] . ', ' . $r['city'] . ', ' . $r['barangay']) ?></td>
                <td><?= htmlspecialchars($r['flagged_reason']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
