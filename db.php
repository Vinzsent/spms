<?php
$host = 'locahost';
$user = 'vleserver';
$pass = 'M!s@dm1n';
$dbname = 'supplier_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
