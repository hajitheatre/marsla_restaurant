<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$category_id = (int)($input['category_id'] ?? 0);
$price = (float)($input['price'] ?? 0);
$description = $input['description'] ?? null;
$image = $input['image'] ?? null;

$errors = [];
if ($name === '') $errors['name'] = 'Item name is required';
if ($category_id <= 0) $errors['category_id'] = 'Valid category is required';
if ($price < 0) $errors['price'] = 'Price must be a positive number';
if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO food_items (category_id, name, description, price, image, is_available) VALUES (?, ?, ?, ?, ?, 1)');
    $stmt->execute([$category_id, $name, $description, $price, $image]);
    $id = (int)$pdo->lastInsertId();

    // Log activity
    require_once __DIR__ . '/../../../core/includes/logger.php';
    logActivity($_SESSION['user']['id'], "New food item($name) added", 'success', ['food_id' => $id, 'price' => $price]);

    echo json_encode(['success' => true, 'data' => ['id' => $id, 'name' => $name, 'category_id' => $category_id, 'price' => $price, 'image' => $image]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create food item', 'error' => $e->getMessage()]);
}

?>
