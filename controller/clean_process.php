<?php
session_start();
include('../dB/config.php');
header('Content-Type: application/json');

// Allow longer execution for big batches
ini_set('max_execution_time', 300);

// --- START CLEANING ---
if (isset($_GET['start'])) {
    $listId = $_SESSION['uploaded_lists'][0] ?? null;
    if (!$listId) {
        echo json_encode(["status" => "error", "message" => "No list selected."]);
        exit;
    }

    // Count total records
    $res = $conn->query("SELECT COUNT(*) as cnt FROM beneficiary WHERE list_id=$listId");
    if (!$res) {
        echo json_encode(["status" => "error", "message" => "DB error: " . $conn->error]);
        exit;
    }
    $total = $res->fetch_assoc()['cnt'] ?? 0;

    // Reset progress
    if (!$conn->query("
        UPDATE processing_engine
        SET status='in_progress',
            total_records=$total,
            processed_records=0,
            last_updated=NOW()
        WHERE list_id=$listId
    ")) {
        echo json_encode(["status" => "error", "message" => "DB error: " . $conn->error]);
        exit;
    }

    echo json_encode(["status" => "started", "total" => $total]);
    exit;
}

// --- PROGRESS CHECK ---
if (isset($_GET['progress'])) {
    $listId = $_SESSION['uploaded_lists'][0] ?? null;
    if (!$listId) {
        echo json_encode(["percent" => 0, "message" => "No list selected"]);
        exit;
    }

    $res = $conn->query("
        SELECT processing_id, total_records, processed_records, status
        FROM processing_engine
        WHERE list_id=$listId
        LIMIT 1
    ");
    if (!$res) {
        echo json_encode(["status" => "error", "message" => "DB error: " . $conn->error]);
        exit;
    }
    $row = $res->fetch_assoc();

    if (!$row) {
        echo json_encode(["percent" => 0, "message" => "No processing record found"]);
        exit;
    }

    $processingId = $row['processing_id'];
    $total        = (int)$row['total_records'];
    $done         = (int)$row['processed_records'];
    $status       = $row['status'];

    // Already completed
    if ($status === 'completed') {
        echo json_encode([
            "percent" => 100,
            "message" => "✅ Cleaning complete!",
            "status"  => "completed"
        ]);
        exit;
    }

    // Batch size reduced for stability
    $batchSize = 1000;

    // Fetch next batch
    $res = $conn->query("
        SELECT beneficiary_id, first_name, last_name, birth_date
        FROM beneficiary
        WHERE list_id=$listId
        LIMIT $batchSize OFFSET $done
    ");
    if (!$res) {
        echo json_encode(["status" => "error", "message" => "DB error: " . $conn->error]);
        exit;
    }

    while ($r = $res->fetch_assoc()) {
        $fname = $conn->real_escape_string($r['first_name']);
        $lname = $conn->real_escape_string($r['last_name']);
        $bdate = $conn->real_escape_string($r['birth_date']);

        $dup = $conn->query("
            SELECT beneficiary_id
            FROM beneficiary
            WHERE list_id=$listId
              AND first_name='$fname'
              AND last_name='$lname'
              AND birth_date='$bdate'
              AND beneficiary_id <> ".$r['beneficiary_id']."
            LIMIT 1
        ");

        if ($dup && $dup->num_rows > 0) {
            $conn->query("
                INSERT IGNORE INTO duplicaterecord 
                (beneficiary_id, processing_id, flagged_reason, status)
                VALUES (".$r['beneficiary_id'].", $processingId, 'Duplicate found', 'flagged')
            ");
        }
    }

    // Update progress
    $newDone = min($done + $batchSize, $total);
    $percent = $total > 0 ? round(($newDone / $total) * 100) : 100;

    $conn->query("
        UPDATE processing_engine
        SET processed_records=$newDone,
            last_updated=NOW()
        WHERE processing_id=$processingId
    ");

    if ($percent >= 100) {
        $conn->query("UPDATE processing_engine SET status='completed' WHERE processing_id=$processingId");
        echo json_encode([
            "percent" => 100,
            "message" => "✅ Cleaning complete!",
            "status"  => "completed"
        ]);
    } else {
        echo json_encode([
            "percent" => $percent,
            "message" => "Cleaning in progress... $percent%",
            "status"  => "in_progress"
        ]);
    }
    exit;
}
