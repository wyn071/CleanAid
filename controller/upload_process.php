<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../dB/config.php');

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    die("❌ ERROR: User not logged in.");
}

$uploadDir = dirname(__DIR__) . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$allowedTypes = ['csv', 'xls', 'xlsx'];
$uploadedFiles = [];
$errors = [];

if (!isset($_FILES['file']) || empty($_FILES['file']['name'][0])) {
    $_SESSION['upload_errors'] = ['No files uploaded.'];
    $_SESSION['uploaded_files'] = [];
    die("❌ ERROR: No files uploaded.");
}

foreach ($_FILES['file']['name'] as $index => $originalName) {
    $tmpName = $_FILES['file']['tmp_name'][$index];
    $error = $_FILES['file']['error'][$index];

    if ($error !== UPLOAD_ERR_OK) {
        $errors[] = "$originalName failed to upload.";
        continue;
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        $errors[] = "$originalName is not an allowed file type.";
        continue;
    }

    $targetPath = $uploadDir . basename($originalName);

    // Prevent overwriting existing files
    $finalPath = $targetPath;
    $filenameOnly = pathinfo($originalName, PATHINFO_FILENAME);
    $counter = 1;
    while (file_exists($finalPath)) {
        $finalPath = $uploadDir . $filenameOnly . "($counter)." . $ext;
        $counter++;
    }

    $finalName = basename($finalPath);

    if (move_uploaded_file($tmpName, $finalPath)) {
        $uploadedFiles[] = $finalName;

        // ✅ Insert file record into DB
        $stmt = $conn->prepare("INSERT INTO beneficiarylist (fileName, date_submitted, status, user_id) VALUES (?, NOW(), 'uploaded', ?)");
        if ($stmt === false) {
            die("❌ Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("si", $finalName, $user_id);
        if (!$stmt->execute()) {
            die("❌ Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } else {
        $errors[] = "Failed to move $originalName.";
    }
}

// Store session feedback
$_SESSION['uploaded_files'] = $uploadedFiles;
$_SESSION['upload_errors'] = $errors;

// Redirect to clean.php
header("Location: ../view/admin/clean.php");
exit;
