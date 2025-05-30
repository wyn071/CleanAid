<?php
session_start();
include("../../dB/config.php");

$duplicates = 0;
$missing = 0;
$cleaned = 0;

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $_SESSION['message'] = "Unauthorized access.";
    $_SESSION['code'] = "error";
    header("Location: ../../login.php");
    exit();
}

// Get latest list_id for this user
$listQuery = $conn->prepare("SELECT list_id FROM beneficiarylist WHERE user_id = ? ORDER BY date_submitted DESC LIMIT 1");
$listQuery->bind_param("i", $user_id);
$listQuery->execute();
$listResult = $listQuery->get_result();
$list_id = $listResult->fetch_assoc()['list_id'] ?? null;

if (!$list_id) {
    $_SESSION['message'] = "No uploaded list found.";
    $_SESSION['code'] = "warning";
    header("Location: clean.php");
    exit();
}

// Update status to processing
$updateStatus = $conn->prepare("UPDATE beneficiarylist SET status = 'processing' WHERE list_id = ?");
$updateStatus->bind_param("s", $list_id);
$updateStatus->execute();

function insertIssue($conn, $beneficiary_id, $processing_id, $reason) {
    $flagStatus = "unresolved";
    $stmt = $conn->prepare("INSERT INTO duplicaterecord (beneficiary_id, processing_id, flagged_reason, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $beneficiary_id, $processing_id, $reason, $flagStatus);
    $stmt->execute();
}

try {
    // Start processing
    $processing_date = date('Y-m-d H:i:s');
    $status = 'completed';

    $stmt = $conn->prepare("INSERT INTO processing_engine (list_id, processing_date, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $list_id, $processing_date, $status);
    $stmt->execute();
    $processing_id = $conn->insert_id;

    $stmt2 = $conn->prepare("SELECT * FROM beneficiary WHERE list_id = ?");
    $stmt2->bind_param("s", $list_id);
    $stmt2->execute();
    $result = $stmt2->get_result();

    while ($row = $result->fetch_assoc()) {
        $beneficiary_id = $row['beneficiary_id'];
        $first_name = trim($row['first_name']);
        $last_name = trim($row['last_name']);
        $birth_date = trim($row['birth_date']);
        $region = trim($row['region']);
        $province = trim($row['province']);
        $city = trim($row['city']);
        $barangay = trim($row['barangay']);
        $marital_status = trim($row['marital_status']);

        $hasIssue = false;

        // === MISSING FIELDS ===
        if (
            $first_name === '' || $last_name === '' || $birth_date === '' ||
            $region === '' || $province === '' || $city === '' ||
            $barangay === '' || $marital_status === ''
        ) {
            $missing++;
            $hasIssue = true;
            insertIssue($conn, $beneficiary_id, $processing_id, "Missing required field(s)");
        }

        // === EXACT DUPLICATES ===
        $dupCheck = $conn->prepare("SELECT COUNT(*) AS count FROM beneficiary WHERE LOWER(TRIM(first_name)) = ? AND LOWER(TRIM(last_name)) = ? AND birth_date = ? AND list_id = ?");
        $fname = strtolower($first_name);
        $lname = strtolower($last_name);
        $dupCheck->bind_param("ssss", $fname, $lname, $birth_date, $list_id);
        $dupCheck->execute();
        $dupResult = $dupCheck->get_result();
        $dupCount = $dupResult->fetch_assoc()['count'];

        if ($dupCount > 1) {
            $duplicates++;
            $hasIssue = true;
            insertIssue($conn, $beneficiary_id, $processing_id, "Exact duplicate (Name + Birthdate)");
        }

        // === SOUNDS-LIKE ===
        $soundCheck = $conn->prepare("SELECT beneficiary_id FROM beneficiary WHERE SOUNDEX(first_name) = SOUNDEX(?) AND SOUNDEX(last_name) = SOUNDEX(?) AND birth_date = ? AND list_id = ? AND beneficiary_id != ?");
        $soundCheck->bind_param("ssssi", $first_name, $last_name, $birth_date, $list_id, $beneficiary_id);
        $soundCheck->execute();
        $soundResult = $soundCheck->get_result();

        if ($soundResult->num_rows > 0) {
            $duplicates++;
            $hasIssue = true;
            insertIssue($conn, $beneficiary_id, $processing_id, "Sounds-like match (SOUNDEX)");
        }

        if (!$hasIssue) {
            $cleaned++;
        }
    }

    // Mark list as completed
    $updateStatus = $conn->prepare("UPDATE beneficiarylist SET status = 'completed' WHERE list_id = ?");
    $updateStatus->bind_param("s", $list_id);
    $updateStatus->execute();

} catch (Exception $e) {
    $updateStatus = $conn->prepare("UPDATE beneficiarylist SET status = 'error' WHERE list_id = ?");
    $updateStatus->bind_param("s", $list_id);
    $updateStatus->execute();
}

$_SESSION['cleaning_result'] = [
    'duplicates' => $duplicates,
    'missing' => $missing,
    'cleaned' => $cleaned
];

header("Location: review.php");
exit();
