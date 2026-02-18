<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$available = isset($input['available']) ? (int)$input['available'] : null;

if ($id <= 0 || $available === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('UPDATE food_items SET is_available = ? WHERE id = ?');
    $stmt->execute([$available ? 1 : 0, $id]);
    echo json_encode(['success' => true, 'id' => $id, 'available' => (bool)$available]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update availability', 'error' => $e->getMessage()]);
}

?>
