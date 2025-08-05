<?php
session_start();

if (!empty($_POST['files'])) {
    $_SESSION['uploaded_files'] = $_POST['files'];
}

$files = $_SESSION['uploaded_files'] ?? [];
$results = [];

foreach ($files as $file) {
    $filePath = __DIR__ . "/../../uploads/" . $file;
    $scriptPath = __DIR__ . "/../../clean_data.py";
    $command = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($filePath);
    $output = shell_exec($command);
$data = json_decode($output, true);

// DEBUG: fallback if JSON failed
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    file_put_contents("json_error_log.txt", json_last_error_msg() . "\n\n" . $output);
}

    $data = json_decode($output, true);

    $results[] = [
        'file' => $file,
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
      <!-- File Tabs -->
      <ul class="nav nav-tabs mb-4" role="tablist">
        <?php foreach ($results as $i => $result): ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#file<?= $i ?>" type="button" role="tab">
              <?= substr($result['file'], 0, 5) ?>..
            </button>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="tab-content">
        <?php foreach ($results as $i => $result): 
          $data = $result['data'];
        ?>
        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="file<?= $i ?>" role="tabpanel">

          <!-- Summary Card -->
          <div class="card border-0 rounded-4 shadow-sm mb-4 p-4">
            <h5 class="mb-3">Summary — <?= htmlspecialchars($result['file']) ?></h5>
            <ul class="list-unstyled">
              <li><strong>Total Records Processed:</strong> <?= $data['summary']['total_records'] ?? 0 ?></li>
              <li><strong>Exact Duplicates:</strong> <?= $data['summary']['exact_duplicates_count'] ?? 0 ?></li>
              <li><strong>Possible Duplicates:</strong> <?= $data['summary']['fuzzy_duplicates_count'] ?? 0 ?></li>
              <li><strong>Sounds-Like Duplicates:</strong> <?= $data['summary']['sounds_like_count'] ?? 0 ?></li>
            </ul>
          </div>

          <!-- Flagged Records Card -->
          <div class="card border-0 rounded-4 shadow-sm p-4">
            <h5 class="mb-3">Flagged Records — <?= htmlspecialchars($result['file']) ?></h5>

            <?php
            $rows = [];
            $reasonsMap = [];

            // 1. Add missing data
            foreach ($data['missing_data'] ?? [] as $row) {
                $key = md5(json_encode($row));
                $rows[$key] = $row;
                $reasonsMap[$key][] = "Missing Data";
            }

            // 2. Add exact duplicates
            foreach ($data['exact_duplicates'] ?? [] as $row) {
                $key = md5(json_encode($row));
                $rows[$key] = $row;
                $reasonsMap[$key][] = "Exact Duplicate";
            }

            // 3. Collect fuzzy/sounds-like row indexes
            $fuzzyIndexes = [];
            foreach ($data['fuzzy_duplicates'] ?? [] as $pair) {
                $fuzzyIndexes[$pair['row1_index']] = true;
                $fuzzyIndexes[$pair['row2_index']] = true;
            }

            $soundexIndexes = [];
            foreach ($data['sounds_like_duplicates'] ?? [] as $pair) {
                $soundexIndexes[$pair['row1_index']] = true;
                $soundexIndexes[$pair['row2_index']] = true;
            }

            // 4. Read file & append rows by index
            $csvPath = realpath(__DIR__ . "/../../uploads/" . $result['file']);
            if ($csvPath && file_exists($csvPath)) {
                if (($handle = fopen($csvPath, "r")) !== false) {
                    $headers = fgetcsv($handle);
                    $allRows = [];
                    while (($rowData = fgetcsv($handle)) !== false) {
                        $allRows[] = array_combine($headers, $rowData);
                    }
                    fclose($handle);

                    foreach ($allRows as $i => $row) {
                        $key = md5(json_encode($row));
                        if (isset($fuzzyIndexes[$i])) {
                            $rows[$key] = $row;
                            $reasonsMap[$key][] = "Fuzzy Duplicate";
                        }
                        if (isset($soundexIndexes[$i])) {
                            $rows[$key] = $row;
                            $reasonsMap[$key][] = "Sounds-Like Duplicate";
                        }
                    }
                }
            }
            ?>

            <?php if (!empty($rows)): ?>
              <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                  <thead class="table-light">
                    <tr>
                      <?php foreach (array_keys(reset($rows)) as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                      <?php endforeach; ?>
                      <th>Reason(s)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($rows as $key => $row): ?>
                      <tr>
                        <?php foreach ($row as $val): ?>
                          <td><?= htmlspecialchars($val ?? '-') ?></td>
                        <?php endforeach; ?>
                        <td class="text-danger small"><?= implode(', ', $reasonsMap[$key]) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- Download -->
              <form method="POST" action="../../controller/export_issues.php">
                <input type="hidden" name="filename" value="<?= htmlspecialchars($result['file']) ?>">
                <input type="hidden" name="issues" value='<?= json_encode(array_values($rows)) ?>'>
                <button class="btn btn-outline-dark mt-3" type="submit">
                  ⬇️ Download Flagged Records
                </button>
              </form>
            <?php else: ?>
              <div class="alert alert-info">✅ No flagged rows found for this file.</div>
            <?php endif; ?>
          </div>

        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning">No uploaded files found or processing failed.</div>
    <?php endif; ?>
  </section>
</main>

<?php include("./includes/footer.php"); ?>
