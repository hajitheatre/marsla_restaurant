<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/config/db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Only email and password are required on registration. Derive names from email local-part.
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role = $data['role'] ?? 'Customer';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

$pdo = getDB();

// Check if email exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email already registered.']);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

// derive names from local-part
$local = strstr($email, '@', true);
$first_name = '';
$last_name = '';
if ($local !== false) {
    // split by delimiters
    $parts = preg_split('/[\.\_\-\+]/', $local);
    if (count($parts) >= 2) {
        $first_name = ucfirst($parts[0]);
        $last_name = ucfirst(implode(' ', array_slice($parts, 1)));
    } else {
        $first_name = ucfirst($parts[0]);
        $last_name = '';
    }
}

// insert user
$insert = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
try {
    $insert->execute([$first_name, $last_name, $email, $password_hash, $role]);
    $userId = $pdo->lastInsertId();

    // Auto-login the user by storing session
    $_SESSION['user'] = [
        'id' => $userId,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'role' => $role,
        'onboarded' => 0,
        'phone' => ''
    ];

    // Log registration activity
    require_once __DIR__ . '/../../core/includes/logger.php';
    logActivity($userId, "New User Registered", 'success');

    echo json_encode(['success' => true, 'message' => 'Registration successful.', 'user' => $_SESSION['user']]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed.']);
}
