<?php
header('Content-Type: application/json');

// Allow requests from your React development server (adjust if needed)
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Respond to OPTIONS requests (preflight checks for CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include your database configuration if needed for storing upload info
// require_once 'config.php';

$response = ['success' => false, 'message' => 'Upload failed.'];

// Check if file was uploaded without errors
if (isset($_FILES['data_file']) && $_FILES['data_file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['data_file']['tmp_name'];
    $fileName = $_FILES['data_file']['name'];
    $fileSize = $_FILES['data_file']['size'];
    $fileType = $_FILES['data_file']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Sanitize file name
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    // Directory where you want to save the uploaded files
    $uploadFileDir = './uploads/'; // Create this directory and ensure it's writable
    $dest_path = $uploadFileDir . $newFileName;

    // Validate file extension (add/remove extensions as needed)
    $allowedfileExtensions = ['xlsx', 'xls', 'csv'];
    if (in_array($fileExtension, $allowedfileExtensions)) {

        // Move the file to the upload directory
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // File is successfully moved

            // TODO: Implement your data processing and cleansing logic here
            // - Read the file ($dest_path)
            // - Perform validation and cleansing
            // - Store upload details and results in your database
            // - Associate the upload with the logged-in user (get user ID from session/token)

            // Example placeholder response:
            // In a real application, you'd get the upload ID from your database after insertion
            $simulatedUploadId = rand(100, 999); // Replace with actual ID from DB

            $response = [
                'success' => true,
                'message' => 'File uploaded and processing initiated.',
                'upload_id' => $simulatedUploadId // Return the upload ID
            ];

        } else {
            $response['message'] = 'There was an error moving the uploaded file.';
        }
    } else {
        $response['message'] = 'Invalid file type. Allowed types: ' . implode(',', $allowedfileExtensions) . '.';
    }
} else {
    $response['message'] = $_FILES['data_file']['error'] > 0 ? 'File upload error code: ' . $_FILES['data_file']['error'] : 'No file uploaded or an unknown error occurred.';
}

echo json_encode($response);
?> 