<?php
session_start();
include('../dB/config.php');

// Get processing_id from query string
$processingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($processingId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid processing ID"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT status, progress_percent
    FROM processing_engine
    WHERE processing_id = ?
");
$stmt->bind_param("i", $processingId);
$stmt->execute();
$stmt->bind_result($status, $progress);
$stmt->fetch();
$stmt->close();

if ($status === null) {
    http_response_code(404);
    echo json_encode(["error" => "Processing ID not found"]);
    exit;
}

header('Content-Type: application/json');
echo json_encode([
    "status"   => $status,
    "progress" => (int)$progress
]);
