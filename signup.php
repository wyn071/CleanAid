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

require_once 'config.php'; // Include your database configuration file

// Get the raw POST data
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($data && isset($data['fullname']) && isset($data['username']) && isset($data['password'])) {
    $fullname = trim($data['fullname']);
    $username = trim($data['username']);
    $password = $data['password'];

    if (empty($fullname) || empty($username) || empty($password)) {
        $response['message'] = 'All fields are required.';
    } else {
        try {
            $conn = getDBConnection(); // Get database connection from config.php

            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $response['message'] = 'Username already exists.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user into database
                // Assuming a 'users' table with columns: id, full_name, username, password, created_at
                $stmt = $conn->prepare("INSERT INTO users (full_name, username, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $fullname, $username, $hashed_password);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Registration successful! You can now log in.'];
                } else {
                    $response['message'] = 'Error registering user: ' . $conn->error;
                }
            }

            $stmt->close();
            $conn->close();

        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Full name, username, and password are required.';
}

echo json_encode($response);
?> 