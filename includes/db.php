<?php
$host   = '127.0.0.1';
$user   = 'root';
$pass   = '';
$dbname = 'supplier_db';

mysqli_report(MYSQLI_REPORT_OFF); // Suppress default exceptions; handle manually below
$conn = new mysqli($host, $user, $pass, $dbname);

// Use the standalone function — safe even when the mysqli object itself is in a broken state
if (mysqli_connect_error()) {
    $errMsg = '(' . mysqli_connect_errno() . ') ' . mysqli_connect_error();
    error_log('Database connection failed: ' . $errMsg);
    die('Database connection failed. Please check your database settings.');
}
