<?php
session_start();
require_once __DIR__ . '/../../core/config/db.php';
require_once __DIR__ . '/../../core/includes/logger.php';

header('Content-Type: application/json');

require_once __DIR__ . '/../../core/includes/check_user_api_auth.php';

$currentUser = $_SESSION['user'];
$userId = $currentUser['id'];

$data = json_decode(file_get_contents('php://input'), true);

$firstName = trim($data['first_name'] ?? '');
$lastName = trim($data['last_name'] ?? '');
$phone = trim($data['phone'] ?? '');

if (empty($firstName) || empty($lastName) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $pdo = getDB();
    
    $stmt = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, phone = ?, onboarded = 1 WHERE id = ?');
    $result = $stmt->execute([$firstName, $lastName, $phone, $userId]);

    if ($result) {
        // Update session
        $_SESSION['user']['first_name'] = $firstName;
        $_SESSION['user']['last_name'] = $lastName;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['onboarded'] = 1;
        
        // logActivity($userId, 'Completed onboarding profile setup', 'user');

        echo json_encode([
            'success' => true, 
            'message' => 'Profile completed successfully',
            'user' => $_SESSION['user']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to complete onboarding']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
