<?php
session_start();

// Admin password
$admin_password = 'misadmin';

// Check if password was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    $entered_password = $_POST['admin_password'];
    
    // Verify the password
    if ($entered_password === $admin_password) {
        // Set admin session flag
        $_SESSION['admin_verified'] = true;
        $_SESSION['admin_verified_time'] = time();
        
        // Redirect to settings page
        header("Location: ../pages/users.php");
        exit;
    } else {
        // Wrong password - redirect back with error
        $_SESSION['error'] = 'Incorrect admin password. Access denied.';
        header("Location: ../dashboard.php");
        exit;
    }
} else {
    // No password submitted - redirect back
    header("Location: ../dashboard.php");
    exit;
}
?> 