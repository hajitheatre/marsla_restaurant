<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/config/db.php';
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$id = isset($input['id']) ? (int)$input['id'] : 0;
$first_name = trim($input['first_name'] ?? '');
$last_name = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? null;
$role = isset($input['role']) ? trim($input['role']) : null;

$errors = [];
if ($id <= 0) $errors['id'] = 'Invalid user id';
if ($first_name === '') $errors['first_name'] = 'First name is required';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
if ($password !== null && $password !== '' && strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';
if ($role !== null && $role !== 'Admin' && $role !== 'Customer' && $role !== 'Rider') $errors['role'] = 'Invalid role';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT id, email, role FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $currentId = $_SESSION['user']['id'] ?? null;
    if ($currentId !== null && intval($currentId) === intval($id) && $role !== null && $role !== $existing['role']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['role' => 'Cannot change role of the currently logged-in user']]);
        exit;
    }

    if ($existing['role'] === 'Admin' && $role !== null && $role !== 'Admin') {
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ? AND id != ?');
        $check->execute(['Admin', $id]);
        if ($check->fetchColumn() == 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['role' => 'There must be at least one Admin']]);
            exit;
        }
    }

    if ($email !== $existing['email']) {
        $u = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id != ?');
        $u->execute([$email, $id]);
        if ($u->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'errors' => ['email' => 'Email already exists']]);
            exit;
        }
    }

    $fields = [];
    $params = [];
    $fields[] = 'first_name = ?'; $params[] = $first_name;
    $fields[] = 'last_name = ?'; $params[] = $last_name;
    $fields[] = 'email = ?'; $params[] = $email;
    $fields[] = 'phone = ?'; $params[] = $phone;
    if ($role !== null) { $fields[] = 'role = ?'; $params[] = $role; }
    if ($password !== null && $password !== '') { $fields[] = 'password_hash = ?'; $params[] = password_hash($password, PASSWORD_DEFAULT); }

    $params[] = $id;
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $upd = $pdo->prepare($sql);
    $upd->execute($params);

    // Log activity
    require_once __DIR__ . '/../../../core/includes/logger.php';
    logActivity($_SESSION['user']['id'], "Admin updated user: $email", 'user', ['updated_user_id' => $id, 'updated_user_email' => $email]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
