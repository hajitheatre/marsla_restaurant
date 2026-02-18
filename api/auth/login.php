<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../core/config/db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT id, first_name, last_name, email, password_hash, role, onboarded, phone FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    exit;
}

// Authentication successful
$_SESSION['user'] = [
    'id' => $user['id'],
    'first_name' => $user['first_name'],
    'last_name' => $user['last_name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'onboarded' => $user['onboarded'],
    'phone' => $user['phone']
];

// Log login activity
require_once __DIR__ . '/../../core/includes/logger.php';
logActivity($user['id'], "User Login", 'info');

echo json_encode(['success' => true, 'message' => 'Login successful.', 'user' => $_SESSION['user']]);


