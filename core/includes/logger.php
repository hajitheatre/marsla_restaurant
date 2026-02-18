<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Logs a user activity into the database.
 * 
 * @param int|null $userId The ID of the user performing the action.
 * @param string $action A description of the action (e.g., 'New order placed').
 * @param string $type The type of activity ('info', 'success', 'warning', 'danger').
 * @param array|null $meta Optional metadata to store as JSON.
 * @return bool True on success, false on failure.
 */
function logActivity($userId, $action, $type = 'info', $meta = null) {
    try {
        $pdo = getDB();
        // Insert a new record into activity_logs. user_id can be NULL for system actions.
        $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, type, meta) VALUES (?, ?, ?, ?)');
        
        // Convert metadata array to JSON if provided
        $metaJson = $meta ? json_encode($meta) : null;
        
        return $stmt->execute([
            $userId,
            $action,
            $type,
            $metaJson
        ]);
    } catch (Exception $e) {
        error_log('Failed to log activity: ' . $e->getMessage());
        return false;
    }
}
?>
