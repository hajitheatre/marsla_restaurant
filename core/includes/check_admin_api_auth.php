<?php
require_once __DIR__ . '/auth_functions.php';

// Ensure the user is an Admin, return JSON error if not
checkAuth('Admin', true);
