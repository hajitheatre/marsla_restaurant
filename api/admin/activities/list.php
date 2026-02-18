<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';
require_once __DIR__ . '/../../../core/config/db.php';

try {
    $pdo = getDB();
    
    // Fetch latest 15 activities with user details
    $stmt = $pdo->query('
        SELECT 
            al.id, 
            al.action, 
            al.type, 
            al.created_at,
            u.first_name, 
            u.last_name,
            u.email,
            u.role
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT 15
    ');
    
    $activities = $stmt->fetchAll();
    
    // Format activities for the UI
    $formatted = array_map(function($act) {
        $firstName = $act['first_name'] ?? '';
        $lastName = $act['last_name'] ?? '';
        $email = $act['email'] ?? '';
        $role = $act['role'] ?? '';
        
        $name = trim($firstName . ' ' . $lastName);
        if (empty($name)) {
            $name = $email ?: 'System';
        }
        
        // Construct the "By [User]([Email]) - [Role]" suffix
        $userSuffix = "";
        if (!empty($email)) {
            $userSuffix = "By $name($email) - $role";
        } else {
            $userSuffix = "By $name";
        }

        // The action might already have details, we append the user info if it's not already there
        // For consistency with user request: "[Action]: [UserSuffix]"
        $action = $act['action'];
        
        return [
            'id' => $act['id'],
            'action' => $action,
            'type' => $act['type'],
            'time' => $act['created_at'],
            'user_name' => $name,
            'user_email' => $email,
            'user_role' => $role,
            'user_suffix' => $userSuffix
        ];
    }, $activities);
    
    echo json_encode(['success' => true, 'data' => $formatted]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch activities']);
}
?>
