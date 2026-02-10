<?php
include 'includes/db.php';
$res = $conn->query('DESCRIBE purchase_orders');
if (!$res) {
    die("Error: " . $conn->error);
}
while ($row = $res->fetch_assoc()) {
    echo "COL: " . $row['Field'] . "\n";
}
