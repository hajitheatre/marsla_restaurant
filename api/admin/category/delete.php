<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid category id']);
    exit;
}

try {
    $pdo = getDB();
    
    // Fetch name for logging
    $nameStmt = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
    $nameStmt->execute([$id]);
    $catName = $nameStmt->fetchColumn() ?: "Unknown";

    // Prevent deleting categories that have items
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM food_items WHERE category_id = ?');
    $stmt->execute([$id]);
    $count = (int)$stmt->fetchColumn();
    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Category has food items. Remove or reassign them first.']);
        exit;
    }

    $del = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $del->execute([$id]);

    // Log activity
    require_once __DIR__ . '/../../../core/includes/logger.php';
    logActivity($_SESSION['user']['id'], "Category($catName) Deleted", 'danger', ['category_id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Category deleted', 'id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete category', 'error' => $e->getMessage()]);
}

?>
