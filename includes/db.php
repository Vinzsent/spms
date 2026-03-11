<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'supplier_db';

mysqli_report(MYSQLI_REPORT_OFF); // Disable default error reporting to handle it manually
$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    // We don't die() here for API compatibility. 
    // Individual scripts should check if $conn is valid or use error handling.
    // However, for existing pages, we keep a fallback or let them handle it.
}
