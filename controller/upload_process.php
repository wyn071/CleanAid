<?php
session_start();
include('../dB/config.php');
use PhpOffice\PhpSpreadsheet\IOFactory;

function formatDate($dateStr) {
    if (!$dateStr) return null;
    $dateStr = trim($dateStr);

    $dt = DateTime::createFromFormat('d/m/Y', $dateStr);
    if ($dt) return $dt->format('Y-m-d');

    $dt = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($dt) return $dt->format('Y-m-d');

    if (is_numeric($dateStr)) {
        $unix_date = ((int)$dateStr - 25569) * 86400; 
        return gmdate("Y-m-d", $unix_date);
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $files = $_FILES['file'];
    $userId = $_SESSION['user_id'] ?? 1;

    $_SESSION['uploaded_lists'] = [];

    for ($i = 0; $i < count($files['name']); $i++) {
        $filename   = basename($files['name'][$i]);
        $tmpName    = $files['tmp_name'][$i];
        $extension  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!is_uploaded_file($tmpName)) {
            $_SESSION['upload_errors'][] = "❌ Invalid upload for $filename";
            continue;
        }

        // Insert metadata into beneficiarylist
        $sqlList = "INSERT INTO beneficiarylist (fileName, date_submitted, status, user_id)
                    VALUES ('$filename', NOW(), 'pending', '$userId')";
        mysqli_query($conn, $sqlList);
        $listId = mysqli_insert_id($conn);

        $_SESSION['uploaded_lists'][] = $listId;

        // Insert record into processing_engine
        $sqlProc = "INSERT INTO processing_engine (list_id, processing_date, status)
                    VALUES ('$listId', NOW(), 'in_progress')";
        mysqli_query($conn, $sqlProc);
        $processingId = mysqli_insert_id($conn);

        // Function to insert a row
        function insertBeneficiary($conn, $listId, $processingId, $first_name, $last_name, $middle_name, $ext_name, $birth_date, $region, $province, $city, $barangay, $marital) {
            $sql = "INSERT INTO beneficiary 
                    (list_id, first_name, last_name, middle_name, ext_name, birth_date, region, province, city, barangay, marital_status) 
                    VALUES 
                    ('$listId', '$first_name', '$last_name', '$middle_name', '$ext_name', " . ($birth_date ? "'$birth_date'" : "NULL") . ", '$region', '$province', '$city', '$barangay', '$marital')";
            mysqli_query($conn, $sql);
        }

        // --- CSV parsing ---
        if ($extension === 'csv') {
            $handle = fopen($tmpName, "r");
            if ($handle !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    if ($row == 1) continue;

                    $first_name   = mysqli_real_escape_string($conn, $data[2] ?? '');
                    $last_name    = mysqli_real_escape_string($conn, $data[3] ?? '');
                    $middle_name  = mysqli_real_escape_string($conn, $data[4] ?? '');
                    $ext_name     = mysqli_real_escape_string($conn, $data[5] ?? '');
                    $birth_date   = mysqli_real_escape_string($conn, formatDate($data[6] ?? ''));
                    $region       = mysqli_real_escape_string($conn, $data[7] ?? '');
                    $province     = mysqli_real_escape_string($conn, $data[8] ?? '');
                    $city         = mysqli_real_escape_string($conn, $data[9] ?? '');
                    $barangay     = mysqli_real_escape_string($conn, $data[10] ?? '');
                    $marital      = mysqli_real_escape_string($conn, $data[11] ?? '');

                    insertBeneficiary($conn, $listId, $processingId, $first_name, $last_name, $middle_name, $ext_name, $birth_date, $region, $province, $city, $barangay, $marital);
                }
                fclose($handle);
            }
        } 
        // --- Excel parsing ---
        elseif (in_array($extension, ['xls', 'xlsx'])) {
            require '../../vendor/autoload.php';
            $spreadsheet = IOFactory::load($tmpName);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            foreach ($sheetData as $index => $row) {
                if ($index == 0) continue;

                $first_name   = mysqli_real_escape_string($conn, $row[2] ?? '');
                $last_name    = mysqli_real_escape_string($conn, $row[3] ?? '');
                $middle_name  = mysqli_real_escape_string($conn, $row[4] ?? '');
                $ext_name     = mysqli_real_escape_string($conn, $row[5] ?? '');
                $birth_date   = mysqli_real_escape_string($conn, formatDate($row[6] ?? ''));
                $region       = mysqli_real_escape_string($conn, $row[7] ?? '');
                $province     = mysqli_real_escape_string($conn, $row[8] ?? '');
                $city         = mysqli_real_escape_string($conn, $row[9] ?? '');
                $barangay     = mysqli_real_escape_string($conn, $row[10] ?? '');
                $marital      = mysqli_real_escape_string($conn, $row[11] ?? '');

                insertBeneficiary($conn, $listId, $processingId, $first_name, $last_name, $middle_name, $ext_name, $birth_date, $region, $province, $city, $barangay, $marital);
            }
        }

        // After processing, update status
        $updateProc = "UPDATE processing_engine SET status='completed' WHERE processing_id='$processingId'";
        mysqli_query($conn, $updateProc);
    }

    $_SESSION['success'] = "✅ File(s) uploaded, beneficiaries saved!";
    header("Location: ../view/admin/clean.php");
    exit;
} else {
    $_SESSION['error'] = "❌ No files uploaded.";
    header("Location: ../view/admin/clean.php");
    exit;
}
?>
