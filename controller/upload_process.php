<?php
session_start();
include('../dB/config.php');
header('Content-Type: application/json');

// Allow longer execution for large uploads
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

/**
 * Insert beneficiaries in chunks of 5000 for performance
 */
function insertBeneficiaries($conn, $rows, $listId, $chunkSize = 5000) {
    $inserted = 0;

    for ($i = 0; $i < count($rows); $i += $chunkSize) {
        $chunk = array_slice($rows, $i, $chunkSize);
        $values = [];

        foreach ($chunk as $row) {
            $first_name   = $conn->real_escape_string($row['first_name'] ?? '');
            $last_name    = $conn->real_escape_string($row['last_name'] ?? '');
            $middle_name  = $conn->real_escape_string($row['middle_name'] ?? '');
            $suffix       = $conn->real_escape_string($row['suffix'] ?? '');
            $birth_date   = $conn->real_escape_string($row['birth_date'] ?? '');
            $region       = $conn->real_escape_string($row['region'] ?? '');
            $province     = $conn->real_escape_string($row['province'] ?? '');
            $city         = $conn->real_escape_string($row['city'] ?? '');
            $barangay     = $conn->real_escape_string($row['barangay'] ?? '');
            $civil_status = $conn->real_escape_string($row['civil_status'] ?? '');

            $values[] = "(
                $listId,
                '$first_name',
                '$last_name',
                '$middle_name',
                '$suffix',
                '$birth_date',
                '$region',
                '$province',
                '$city',
                '$barangay',
                '$civil_status'
            )";
        }

        if (!empty($values)) {
            $sql = "INSERT INTO beneficiary (
                        list_id, first_name, last_name, middle_name, suffix, birth_date,
                        region, province, city, barangay, civil_status
                    ) VALUES " . implode(",", $values);

            if (!$conn->query($sql)) {
                throw new Exception("Insert failed: " . $conn->error);
            }

            $inserted += count($chunk);
        }
    }

    return $inserted;
}

try {
    if (!isset($_FILES['file'])) {
        throw new Exception("No file uploaded.");
    }

    $files = $_FILES['file'];
    $uploadedLists = [];

    // Loop through each uploaded file
    for ($i = 0; $i < count($files['name']); $i++) {
        $tmpName = $files['tmp_name'][$i];
        $fileName = $files['name'][$i];

        if (!is_uploaded_file($tmpName)) {
            throw new Exception("Upload failed for $fileName.");
        }

        // Save file info in beneficiarylist
        $conn->query("INSERT INTO beneficiarylist (fileName, status) VALUES ('$fileName', 'uploaded')");
        $listId = $conn->insert_id;

        // Example: parse CSV (adjust for XLSX if needed)
        $rows = [];
        if (($handle = fopen($tmpName, "r")) !== false) {
            $header = fgetcsv($handle); // skip header
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = [
                    'first_name'   => $data[0] ?? '',
                    'last_name'    => $data[1] ?? '',
                    'middle_name'  => $data[2] ?? '',
                    'suffix'       => $data[3] ?? '',
                    'birth_date'   => $data[4] ?? '',
                    'region'       => $data[5] ?? '',
                    'province'     => $data[6] ?? '',
                    'city'         => $data[7] ?? '',
                    'barangay'     => $data[8] ?? '',
                    'civil_status' => $data[9] ?? ''
                ];
            }
            fclose($handle);
        }

        // Insert rows in chunks
        $totalInserted = insertBeneficiaries($conn, $rows, $listId);

        // Save processing_engine record
        $conn->query("
            INSERT INTO processing_engine (list_id, processing_date, status, total_records, processed_records)
            VALUES ($listId, NOW(), 'uploaded', $totalInserted, 0)
        ");

        $uploadedLists[] = $listId;
    }

    // Save uploaded list IDs in session for clean.php
    $_SESSION['uploaded_lists'] = $uploadedLists;

    echo json_encode(["status" => "success", "lists" => $uploadedLists]);
    exit;

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit;
}
