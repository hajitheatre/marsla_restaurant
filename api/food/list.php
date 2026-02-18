<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/config/db.php';

try {
    $pdo = getDB();
    
    // Get category filter if provided
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Base query
    $query = 'SELECT 
                f.id, 
                f.name, 
                f.description, 
                f.price, 
                f.image, 
                f.is_available,
                f.category_id,
                c.name as category_name,
                c.id as category_id
              FROM food_items f
              LEFT JOIN categories c ON f.category_id = c.id
              WHERE 1=1';
    
    $params = [];
    
    // Add category filter
    if ($categoryId !== null && $categoryId !== 0) {
        $query .= ' AND f.category_id = ?';
        $params[] = $categoryId;
    }
    
    // Add search filter
    if (!empty($search)) {
        $query .= ' AND (f.name LIKE ? OR f.description LIKE ? OR CAST(f.price AS CHAR) LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    
    $query .= ' ORDER BY c.name ASC, f.name ASC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $foodItems = $stmt->fetchAll();
    
    // Transform to match frontend naming convention
    $transformedItems = array_map(function($item) {
        // If image is empty or null, use a placeholder; otherwise use the image from assets/images
        if (empty($item['image'])) {
            $imagePath = 'assets/images/food-6.jpg'; // fallback placeholder
        } else {
            // If image doesn't start with 'assets/', prepend the path
            if (strpos($item['image'], 'assets/') === 0) {
                $imagePath = $item['image'];
            } else {
                $imagePath = 'assets/images/' . $item['image'];
            }
        }
        
        return [
            'food_id' => (int)$item['id'],
            'category_id' => (int)$item['category_id'],
            'food_name' => $item['name'],
            'description' => $item['description'],
            'price' => (int)$item['price'],
            'image_path' => $imagePath,
            'availability_status' => $item['is_available'] ? 'Available' : 'Unavailable',
            'category_name' => $item['category_name'] ?? 'Uncategorized'
        ];
    }, $foodItems);
    
    echo json_encode([
        'success' => true,
        'data' => $transformedItems,
        'count' => count($transformedItems)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch food items',
        'message' => $e->getMessage()
    ]);
}
?>
