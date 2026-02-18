<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';
require_once __DIR__ . '/../../../core/config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

$orderId = (int)$data['id'];

try {
    $pdo = getDB();
    // Assuming ON DELETE CASCADE is set for order_items in schema.sql
    // If not, we'd need to delete items first. Step 20 confirmed schema has ON DELETE CASCADE.
    
    // Soft delete for admin: update deleted_by_admin flag
    $stmt = $pdo->prepare('UPDATE orders SET deleted_by_admin = 1 WHERE id = ?');
    $stmt->execute([$orderId]);
    
    if ($stmt->rowCount() > 0) {
        // Log activity
        require_once __DIR__ . '/../../../core/includes/logger.php';
        $adminId = $_SESSION['user']['id'] ?? null;
        logActivity($adminId, "Order #$orderId Deleted", 'danger', ['order_id' => $orderId]);
        
        echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
}
?>
