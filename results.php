<?php
header('Content-Type: application/json');

// Allow requests from your React development server (adjust if needed)
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Respond to OPTIONS requests (preflight checks for CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => 'Invalid request.'];

if (isset($_GET['upload_id'])) {
    $upload_id = $_GET['upload_id'];

    // TODO: Implement actual database logic to fetch upload details and results
    // For now, returning placeholder data

    $uploadDetails = null;
    $results = [];

    if ($upload_id === '123') { // Example: data for upload ID 123
        $uploadDetails = [
            'filename' => 'sample_data.xlsx',
            'upload_date' => '2023-10-27 10:00:00'
        ];
        $results = [
            ['id' => 1, 'issue_type' => 'Duplicate', 'description' => 'Duplicate record found', 'row_number' => 5, 'value' => 'John Doe'],
            ['id' => 2, 'issue_type' => 'Missing', 'description' => 'Required field empty: Address', 'row_number' => 12, 'value' => ''],
            ['id' => 3, 'issue_type' => 'Inconsistent', 'description' => 'Age is inconsistent', 'row_number' => 8, 'value' => 'abc']
        ];
        $response = ['success' => true, 'uploadDetails' => $uploadDetails, 'results' => $results];

    } else if ($upload_id === '456') { // Example: data for upload ID 456 (no issues)
         $uploadDetails = [
            'filename' => 'clean_data.csv',
            'upload_date' => '2023-10-27 11:30:00'
        ];
         $results = []; // No issues found
         $response = ['success' => true, 'uploadDetails' => $uploadDetails, 'results' => $results, 'message' => 'No issues found.'];

    } else {
        $response = ['success' => false, 'message' => 'Upload ID not found.'];
    }

} else {
    $response = ['success' => false, 'message' => 'Upload ID is required.'];
}

echo json_encode($response);
?> 