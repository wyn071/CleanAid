<?php
session_start();
include('../../dB/config.php');

if (!empty($_POST['lists'])) {
    $_SESSION['uploaded_lists'] = $_POST['lists'];
}

$lists = $_SESSION['uploaded_lists'] ?? [];
$results = [];

foreach ($lists as $listId) {
    // Fetch beneficiaries for this list
    $sql = "SELECT * FROM beneficiary WHERE list_id = '$listId'";
    $res = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }

    // Run Python script
    $scriptPath = __DIR__ . "/../../clean_data.py";
    $jsonInput = json_encode($rows);

    $command = "python3 " . escapeshellarg($scriptPath);
    $descriptorspec = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ];
    $process = proc_open($command, $descriptorspec, $pipes);
    fwrite($pipes[0], $jsonInput);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($process);

    $data = json_decode($output, true);

    $results[] = [
        'list_id' => $listId,
        'data' => $data
    ];
}
?>

<?php include("./includes/header.php"); ?>
<?php include("./includes/topbar.php"); ?>
<?php include("./includes/sidebar.php"); ?>

<main class="main bg-body-tertiary" style="min-height: 100vh;">
  <section class="container py-5">
    <h2 class="fw-bold mb-4">Review Summary</h2>

    <?php if (!empty($results)): ?>
      <div class="tab-content">
        <?php foreach ($results as $i => $result): 
          $data = $result['data'] ?? [];
          $listId = $result['list_id'];
          $res = mysqli_query($conn, "SELECT fileName FROM beneficiarylist WHERE list_id='$listId'");
          $meta = mysqli_fetch_assoc($res);
        ?>
        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="list<?= $i ?>" role="tabpanel">

          <!-- Summary Card -->
          <div class="card border-0 rounded-4 shadow-sm mb-4 p-4">
            <h5 class="mb-3">Summary — <?= htmlspecialchars($meta['fileName'] ?? "List #$listId") ?></h5>
            <ul class="list-unstyled">
              <li><strong>Total Records Processed:</strong> <?= $data['summary']['total_records'] ?? 0 ?></li>
              <li><strong>Exact Duplicates:</strong> <?= $data['summary']['exact_duplicates_count'] ?? 0 ?></li>
              <li><strong>Possible Duplicates:</strong> <?= $data['summary']['fuzzy_duplicates_count'] ?? 0 ?></li>
              <li><strong>Sounds-Like Duplicates:</strong> <?= $data['summary']['sounds_like_count'] ?? 0 ?></li>
            </ul>
          </div>

          <!-- Flagged Records -->
          <div class="card border-0 rounded-4 shadow-sm p-4">
            <h5 class="mb-3">Flagged Records</h5>
            <?php if (!empty($data['missing_data']) || !empty($data['exact_duplicates']) || !empty($data['fuzzy_duplicates']) || !empty($data['sounds_like_duplicates'])): ?>
              <pre><?php echo json_encode($data, JSON_PRETTY_PRINT); ?></pre>
            <?php else: ?>
              <div class="alert alert-info">✅ No flagged rows found for this list.</div>
            <?php endif; ?>
          </div>

        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning">No uploaded lists found or processing failed.</div>
    <?php endif; ?>
  </section>
</main>

<?php include("./includes/footer.php"); ?>
