<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';
require_once __DIR__ . '/../../../core/includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['title']) || !isset($data['image_path'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo = getDB();
    // Create a new gallery record. Supports both 'image' and 'video' media types.
    $stmt = $pdo->prepare("INSERT INTO gallery (title, caption, image_path, media_type, tags) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['title'],
        $data['caption'] ?? '',
        $data['image_path'],
        $data['media_type'] ?? 'image',
        $data['tags'] ?? ''
    ]);

    $itemId = $pdo->lastInsertId();
    
    // Log the addition of new media for auditing
    logActivity($_SESSION['user']['id'], "Added new gallery item: " . $data['title'], 'success', ['item_id' => $itemId]);

    echo json_encode([
        'success' => true,
        'message' => 'Gallery item created successfully',
        'id' => $itemId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create gallery item',
        'error' => $e->getMessage()
    ]);
}
