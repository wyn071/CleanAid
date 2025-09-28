<?php
include 'db.php';

// ========== IMPORT FILE AND CREATE LIST ==========
if (isset($_POST['upload'])) {
    $userId = 1; // TODO: set from session
    $listName = $_FILES['file']['name'];
    $filePath = $_FILES['file']['tmp_name'];

    // Create new list entry
    $conn->query("INSERT INTO beneficiarylist (date_submitted, status, user_id) VALUES (NOW(), 'UPLOADED', $userId)");
    $listId = $conn->insert_id;

    // Insert beneficiaries in batches
    $batchSize = 1000;
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle); // skip header
        $batch = [];
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $batch[] = $data;
            if (count($batch) >= $batchSize) {
                insert_batch($batch, $listId, $conn);
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            insert_batch($batch, $listId, $conn);
        }
        fclose($handle);
    }

    // Create processing entry
    $conn->query("INSERT INTO processing_engine (list_id, processing_date, status) VALUES ($listId, NOW(), 'PENDING')");
    $processingId = $conn->insert_id;

    echo "File imported. Processing ID: $processingId";
    exit;
}

// ========== RUN ANALYSIS ==========
if (isset($_GET['run_analysis'])) {
    $processingId = intval($_GET['run_analysis']);

    // Lookup list_id
    $res = $conn->query("SELECT list_id FROM processing_engine WHERE processing_id=$processingId");
    if (!$res || $res->num_rows == 0) {
        die("Invalid processing ID");
    }
    $listId = $res->fetch_assoc()['list_id'];

    // Fetch beneficiaries for this list
    $rows = [];
    $idByIndex = [];
    $q = $conn->prepare("SELECT beneficiary_id, first_name, middle_name, last_name, ext_name,
                                birth_date, region, province, city, barangay
                         FROM beneficiary
                         WHERE list_id=?
                         ORDER BY beneficiary_id ASC");
    $q->bind_param('i', $listId);
    $q->execute();
    $rs = $q->get_result();
    $idx = 0;
    while ($r = $rs->fetch_assoc()) {
        $rows[] = [
            'first_name' => $r['first_name'],
            'middle_name'=> $r['middle_name'],
            'last_name'  => $r['last_name'],
            'ext_name'   => $r['ext_name'],
            'birth_date' => $r['birth_date'],
            'region'     => $r['region'],
            'province'   => $r['province'],
            'city'       => $r['city'],
            'barangay'   => $r['barangay']
        ];
        $idByIndex[$idx] = (int)$r['beneficiary_id'];
        $idx++;
    }
    $q->close();

    // Save debug input
    file_put_contents("/tmp/analyzer_input_$processingId.json", json_encode($rows, JSON_UNESCAPED_UNICODE));

    // Run Python analyzer
    $cmd = "python3 clean_data.py";
    $descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    $proc = proc_open($cmd, $descriptorspec, $pipes, __DIR__);
    if (is_resource($proc)) {
        fwrite($pipes[0], json_encode($rows, JSON_UNESCAPED_UNICODE));
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $ret = proc_close($proc);

        file_put_contents("/tmp/analyzer_out_$processingId.json", $stdout);

        if ($ret !== 0) {
            echo "Analyzer failed: $stderr";
            exit;
        }

        $out = json_decode($stdout, true);

        // Insert flagged duplicates
        $ins = $conn->prepare("INSERT INTO duplicaterecord (beneficiary_id, processing_id, flagged_reason, status) VALUES (?,?,?, 'FLAGGED')");

        foreach (['exact_duplicates','fuzzy_duplicates','sounds_like_duplicates'] as $key) {
            foreach ($out[$key] as $pair) {
                $a = $idByIndex[(int)$pair['row1_index']] ?? null;
                $b = $idByIndex[(int)$pair['row2_index']] ?? null;
                if ($a && $b) {
                    $reason = ucfirst(str_replace('_',' ',$key)) . " with ID $b";
                    $ins->bind_param('iis', $a, $processingId, $reason);
                    $ins->execute();
                }
            }
        }
        $ins->close();

        // Update processing status
        $conn->query("UPDATE processing_engine SET status='DONE' WHERE processing_id=$processingId");

        echo "Analysis complete.";
    }
}

// ========== HELPERS ==========
function insert_batch($batch, $listId, $conn) {
    $ins = $conn->prepare("INSERT INTO beneficiary 
        (list_id, first_name, last_name, middle_name, ext_name, birth_date, region, province, city, barangay, marital_status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($batch as $row) {
        list($first, $last, $middle, $ext, $dob, $region, $province, $city, $barangay, $marital) = $row;
        $ins->bind_param('issssssssss', $listId, $first, $last, $middle, $ext, $dob, $region, $province, $city, $barangay, $marital);
        $ins->execute();
    }
    $ins->close();
}
?>
