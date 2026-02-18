<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/config/db.php';
require_once __DIR__ . '/../../core/includes/check_admin_api_auth.php';

$pdo = getDB();
try {
    // pagination and search
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(200, (int)$_GET['limit'])) : 25;
    $offset = ($page - 1) * $limit;
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';

    $countSql = 'SELECT COUNT(*) as c FROM users';
    $dataSql = 'SELECT id, first_name, last_name, email, phone, role, created_at FROM users';
    $where = '';
    $params = [];
    if ($q !== '') {
        $where = ' WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR role LIKE ? OR created_at LIKE ?)';
        $like = "%$q%";
        $params = [$like, $like, $like, $like, $like];
        $countSql .= $where;
        $dataSql .= $where;
    }

    $total = $pdo->prepare($countSql);
    $total->execute($params);
    $totalCount = (int)$total->fetchColumn();

    $dataSql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($dataSql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentId = $_SESSION['user']['id'] ?? null;
    $out = array_map(function($r) use ($currentId){
        $r['is_current'] = ($currentId !== null && intval($r['id']) === intval($currentId)) ? 1 : 0;
        return $r;
    }, $rows);

    echo json_encode(['data' => $out, 'total' => $totalCount, 'page' => $page, 'limit' => $limit]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch users']);
}
