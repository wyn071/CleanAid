<?php
session_start();
include("../../dB/config.php");
include("./includes/header.php");
include("./includes/topbar.php");
include("./includes/sidebar.php");

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    $_SESSION['message'] = "Unauthorized access.";
    $_SESSION['code'] = "error";
    header("Location: ../../login.php");
    exit();
}

// Get latest list_id for user
$listQuery = $conn->prepare("SELECT list_id FROM beneficiarylist WHERE user_id = ? ORDER BY date_submitted DESC LIMIT 1");
$listQuery->bind_param("i", $user_id);
$listQuery->execute();
$listResult = $listQuery->get_result();
$list_id = $listResult->fetch_assoc()['list_id'] ?? null;

$flaggedRecords = [];

if ($list_id) {
    // Get all processing_ids for the given list_id
    $processingQuery = $conn->prepare("SELECT processing_id FROM processing_engine WHERE list_id = ?");
    $processingQuery->bind_param("s", $list_id);
    $processingQuery->execute();
    $processingResult = $processingQuery->get_result();

    $processing_ids = [];
    while ($row = $processingResult->fetch_assoc()) {
        $processing_ids[] = $row['processing_id'];
    }

    if (!empty($processing_ids)) {
        // Build dynamic placeholders
        $placeholders = implode(',', array_fill(0, count($processing_ids), '?'));
        $types = str_repeat('i', count($processing_ids));

        // Prepare the statement dynamically
        $issueQuery = $conn->prepare("
            SELECT b.*, d.flagged_reason 
            FROM duplicaterecord d
            JOIN beneficiary b ON d.beneficiary_id = b.beneficiary_id
            WHERE d.processing_id IN ($placeholders)
            ORDER BY b.beneficiary_id
        ");
        $issueQuery->bind_param($types, ...$processing_ids);
        $issueQuery->execute();
        $issueResult = $issueQuery->get_result();

        while ($row = $issueResult->fetch_assoc()) {
            $bid = $row['beneficiary_id'];
            if (!isset($flaggedRecords[$bid])) {
                $flaggedRecords[$bid] = [
                    'beneficiary_id' => $row['beneficiary_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'birth_date' => $row['birth_date'],
                    'barangay' => $row['barangay'],
                    'region' => $row['region'],
                    'province' => $row['province'],
                    'city' => $row['city'],
                    'marital_status' => $row['marital_status'],
                    'reasons' => []
                ];
            }

            // Avoid duplicate reasons
            if (!in_array($row['flagged_reason'], $flaggedRecords[$bid]['reasons'])) {
                $flaggedRecords[$bid]['reasons'][] = $row['flagged_reason'];
            }
        }

        // Store flagged records for export
        $_SESSION['flagged_records'] = $flaggedRecords;
    }
}

// Summary counts
$summary = $_SESSION['cleaning_result'] ?? [
    'duplicates' => 0,
    'missing' => 0,
    'cleaned' => 0
];
?>

<main id="main" class="main">
  <section class="container py-5">
    <h2 class="fw-bold mb-4">Review Issues</h2>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <h5 class="card-title">Summary</h5>
        <ul class="mb-0">
          <li><strong>Duplicates Found:</strong> <?= (int)$summary['duplicates'] ?></li>
          <li><strong>Missing Fields:</strong> <?= (int)$summary['missing'] ?></li>
          <li><strong>Total Records Processed:</strong> <?= (int)$summary['cleaned'] ?></li>
        </ul>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Flagged Records</h5>
        <?php if (count($flaggedRecords) > 0): ?>
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
                  <th>Marital_status</th>
                  <th>Reason(s)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($flaggedRecords as $record): ?>
                  <tr>
                    <td><?= htmlspecialchars($record['beneficiary_id']) ?></td>
                    <td><?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></td>
                    <td><?= htmlspecialchars($record['birth_date']) ?></td>
                    <td><?= htmlspecialchars($record['region']) ?></td>
                    <td><?= htmlspecialchars($record['province']) ?></td>
                    <td><?= htmlspecialchars($record['city']) ?></td>
                    <td><?= htmlspecialchars($record['barangay']) ?></td>
                    <td><?= htmlspecialchars($record['marital_status']) ?></td>
                    <td class="text-danger">
                      <?= implode('<br>', array_map('htmlspecialchars', array_unique($record['reasons']))) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <form method="post" action="export_issues.php">
            <button type="submit" class="btn btn-outline-secondary mt-3">
              <i class="bi bi-download"></i> Download Flagged Records (CSV)
            </button>
          </form>
        <?php else: ?>
          <p class="text-muted mb-0">No flagged issues found in this file.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include("./includes/footer.php"); ?>
