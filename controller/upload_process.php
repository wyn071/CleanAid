<?php
session_start();
include("./../dB/config.php");

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Unauthorized access.";
    $_SESSION['code'] = "error";
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExt !== 'csv') {
        $_SESSION['message'] = "Please upload a valid CSV file.";
        $_SESSION['code'] = "error";
        header("Location: ../view/upload.php");
        exit();
    }

    if (($handle = fopen($fileTmpPath, 'r')) !== false) {
        fgetcsv($handle); // skip header row

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($row) < 12) continue;

            list($beneficiary_id, $list_id, $first_name, $last_name, $middle_name, $ext_name,
                $birth_date, $region, $province, $city, $barangay, $marital_status) = array_map('trim', $row);

            // Insert list if not exists
            $check = $conn->prepare("SELECT 1 FROM beneficiarylist WHERE list_id = ?");
            $check->bind_param("s", $list_id);
            $check->execute();
            $res = $check->get_result();

            if ($res->num_rows === 0) {
                $status = 'pending';
                $stmt1 = $conn->prepare("INSERT INTO beneficiarylist (list_id, user_id, fileName, date_submitted, status)
                                         VALUES (?, ?, ?, NOW(), ?)");
                $stmt1->bind_param("siss", $list_id, $user_id, $fileName, $status);
                $stmt1->execute();
            }

            // Insert beneficiary (skip duplicates using INSERT IGNORE)
            $stmt2 = $conn->prepare("INSERT IGNORE INTO beneficiary (
                beneficiary_id, list_id, first_name, last_name, middle_name, ext_name,
                birth_date, region, province, city, barangay, marital_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param(
                "ssssssssssss",
                $beneficiary_id, $list_id, $first_name, $last_name, $middle_name, $ext_name,
                $birth_date, $region, $province, $city, $barangay, $marital_status
            );
            $stmt2->execute();
        }

        fclose($handle);
        $_SESSION['uploaded_filename'] = $fileName;
        $_SESSION['message'] = "File uploaded successfully!";
        $_SESSION['code'] = "success";
        header("Location: ../view/admin/clean.php");
        exit();
    }
}

$_SESSION['message'] = "Upload failed or invalid file.";
$_SESSION['code'] = "error";
header("Location: ../view/upload.php");
exit();
