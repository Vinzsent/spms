<?php
include 'c:/xampp/htdocs/darts/includes/db.php';

echo "--- supply_request Table ---\n";
$res = $conn->query("DESCRIBE supply_request");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n--- Tables in Database ---\n";
$res = $conn->query("SHOW TABLES");
if ($res) {
    while($row = $res->fetch_row()) {
        echo $row[0] . "\n";
    }
}
?>
