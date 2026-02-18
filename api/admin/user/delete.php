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

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user id']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $currentId = $_SESSION['user']['id'] ?? null;
    if ($currentId !== null && intval($currentId) === intval($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot delete the currently logged-in user']);
        exit;
    }

    if ($user['role'] === 'Admin') {
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ? AND id != ?');
        $check->execute(['Admin', $id]);
        if ($check->fetchColumn() == 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'There must be at least one Admin']);
            exit;
        }
    }

    // Fetch details for logging
    $infoStmt = $pdo->prepare('SELECT first_name, last_name, email, role FROM users WHERE id = ?');
    $infoStmt->execute([$id]);
    $u = $infoStmt->fetch(PDO::FETCH_ASSOC);
    $uName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
    $uEmail = $u['email'] ?? 'Unknown';
    $uRole = $u['role'] ?? 'User';

    $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $del->execute([$id]);

    // Log activity
    require_once __DIR__ . '/../../../core/includes/logger.php';
    logActivity($_SESSION['user']['id'], "User Deleted: $uName($uEmail) - $uRole", 'danger', ['deleted_user_id' => $id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
