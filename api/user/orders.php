<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/includes/check_user_api_auth.php';

require_once __DIR__ . '/../../core/config/db.php';

try {
    $pdo = getDB();
    $userId = $_SESSION['user']['id'];
    
    $stmt = $pdo->prepare('
        SELECT 
            o.id, 
            o.created_at as date, 
            o.total_amount, 
            o.status
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ');
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
    
    foreach ($orders as &$order) {
        $stmt = $pdo->prepare('
            SELECT fi.name, oi.quantity
            FROM order_items oi
            JOIN food_items fi ON oi.food_item_id = fi.id
            WHERE oi.order_id = ?
        ');
        $stmt->execute([$order['id']]);
        $items = $stmt->fetchAll();
        
        $itemNames = array_map(function($item) {
            return $item['name'] . ' x' . $item['quantity'];
        }, $items);
        $order['items'] = implode(', ', $itemNames) ?: 'No items';
    }
    
    echo json_encode(['success' => true, 'data' => $orders]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
