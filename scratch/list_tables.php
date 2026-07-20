<?php
include 'includes/db.php';
$res = $conn->query("SHOW TABLES");
echo "TABLES IN SYSTEM:\n";
while($r = $res->fetch_row()) {
    echo "- " . $r[0] . "\n";
}
$conn->close();
