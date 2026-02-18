<?php
/**
 * Database Seed Script
 * Run this once to populate the database with sample food items and categories
 * 
 * Usage: Visit http://localhost/marsla_restaurant/database/seed.php in your browser
 * Or run: php -r "require 'seed.php';"
 */

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = getDB();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Clear existing data (optional - comment out if you want to keep existing data)
    // $pdo->exec('TRUNCATE TABLE order_items');
    // $pdo->exec('TRUNCATE TABLE orders');
    // $pdo->exec('TRUNCATE TABLE cart_items');
    // $pdo->exec('TRUNCATE TABLE food_items');
    // $pdo->exec('TRUNCATE TABLE categories');
    
    // Insert Categories
    $categories = [
        ['name' => 'Main Dishes', 'description' => 'Hearty and delicious main courses'],
        ['name' => 'Grilled', 'description' => 'Fresh from the grill'],
        ['name' => 'Seafood', 'description' => 'Fresh catches from the ocean'],
        ['name' => 'Rice & Biryani', 'description' => 'Traditional rice dishes'],
        ['name' => 'Beverages', 'description' => 'Refreshing drinks'],
    ];
    
    $categoryStmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
    foreach ($categories as $cat) {
        $categoryStmt->execute([$cat['name'], $cat['description']]);
    }
    
    // Get category IDs
    $stmt = $pdo->prepare('SELECT id FROM categories ORDER BY name ASC');
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Insert Food Items
    $foodItems = [
        // Rice & Biryani (index 3)
        ['category_id' => $cats[3] ?? 4, 'name' => 'Biryani Rice', 'description' => 'Nice spiced Biryani meal with chicken.', 'price' => 10000, 'image' => 'biryani.jpg', 'is_available' => 1],
        ['category_id' => $cats[3] ?? 4, 'name' => 'Biryani Kuku', 'description' => 'Aromatic spiced tender chicken drumsticks with Biryani rice', 'price' => 10000, 'image' => 'biryanikuku.jpg', 'is_available' => 1],
        
        // Seafood (index 2)
        ['category_id' => $cats[2] ?? 3, 'name' => 'Noodles & Shrimps', 'description' => 'Delicious noodles with a Tanzanian flavour', 'price' => 20000, 'image' => 'noodles.jpg', 'is_available' => 1],
        ['category_id' => $cats[2] ?? 3, 'name' => 'Fish & Fries', 'description' => 'Delicious Fish and fries with a unique Tanzanian flavour', 'price' => 12000, 'image' => 'fish.jpg', 'is_available' => 1],
        
        // Grilled (index 1)
        ['category_id' => $cats[1] ?? 2, 'name' => 'Kuku Choma', 'description' => 'Creamy curry with tender chicken pieces and naan bread', 'price' => 4000, 'image' => 'chicken.jpg', 'is_available' => 1],
        ['category_id' => $cats[1] ?? 2, 'name' => 'Mishakaki', 'description' => 'Fragrant spiced beef chunks', 'price' => 1000, 'image' => 'mishakaki.jpg', 'is_available' => 1],
        ['category_id' => $cats[1] ?? 2, 'name' => 'Nyama Choma', 'description' => 'Delicious grilled with beef.', 'price' => 10000, 'image' => 'nyama-choma.jpg', 'is_available' => 1],
        
        // Main Dishes (index 0)
        ['category_id' => $cats[0] ?? 1, 'name' => 'Chapati Rosti', 'description' => 'Delicious made Chapati with freshly roasted meat.', 'price' => 8000, 'image' => 'chapati-rosti.jpg', 'is_available' => 1],
        ['category_id' => $cats[0] ?? 1, 'name' => 'Chips Kuku', 'description' => 'Delicious fries with fried chicken.', 'price' => 15000, 'image' => 'chipsirosti.jpg', 'is_available' => 1],
        ['category_id' => $cats[0] ?? 1, 'name' => 'Chipsi Rosti', 'description' => 'Delicious fries with roasted chicken.', 'price' => 10000, 'image' => 'Chipsi kukurosti.jpg', 'is_available' => 1],
        
        // Beverages (index 4)
        ['category_id' => $cats[4] ?? 5, 'name' => 'Fresh Juice Combo', 'description' => 'Selection of fresh tropical fruit juices', 'price' => 3000, 'image' => 'food-6.jpg', 'is_available' => 1],
        ['category_id' => $cats[4] ?? 5, 'name' => 'Fresh Juice Mixer', 'description' => 'Selection of fresh tropical fruit juices', 'price' => 2000, 'image' => 'juice.jpg', 'is_available' => 1],
    ];
    
    $foodStmt = $pdo->prepare('INSERT INTO food_items (category_id, name, description, price, image, is_available) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($foodItems as $item) {
        $foodStmt->execute([
            $item['category_id'],
            $item['name'],
            $item['description'],
            $item['price'],
            $item['image'],
            $item['is_available']
        ]);
    }

    // Create an admin user if not exists
    $adminEmail = 'admin@marsla.local';
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $check->execute([$adminEmail]);
    if (!$check->fetch()) {
        $adminPwd = 'admin123';
        $hash = password_hash($adminPwd, PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)');
        $ins->execute(['Admin', 'User', $adminEmail, $hash, 'Admin']);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database seeded successfully!',
        'categories_inserted' => count($categories),
        'food_items_inserted' => count($foodItems)
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Seeding failed',
        'message' => $e->getMessage()
    ]);
}
?>
