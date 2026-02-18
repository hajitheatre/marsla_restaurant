<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/includes/check_user_api_auth.php';

require_once __DIR__ . '/../../core/config/db.php';

try {
    $pdo = getDB();
    $userId = $_SESSION['user']['id'];
    
    // Get total orders
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM orders WHERE user_id = ?');
    $stmt->execute([$userId]);
    $totalOrders = (int)$stmt->fetch()['total'] ?? 0;
    
    // Get total spent (completed orders only)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE user_id = ? AND status = 'Completed'");
    $stmt->execute([$userId]);
    $totalSpent = (float)$stmt->fetch()['total'] ?? 0;
    
    // Get last order date
    $stmt = $pdo->prepare('SELECT created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    $lastOrder = $stmt->fetch();
    $lastLogin = $lastOrder ? $lastOrder['created_at'] : null;
    
    echo json_encode([
        'success' => true,
        'totalOrders' => $totalOrders,
        'totalSpent' => $totalSpent,
        'lastOrderDate' => $lastLogin
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
