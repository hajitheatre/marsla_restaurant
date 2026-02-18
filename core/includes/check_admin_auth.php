<?php
require_once __DIR__ . '/auth_functions.php';

// Ensure the user is an Admin, otherwise redirect to login
checkAuth('Admin');

// Prevent browser caching for security
preventCaching();

// Expose the current user for convenience
$currentUser = $_SESSION['user'];
