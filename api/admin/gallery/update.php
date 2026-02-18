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

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing item ID']);
    exit;
}

try {
    $pdo = getDB();
    
    // Dynamically update the gallery record. If a new file was uploaded, update image_path and media_type.
    $params = [$data['title'], $data['caption'], $data['tags']];
    $sql = "UPDATE gallery SET title = ?, caption = ?, tags = ?";
    
    if (isset($data['image_path'])) {
        $sql .= ", image_path = ?, media_type = ?";
        $params[] = $data['image_path'];
        $params[] = $data['media_type'] ?? 'image';
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $data['id'];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Log activity
    logActivity($_SESSION['user']['id'], "Updated gallery item: " . $data['title'], 'info', ['item_id' => $data['id']]);

    echo json_encode(['success' => true, 'message' => 'Gallery item updated successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update gallery item', 'error' => $e->getMessage()]);
}
