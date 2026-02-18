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
    // Insert new offer. Display order defaults to 0 if not provided.
    $stmt = $pdo->prepare("INSERT INTO special_offers (title, caption, image_path, display_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['title'],
        $data['caption'] ?? '',
        $data['image_path'],
        $data['display_order'] ?? 0
    ]);

    $offerId = $pdo->lastInsertId();
    
    // Log administrative activity for auditing
    logActivity($_SESSION['user']['id'], "Created special offer: " . $data['title'], 'success', ['offer_id' => $offerId]);

    echo json_encode([
        'success' => true,
        'message' => 'Special offer created successfully',
        'id' => $offerId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create special offer',
        'error' => $e->getMessage()
    ]);
}
