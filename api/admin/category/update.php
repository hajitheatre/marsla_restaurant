<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$name = trim($input['name'] ?? '');

if ($id <= 0 || $name === '') {
    http_response_code(422);
    $errors = [];
    if ($id <= 0) $errors['id'] = 'Invalid category id';
    if ($name === '') $errors['name'] = 'Category name is required';
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $pdo = getDB();
    // check uniqueness excluding current id
    $chk = $pdo->prepare('SELECT id FROM categories WHERE name = ? AND id != ? LIMIT 1');
    $chk->execute([$name, $id]);
    if ($chk->fetch()) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => ['name' => 'Category name already exists']]);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
    $desc = $input['description'] ?? null;
    $stmt->execute([$name, $desc, $id]);

    // Log activity
    require_once __DIR__ . '/../../../core/includes/logger.php';
    logActivity($_SESSION['user']['id'], "Updated category: $name", 'info', ['category_id' => $id]);

    echo json_encode(['success' => true, 'data' => ['id' => $id, 'name' => $name, 'description' => $desc]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update category', 'error' => $e->getMessage()]);
}

?>
