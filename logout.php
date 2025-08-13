<?php
/**
 * Logout Script
 * 0S-CARE - Cancer Patient Care Management System
 */

session_start();

// Log the logout event
if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    logError("User logout: " . ($_SESSION['email'] ?? 'unknown'), 'INFO');
}

// Destroy session
session_unset();
session_destroy();

// Clear any remember me cookies
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: login.php?logged_out=1');
exit;
?>