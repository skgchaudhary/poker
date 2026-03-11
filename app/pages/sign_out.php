<?php
session_start();
require_once __DIR__ . '/../connect.php';

// Unset only the logged-in user
unset($_SESSION['user']);

// Optional: regenerate session ID for safety
session_regenerate_id(true);

// Redirect to create session page
header('Location: ' . $BASE . '/');
exit;
