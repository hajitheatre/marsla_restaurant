<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');

if ($name === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => ['name' => 'Category name is required']]);
    exit;
}

try {
    $pdo = getDB();
    // check uniqueness
    $chk = $pdo->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
    $chk->execute([$name]);
    if ($chk->fetch()) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => ['name' => 'Category name already exists']]);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
    $desc = $input['description'] ?? null;
    $stmt->execute([$name, $desc]);
    $id = (int)$pdo->lastInsertId();

    // Log activity
    require_once __DIR__ . '/../../../core/includes/logger.php';
    logActivity($_SESSION['user']['id'], "New Category($name) added", 'info', ['category_id' => $id]);

    echo json_encode(['success' => true, 'data' => ['id' => $id, 'name' => $name, 'description' => $desc]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create category', 'error' => $e->getMessage()]);
}

?>
