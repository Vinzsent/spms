<?php
include 'includes/db.php';

echo "Database: darts\n";

// Check canvass table status column
$result = $conn->query("SHOW COLUMNS FROM canvass LIKE 'status'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Canvass table 'status' column info:\n";
    print_r($row);
}

// Check actual statuses in the table
$result = $conn->query("SELECT DISTINCT status FROM canvass");
if ($result) {
    echo "\nActual statuses in canvass table:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['status'] . "\n";
    }
}

$conn->close();
