<?php
session_start();
include(__DIR__ . "/../dB/config.php");

$exact_duplicates = 0;
$possible_duplicates = 0;
$sound_duplicates = 0;
$cleaned = 0;

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $_SESSION['message'] = "Unauthorized access.";
    $_SESSION['code'] = "error";
    header("Location: ../../login.php");
    exit();
}

// Get latest list_id
$listQuery = $conn->prepare("SELECT list_id FROM beneficiarylist WHERE user_id = ? ORDER BY date_submitted DESC LIMIT 1");
$listQuery->bind_param("i", $user_id);
$listQuery->execute();
$listResult = $listQuery->get_result();
$list_id = $listResult->fetch_assoc()['list_id'] ?? null;

if (!$list_id) {
    $_SESSION['message'] = "No uploaded list found.";
    $_SESSION['code'] = "warning";
    header("Location: ../view/admin/clean.php");
    exit();
}

// Set list to processing
$conn->query("UPDATE beneficiarylist SET status = 'processing' WHERE list_id = '$list_id'");

function insertIssue($conn, $beneficiary_id, $processing_id, $reason) {
    $status = "unresolved";
    $stmt = $conn->prepare("INSERT INTO duplicaterecord (beneficiary_id, processing_id, flagged_reason, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $beneficiary_id, $processing_id, $reason, $status);
    $stmt->execute();
}

try {
    $processing_date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO processing_engine (list_id, processing_date, status) VALUES (?, ?, 'completed')");
    $stmt->bind_param("ss", $list_id, $processing_date);
    $stmt->execute();
    $processing_id = $conn->insert_id;

    $beneficiaries = [];
    $result = $conn->query("SELECT * FROM beneficiary WHERE list_id = '$list_id'");
    while ($row = $result->fetch_assoc()) {
        $beneficiaries[] = $row;
    }

    foreach ($beneficiaries as $target) {
        $beneficiary_id = $target['beneficiary_id'];
        $first_name = strtolower(trim($target['first_name']));
        $last_name = strtolower(trim($target['last_name']));
        $birth_date = trim($target['birth_date']);
        $hasIssue = false;

        // 1. Exact Match
        $matchCount = 0;
        foreach ($beneficiaries as $compare) {
            if (
                strtolower(trim($compare['first_name'])) === $first_name &&
                strtolower(trim($compare['last_name'])) === $last_name &&
                trim($compare['birth_date']) === $birth_date
            ) {
                $matchCount++;
            }
        }
        if ($matchCount > 1) {
            insertIssue($conn, $beneficiary_id, $processing_id, "Exact duplicate (Name + Birthdate)");
            $exact_duplicates++;
            $hasIssue = true;
        }

        // 2. Soundex
        foreach ($beneficiaries as $compare) {
            if (
                $compare['beneficiary_id'] !== $beneficiary_id &&
                soundex($compare['first_name']) === soundex($target['first_name']) &&
                soundex($compare['last_name']) === soundex($target['last_name']) &&
                $compare['birth_date'] === $birth_date
            ) {
                insertIssue($conn, $beneficiary_id, $processing_id, "Sounds-like match (SOUNDEX)");
                $sound_duplicates++;
                $hasIssue = true;
                break;
            }
        }

        // 3. Fuzzy Match
        $scriptPath = realpath(__DIR__ . "/../scripts/cleaner.py");
        $data = [
            "target" => $target,
            "compare_to" => array_filter($beneficiaries, fn($b) => $b['beneficiary_id'] !== $beneficiary_id)
        ];
        $command = escapeshellcmd("python \"$scriptPath\" '" . json_encode($data) . "'");
        $output = shell_exec($command);
        $matches = json_decode($output, true);

        if ($matches && is_array($matches)) {
            foreach ($matches as $match) {
                if ($match['similarity'] > 85 || $match['jaro_winkler'] > 0.90) {
                    insertIssue($conn, $beneficiary_id, $processing_id, "Possible duplicate (Fuzzy/Jaro-Winkler)");
                    $possible_duplicates++;
                    $hasIssue = true;
                    break;
                }
            }
        }

        if (!$hasIssue) $cleaned++;
    }

    $conn->query("UPDATE beneficiarylist SET status = 'completed' WHERE list_id = '$list_id'");
} catch (Exception $e) {
    $conn->query("UPDATE beneficiarylist SET status = 'error' WHERE list_id = '$list_id'");
}

$_SESSION['cleaning_result'] = [
    "exact" => $exact_duplicates,
    "possible" => $possible_duplicates,
    "sound" => $sound_duplicates,
    "cleaned" => $cleaned
];

header("Location: ../view/admin/review.php");
exit();
