<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

if (empty($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error']);
    exit;
}

$allowed = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// limit 3MB
if ($file['size'] > 3 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large']);
    exit;
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$name = bin2hex(random_bytes(8)) . '.' . $ext;
$destDir = __DIR__ . '/../../../assets/images/';
if (!is_dir($destDir)) mkdir($destDir, 0755, true);
$dest = $destDir . $name;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'message' => 'Failed to move file']);
    exit;
}

echo json_encode(['success' => true, 'filename' => $name, 'path' => 'assets/images/' . $name]);

?>
