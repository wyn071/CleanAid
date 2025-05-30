<?php
session_start();
include("../../dB/config.php");

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

    if ($fileExt === 'csv') {
        if (($handle = fopen($fileTmpPath, 'r')) !== false) {
            fgetcsv($handle); // skip header

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $beneficiary_id = $row[0];
                $list_id = $row[1];
                $first_name = $row[2];
                $last_name = $row[3];
                $middle_name = $row[4];
                $ext_name = $row[5];
                $birth_date = $row[6];
                $region = $row[7];
                $province = $row[8];
                $city = $row[9];
                $barangay = $row[10];
                $marital_status = $row[11];

                // Insert list_id into beneficiarylist if not already there
                $check = $conn->prepare("SELECT 1 FROM beneficiarylist WHERE list_id = ?");
                $check->bind_param("s", $list_id);
                $check->execute();
                $res = $check->get_result();

                if ($res->num_rows === 0) {
                    $status = 'pending';
                    $stmt1 = $conn->prepare("INSERT INTO beneficiarylist (list_id, user_id, fileName, date_submitted, status) VALUES (?, ?, ?, NOW(), ?)");
                    $stmt1->bind_param("siss", $list_id, $user_id, $fileName, $status);
                    $stmt1->execute();
                }

                // Insert into beneficiary
                $stmt2 = $conn->prepare("INSERT INTO beneficiary (
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
            $_SESSION['message'] = "File uploaded successfully!";
            $_SESSION['code'] = "success";
            header("Location: clean.php");
            exit();
        }
    }

    $_SESSION['message'] = "Invalid file or upload failed.";
    $_SESSION['code'] = "error";
    header("Location: upload.php");
    exit();
}
