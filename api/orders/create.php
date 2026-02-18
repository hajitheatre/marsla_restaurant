<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/includes/check_user_api_auth.php';

require_once __DIR__ . '/../../core/config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!$data || !isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request: items required']);
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$items = $data['items'];
$phone = trim($data['phone'] ?? $_SESSION['user']['phone'] ?? '');
$address = trim($data['address'] ?? $_SESSION['user']['address'] ?? '');

try {
    $pdo = getDB();
    $pdo->beginTransaction();
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += ($item['price'] * $item['quantity']);
    }
    
    // Insert order
    $orderStmt = $pdo->prepare('
        INSERT INTO orders (user_id, total_amount, status, phone, created_at, updated_at)
        VALUES (?, ?, ?, ?, NOW(), NOW())
    ');
    $orderStmt->execute([$user_id, $total_amount, 'pending', $phone]);
    $order_id = $pdo->lastInsertId();
    
    // Insert order items
    $itemStmt = $pdo->prepare('
        INSERT INTO order_items (order_id, food_item_id, quantity, unit_price, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ');
    
    foreach ($items as $item) {
        $food_id = (int)$item['food_id'];
        $quantity = (int)$item['quantity'];
        $unit_price = (float)$item['price'];
        $subtotal = $unit_price * $quantity;
        
        // Verify food item exists
        $checkStmt = $pdo->prepare('SELECT id FROM food_items WHERE id = ?');
        $checkStmt->execute([$food_id]);
        if (!$checkStmt->fetch()) {
            throw new Exception("Invalid food item ID: $food_id");
        }
        
        $itemStmt->execute([$order_id, $food_id, $quantity, $unit_price, $subtotal]);
    }
    
    // Clear user's cart from database (if using database storage)
    $clearCartStmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
    $clearCartStmt->execute([$user_id]);
    

    
    $pdo->commit();
    
    // Log order activity
    require_once __DIR__ . '/../../core/includes/logger.php';
    logActivity($user_id, "Placed a new order #$order_id", 'success', ['order_id' => $order_id, 'amount' => $total_amount]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $order_id,
        'total_amount' => $total_amount
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order',
        'error' => $e->getMessage()
    ]);
}
?>
