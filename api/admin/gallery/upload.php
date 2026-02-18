<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

// Validate file extensions and determine media type
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedImages = ['jpg', 'jpeg', 'png', 'webp'];
$allowedVideos = ['mp4', 'webm', 'ogg'];

$mediaType = 'image';
if (in_array($fileExt, $allowedVideos)) {
    $mediaType = 'video';
} elseif (!in_array($fileExt, $allowedImages)) {
    // Return error if extension not in any allowed list
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Supported: JPG, PNG, WEBP, MP4, WEBM, OGG']);
    exit;
}

if ($fileError === 0) {
    // 50MB limit for videos, 5MB for images
    $limit = ($mediaType === 'video') ? 50000000 : 5000000;
    
    if ($fileSize < $limit) {
        $fileNewName = uniqid('', true) . "." . $fileExt;
        $uploadDir = __DIR__ . '/../../../assets/gallery/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileDestination = $uploadDir . $fileNewName;
        
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            // Return public path and media type for database storage
            echo json_encode([
                'success' => true,
                'message' => 'File uploaded successfully',
                'path' => 'assets/gallery/' . $fileNewName,
                'media_type' => $mediaType
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File too large. Limit: ' . ($limit / 1000000) . 'MB']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error uploading file']);
}
