<?php
include 'includes/db.php';
$tables = ['property_request', 'supply_request'];
foreach ($tables as $t) {
    echo "=== TABLE: $t ===\n";
    $r = $conn->query("DESCRIBE $t");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            echo $row['Field'] . ' -> ' . $row['Type'] . "\n";
        }
    } else {
        echo "Table does not exist.\n";
    }
    echo "\n";
}
