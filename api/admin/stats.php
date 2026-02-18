<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/includes/check_admin_api_auth.php';
require_once __DIR__ . '/../../core/config/db.php';

try {
    $pdo = getDB();

    $totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $totalItems = (int) $pdo->query('SELECT COUNT(*) FROM food_items')->fetchColumn();
    // Count all orders that are NOT soft-deleted by admin
    $totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE deleted_by_admin = 0')->fetchColumn();
    $totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status = 'completed' AND deleted_by_admin = 0")->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => [
            'totalUsers' => $totalUsers,
            'totalItems' => $totalItems,
            'totalOrders' => $totalOrders,
            'totalRevenue' => (float)$totalRevenue
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
