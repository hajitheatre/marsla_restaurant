<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/config/db.php';

try {
    $pdo = getDB();
    
    // Get all categories with item count
    $query = 'SELECT 
                c.id, 
                c.name,
                COUNT(f.id) as itemCount
              FROM categories c
              LEFT JOIN food_items f ON c.id = f.category_id
              GROUP BY c.id, c.name
              ORDER BY c.name ASC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    // Transform to match frontend naming convention
    $transformedCategories = array_map(function($cat) {
        return [
            'category_id' => (int)$cat['id'],
            'category_name' => $cat['name'],
            'description' => '',
            'itemCount' => (int)$cat['itemCount']
        ];
    }, $categories);
    
    echo json_encode([
        'success' => true,
        'data' => $transformedCategories,
        'count' => count($transformedCategories)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch categories',
        'message' => $e->getMessage()
    ]);
}
?>
