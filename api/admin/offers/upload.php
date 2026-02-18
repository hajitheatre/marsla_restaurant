<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
    exit;
}

$file = $_FILES['image'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (in_array($fileExt, $allowed)) {
    if ($fileError === 0) {
        if ($fileSize < 5000000) { // 5MB limit
            $fileNewName = uniqid('', true) . "." . $fileExt;
            $uploadDir = __DIR__ . '/../../../assets/images/offers/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileDestination = $uploadDir . $fileNewName;
            
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'path' => 'assets/images/offers/' . $fileNewName
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Image too large']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error uploading image']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
}
