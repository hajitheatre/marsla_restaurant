<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/config/db.php';

try {
    $pdo = getDB();
    // Public endpoint: Fetch all gallery items, newest first
    $sql = "SELECT * FROM gallery ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response for the frontend (main.js)
    echo json_encode([
        'success' => true,
        'data' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch gallery items',
        'error' => $e->getMessage()
    ]);
}
