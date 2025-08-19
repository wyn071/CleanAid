<?php
session_start();
include('../dB/config.php');
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = "../../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $files = $_FILES['file'];
    $userId = $_SESSION['user_id'] ?? 1; // fallback if no session user

    $_SESSION['uploaded_files'] = []; // reset previous uploads

    for ($i = 0; $i < count($files['name']); $i++) {
        $filename   = basename($files['name'][$i]);
        $tmpName    = $files['tmp_name'][$i];
        $extension  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $targetFile = $uploadDir . time() . "_" . $filename;

        if (!move_uploaded_file($tmpName, $targetFile)) {
            $_SESSION['upload_errors'][] = "❌ Failed to move file $filename";
            continue;
        }

        // Insert record into beneficiarylist first
        $sqlList = "INSERT INTO beneficiarylist (fileName, date_submitted, status, user_id)
                    VALUES ('$filename', NOW(), 'pending', '$userId')";
        mysqli_query($conn, $sqlList);
        $listId = mysqli_insert_id($conn);

        // Parse and insert rows
        if ($extension === 'csv') {
            $handle = fopen($targetFile, "r");
            if ($handle !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    if ($row == 1) continue; // skip header

                    // map CSV columns to DB
                    $first_name   = mysqli_real_escape_string($conn, $data[0] ?? '');
                    $last_name    = mysqli_real_escape_string($conn, $data[1] ?? '');
                    $middle_name  = mysqli_real_escape_string($conn, $data[2] ?? '');
                    $ext_name     = mysqli_real_escape_string($conn, $data[3] ?? '');
                    $birth_date   = mysqli_real_escape_string($conn, $data[4] ?? '');
                    $region       = mysqli_real_escape_string($conn, $data[5] ?? '');
                    $province     = mysqli_real_escape_string($conn, $data[6] ?? '');
                    $city         = mysqli_real_escape_string($conn, $data[7] ?? '');
                    $barangay     = mysqli_real_escape_string($conn, $data[8] ?? '');
                    $marital      = mysqli_real_escape_string($conn, $data[9] ?? '');

                    $sql = "INSERT INTO beneficiary 
                            (list_id, first_name, last_name, middle_name, ext_name, birth_date, region, province, city, barangay, marital_status) 
                            VALUES 
                            ('$listId', '$first_name', '$last_name', '$middle_name', '$ext_name', '$birth_date', '$region', '$province', '$city', '$barangay', '$marital')";
                    mysqli_query($conn, $sql);
                }
                fclose($handle);
            }
        } elseif (in_array($extension, ['xls', 'xlsx'])) {
            require '../../vendor/autoload.php';

            $spreadsheet = IOFactory::load($targetFile);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            foreach ($sheetData as $index => $row) {
                if ($index == 0) continue; // skip header

                $first_name   = mysqli_real_escape_string($conn, $row[0] ?? '');
                $last_name    = mysqli_real_escape_string($conn, $row[1] ?? '');
                $middle_name  = mysqli_real_escape_string($conn, $row[2] ?? '');
                $ext_name     = mysqli_real_escape_string($conn, $row[3] ?? '');
                $birth_date   = mysqli_real_escape_string($conn, $row[4] ?? '');
                $region       = mysqli_real_escape_string($conn, $row[5] ?? '');
                $province     = mysqli_real_escape_string($conn, $row[6] ?? '');
                $city         = mysqli_real_escape_string($conn, $row[7] ?? '');
                $barangay     = mysqli_real_escape_string($conn, $row[8] ?? '');
                $marital      = mysqli_real_escape_string($conn, $row[9] ?? '');

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
