<?php
require_once __DIR__ . '/../../../core/includes/check_admin_api_auth.php';
require_once __DIR__ . '/../../../core/config/db.php';

try {
    $pdo = getDB();
    
    // Fetch all activities with user details
    $stmt = $pdo->query('
        SELECT 
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
    ');
    
    $activities = $stmt->fetchAll();
    
    $filename = "activity_logs_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['Activity', 'Type', 'Time']);
    
    foreach ($activities as $act) {
        $firstName = $act['first_name'] ?? '';
        $lastName = $act['last_name'] ?? '';
        $email = $act['email'] ?? '';
        $role = $act['role'] ?? '';
        
        $name = trim($firstName . ' ' . $lastName);
        if (empty($name)) $name = $email ?: 'System';
        
        // Final formatted activity: "[Action]: By [Name]([Email]) - [Role]"
        $userSuffix = !empty($email) ? "By $name($email) - $role" : "By $name";
        $fullActivity = $act['action'] . ": " . $userSuffix;
        
        // Format time: "Really current time and full date with day"
        // Eg: Mittwoch, 18. Februar 2026 03:54
        $time = date('l, d F Y H:i', strtotime($act['created_at']));
        
        fputcsv($output, [
            $fullActivity,
            ucfirst($act['type']),
            $time
        ]);
    }
    
    fclose($output);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo "Failed to generate export: " . $e->getMessage();
}
?>
