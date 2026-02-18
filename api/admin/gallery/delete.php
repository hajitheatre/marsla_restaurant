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
    
    // Get title and file path to delete file and log activity
    $stmt = $pdo->prepare("SELECT title, image_path FROM gallery WHERE id = ?");
    $stmt->execute([$data['id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item && !empty($item['image_path'])) {
        $fullPath = __DIR__ . '/../../../' . $item['image_path'];
        // Clean up: delete the physical media file (image/video) from the server
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$data['id']]);

    // Log activity
    $title = $item ? $item['title'] : "Unknown Item";
    logActivity($_SESSION['user']['id'], "Deleted gallery item: " . $title, 'warning', ['item_id' => $data['id']]);

    echo json_encode(['success' => true, 'message' => 'Gallery item deleted successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete gallery item', 'error' => $e->getMessage()]);
}
