<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';
require_once __DIR__ . '/../../../core/config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order ID or status']);
    exit;
}

$orderId = (int)$data['id'];
$status = $data['status'];
$validStatuses = ['pending', 'approved', 'cancelled', 'completed'];

if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$status, $orderId]);
    
    if ($stmt->rowCount() > 0) {
        // Log activity
        require_once __DIR__ . '/../../../core/includes/logger.php';
        $adminId = $_SESSION['user']['id'] ?? null;
        logActivity($adminId, "Order #$orderId changed status to '$status'", 'info', ['order_id' => $orderId, 'status' => $status]);
        
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order not found or no change']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
}
?>
