<?php
$uploadDir = '../uploads/';
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['name'] as $key => $name) {
            $tmpName = $_FILES['files']['tmp_name'][$key];
            $error = $_FILES['files']['error'][$key];
            $size = $_FILES['files']['size'][$key];

            if ($error === UPLOAD_ERR_OK) {
                $targetPath = $uploadDir . basename($name);
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $response[] = [
                        'name' => $name,
                        'status' => 'success',
                        'path' => $targetPath
                    ];
                } else {
                    $response[] = [
                        'name' => $name,
                        'status' => 'error',
                        'message' => 'Failed to move file.'
                    ];
                }
            } else {
                $response[] = [
                    'name' => $name,
                    'status' => 'error',
                    'message' => 'Upload error code: ' . $error
                ];
            }
        }
    } else {
        $response[] = [
            'status' => 'error',
            'message' => 'No files uploaded.'
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>