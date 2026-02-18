<?php
// core/session_check.php
// Handles server-side session timeout (15 minutes)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout_duration = 900; // 15 minutes in seconds

if (isset($_SESSION['last_activity'])) {
    $duration = time() - $_SESSION['last_activity'];
    if ($duration > $timeout_duration) {
        // Session timed out
        session_unset();
        session_destroy();
        // Redirect to login with timeout flag
        header("Location: ../login.php?timeout=1");
        exit();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
