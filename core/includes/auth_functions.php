<?php
// Start session only if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is authenticated and optionally has a specific role.
 * 
 * @param string|null $requiredRole The role required (e.g., 'Admin', 'Customer'). Null for any logged-in user.
 * @param bool $isApi Whether this is an API call (returns JSON) or a page call (redirects).
 */
function checkAuth($requiredRole = null, $isApi = false) {
    // Check if the base user data exists in the session
    $isAuthenticated = isset($_SESSION['user']);
    // Verify role if a specific one is required (e.g., 'Admin')
    $hasRole = $requiredRole === null || (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === $requiredRole);

    if (!$isAuthenticated || !$hasRole) {
        if ($isApi) {
            http_response_code($isAuthenticated ? 403 : 401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $isAuthenticated ? 'Forbidden: You do not have permission.' : 'Unauthorized: Please log in.'
            ]);
            exit;
        } else {
            // Calculate path back to root for login redirect
            // This is a simple way to find login.php from any depth
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $scriptPath = $_SERVER['SCRIPT_NAME'];
            $dir = dirname($scriptPath);
            
            // For this project, we can assume the root is /marsla_restaurant/
            // But let's be more dynamic
            $baseUrl = $protocol . '://' . $host . '/marsla_restaurant/';
            header('Location: ' . $baseUrl . 'login.php');
            exit;
        }
    }
}

/**
 * Prevent browser caching for sensitive pages.
 */
function preventCaching() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
}
