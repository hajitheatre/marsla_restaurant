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
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');

if (empty($firstName) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'First name and email are required']);
    exit;
}

try {
    $pdo = getDB();
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email is already in use']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?');
    $result = $stmt->execute([$firstName, $lastName, $email, $phone, $userId]);

    if ($result) {
        // Update session
        $_SESSION['user']['first_name'] = $firstName;
        $_SESSION['user']['last_name'] = $lastName;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;
        
        // logActivity($userId, 'Updated profile details', 'info');

        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully',
            'user' => $_SESSION['user']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
