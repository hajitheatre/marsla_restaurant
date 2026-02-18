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
    
    // Get title and image path to delete file and log activity
    $stmt = $pdo->prepare("SELECT title, image_path FROM special_offers WHERE id = ?");
    $stmt->execute([$data['id']]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($offer && !empty($offer['image_path'])) {
        $fullPath = __DIR__ . '/../../../' . $offer['image_path'];
        // Clean up: delete the physical image file from the server
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM special_offers WHERE id = ?");
    $stmt->execute([$data['id']]);

    // Log activity
    $title = $offer ? $offer['title'] : "Unknown Offer";
    logActivity($_SESSION['user']['id'], "Deleted special offer: " . $title, 'warning', ['offer_id' => $data['id']]);

    echo json_encode(['success' => true, 'message' => 'Special offer deleted successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete special offer', 'error' => $e->getMessage()]);
}
