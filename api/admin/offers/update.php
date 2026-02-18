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
    echo json_encode(['success' => false, 'message' => 'Missing offer ID']);
    exit;
}

try {
    $pdo = getDB();
    
    // Dynamically build update SQL based on whether a new image was uploaded
    $params = [$data['title'], $data['caption'], $data['display_order']];
    $sql = "UPDATE special_offers SET title = ?, caption = ?, display_order = ?";
    
    if (isset($data['image_path'])) {
        $sql .= ", image_path = ?";
        $params[] = $data['image_path'];
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $data['id'];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Log activity
    logActivity($_SESSION['user']['id'], "Updated special offer: " . $data['title'], 'info', ['offer_id' => $data['id']]);

    echo json_encode(['success' => true, 'message' => 'Special offer updated successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update special offer', 'error' => $e->getMessage()]);
}
