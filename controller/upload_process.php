<?php
session_start();
include('../dB/config.php');
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Format dates to MySQL YYYY-MM-DD
 * Handles:
 *  - DD/MM/YYYY (from CSV/Excel exports)
 *  - YYYY-MM-DD (already formatted)
 *  - Excel numeric dates
 */
function formatDate($dateStr) {
    if (!$dateStr) return null;

    $dateStr = trim($dateStr);

    // Try DD/MM/YYYY
    $dt = DateTime::createFromFormat('d/m/Y', $dateStr);
    if ($dt) return $dt->format('Y-m-d');

    // Try YYYY-MM-DD
    $dt = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($dt) return $dt->format('Y-m-d');

    // Try Excel numeric date
    if (is_numeric($dateStr)) {
        $unix_date = ((int)$dateStr - 25569) * 86400; 
        return gmdate("Y-m-d", $unix_date);
    }

    return null; // fallback
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $files = $_FILES['file'];
    $userId = $_SESSION['user_id'] ?? 1; // fallback if no session user

    $_SESSION['uploaded_files'] = []; // reset previous uploads

    for ($i = 0; $i < count($files['name']); $i++) {
        $filename   = basename($files['name'][$i]);
        $tmpName    = $files['tmp_name'][$i];
        $extension  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!is_uploaded_file($tmpName)) {
            $_SESSION['upload_errors'][] = "❌ Invalid upload for $filename";
            continue;
        }

        // Insert record into beneficiarylist first
        $sqlList = "INSERT INTO beneficiarylist (fileName, date_submitted, status, user_id)
                    VALUES ('$filename', NOW(), 'pending', '$userId')";
        mysqli_query($conn, $sqlList);
        $listId = mysqli_insert_id($conn);

        // --- CSV parsing ---
        if ($extension === 'csv') {
            $handle = fopen($tmpName, "r");
            if ($handle !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    if ($row == 1) continue; // skip header row

                    // Skip beneficiary_id (0) and list_id (1)
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

                    $sql = "INSERT INTO beneficiary 
                            (list_id, first_name, last_name, middle_name, ext_name, birth_date, region, province, city, barangay, marital_status) 
                            VALUES 
                            ('$listId', '$first_name', '$last_name', '$middle_name', '$ext_name', '$birth_date', '$region', '$province', '$city', '$barangay', '$marital')";
                    mysqli_query($conn, $sql);
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
                if ($index == 0) continue; // skip header row

                // Skip beneficiary_id (0) and list_id (1)
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

                $sql = "INSERT INTO beneficiary 
                        (list_id, first_name, last_name, middle_name, ext_name, birth_date, region, province, city, barangay, marital_status) 
                        VALUES 
                        ('$listId', '$first_name', '$last_name', '$middle_name', '$ext_name', '$birth_date', '$region', '$province', '$city', '$barangay', '$marital')";
                mysqli_query($conn, $sql);
            }
        }

        $_SESSION['uploaded_files'][] = $filename; // keep track of uploaded files
    }

    $_SESSION['success'] = "✅ File(s) uploaded and beneficiaries saved!";
    header("Location: ../view/admin/clean.php");
    exit;
} else {
    $_SESSION['error'] = "❌ No files uploaded.";
    header("Location: ../view/admin/clean.php");
    exit;
}
?>
