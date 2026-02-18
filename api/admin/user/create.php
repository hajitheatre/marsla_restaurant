<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$first_name = trim($input['first_name'] ?? '');
$last_name = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';
$role = trim($input['role'] ?? 'Customer');

$errors = [];
if ($first_name === '') $errors['first_name'] = 'First name is required';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
if (!is_string($password) || strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';
if ($role !== 'Admin' && $role !== 'Customer' && $role !== 'Rider') $errors['role'] = 'Invalid role';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'errors' => ['email' => 'Email already exists']]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $pdo->prepare('INSERT INTO users (first_name, last_name, email, phone, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $ins->execute([$first_name, $last_name, $email, $phone, $hash, $role]);
    $id = $pdo->lastInsertId();

    // Log activity
    require_once __DIR__ . '/../../../core/includes/logger.php';
    logActivity($_SESSION['user']['id'], "New User Added : $first_name $last_name($email) - $role", 'info', ['new_user_id' => $id]);

    echo json_encode(['success' => true, 'data' => ['id' => $id, 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'phone' => $phone, 'role' => $role]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
