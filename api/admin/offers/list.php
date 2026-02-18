<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM special_offers ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $offers
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch offers',
        'error' => $e->getMessage()
    ]);
}
