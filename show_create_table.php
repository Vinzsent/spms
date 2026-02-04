<?php
include 'includes/db.php';

$result = $conn->query("SHOW CREATE TABLE canvass");
if ($result) {
    $row = $result->fetch_assoc();
    echo "CREATE TABLE statement:\n";
    echo $row['Create Table'] . "\n";
}

$conn->close();
