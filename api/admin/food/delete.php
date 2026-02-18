<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid id']);
    exit;
}

try {
    $pdo = getDB();
    // Optionally remove image file
    $stmt = $pdo->prepare('SELECT image FROM food_items WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && !empty($row['image'])) {
        $path = __DIR__ . '/../../../assets/images/' . $row['image'];
        if (file_exists($path)) @unlink($path);
    }

    // Fetch name for logging
    $nameStmt = $pdo->prepare('SELECT name FROM food_items WHERE id = ?');
    $nameStmt->execute([$id]);
    $foodName = $nameStmt->fetchColumn() ?: "Unknown";

    $del = $pdo->prepare('DELETE FROM food_items WHERE id = ?');
    $del->execute([$id]);

    // Log activity
    require_once __DIR__ . '/../../../core/includes/logger.php';
    logActivity($_SESSION['user']['id'], "Food item($foodName) Deleted", 'danger', ['food_id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Food item deleted', 'id' => $id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete', 'error' => $e->getMessage()]);
}

?>
