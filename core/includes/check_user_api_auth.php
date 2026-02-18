<?php
require_once __DIR__ . '/auth_functions.php';

// Ensure the user is logged in, return JSON error if not
checkAuth(null, true);
