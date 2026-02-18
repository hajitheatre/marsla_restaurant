<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';
require_once __DIR__ . '/../../../core/config/db.php';

try {
    $pdo = getDB();
    
    // Fetch orders with user details
    $stmt = $pdo->query('
        SELECT 
            o.id, 
            o.total_amount, 
            o.status, 
            o.phone as order_phone, -- Use order phone if available
            o.created_at,
            u.first_name, 
            u.last_name, 
            u.email, 
            u.phone as user_phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE (o.deleted_by_admin IS NULL OR o.deleted_by_admin = 0)
        ORDER BY o.created_at DESC
    ');
    
    $orders = $stmt->fetchAll();
    
    // Fetch items for each order
    foreach ($orders as &$order) {
        $itemStmt = $pdo->prepare('
            SELECT fi.name, oi.quantity
            FROM order_items oi
            JOIN food_items fi ON oi.food_item_id = fi.id
            WHERE oi.order_id = ?
        ');
        $itemStmt->execute([$order['id']]);
        $items = $itemStmt->fetchAll();
        
        $itemNames = array_map(function($item) {
            return $item['name'];
        }, $items);
        
        $order['items'] = $itemNames; // Array of item names
        
        // Format customer name
        $order['customer'] = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
        if (empty($order['customer'])) {
            $order['customer'] = $order['email'] ?? 'Unknown';
        }
        
        // Prefer order-specific phone, fallback to user profile phone
        $order['phone'] = $order['order_phone'] ?: ($order['user_phone'] ?: 'N/A');
        
        // Format date
        $order['date'] = date('Y-m-d', strtotime($order['created_at']));
    }
    
    echo json_encode(['success' => true, 'data' => $orders]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch orders', 'error' => $e->getMessage()]);
}
?>
