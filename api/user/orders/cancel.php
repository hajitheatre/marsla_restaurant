<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/config/db.php';

require_once __DIR__ . '/../../../core/includes/check_user_api_auth.php';

$user = $_SESSION['user'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

$orderId = (int)$data['id'];

try {
    $pdo = getDB();
    
    // Verify order exists, belongs to user, and is pending
    $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ?');
    $stmt->execute([$orderId, $user['id']]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if ($order['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Cannot cancel order that is not pending']);
        exit;
    }

    // Cancel order
    $updateStmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
    $updateStmt->execute([$orderId]);
    
    echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error', 'error' => $e->getMessage()]);
}
?>
