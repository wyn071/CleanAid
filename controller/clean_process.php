<?php
session_start();
include('../dB/config.php');
header('Content-Type: application/json');

// --- START CLEANING ---
if (isset($_GET['start'])) {
    $_SESSION['clean_progress'] = 0;
    $_SESSION['clean_message']  = "Cleaning started...";

    // Clear old duplicate records before new scan
    $conn->query("DELETE FROM duplicaterecord");

    // Fetch all beneficiaries
    $beneficiaries = $conn->query("SELECT * FROM beneficiary");
    $processed = 0;
    $total = $beneficiaries->num_rows;

    while ($row = $beneficiaries->fetch_assoc()) {
        $reason = "";

        // 1️⃣ Missing data check
        if (empty($row['first_name']) || empty($row['last_name']) || empty($row['birth_date'])) {
            $reason = "missing";
        } else {
            // 2️⃣ Check for possible duplicates
            $dupQuery = $conn->prepare("
                SELECT beneficiary_id, first_name, last_name, birth_date 
                FROM beneficiary 
                WHERE beneficiary_id != ? 
                AND first_name = ? AND last_name = ?
            ");
            $dupQuery->bind_param("iss", $row['beneficiary_id'], $row['first_name'], $row['last_name']);
            $dupQuery->execute();
            $dupResult = $dupQuery->get_result();

            while ($dup = $dupResult->fetch_assoc()) {
                if ($dup['birth_date'] === $row['birth_date']) {
                    $reason = "exact";
                } elseif (similar_text($dup['first_name'], $row['first_name']) >= 80 ||
                          similar_text($dup['last_name'], $row['last_name']) >= 80) {
                    $reason = "sounds-like";
                } else {
                    $reason = "possible";
                }
            }
        }

        // 3️⃣ Insert duplicate record if flagged
        if (!empty($reason)) {
            $stmt = $conn->prepare("
                INSERT INTO duplicaterecord (beneficiary_id, flagged_reason, status) 
                VALUES (?, ?, 'pending')
            ");
            $stmt->bind_param("is", $row['beneficiary_id'], $reason);
            $stmt->execute();
        }

        // 4️⃣ Progress bar update
        $processed++;
        $_SESSION['clean_progress'] = intval(($processed / $total) * 100);
        $_SESSION['clean_message']  = "Processing $processed of $total...";
    }

    echo json_encode(["success" => true, "message" => "Cleaning completed"]);
    exit;
}

// --- PROGRESS CHECK ---
if (isset($_GET['progress'])) {
    $percent = $_SESSION['clean_progress'] ?? 0;
    $message = $_SESSION['clean_message'] ?? "Waiting...";
    echo json_encode(["percent" => $percent, "message" => $message]);
    exit;
}
?>
