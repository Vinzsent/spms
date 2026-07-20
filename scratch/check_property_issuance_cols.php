<?php
include 'includes/db.php';
$r = $conn->query('DESCRIBE property_issuance');
if ($r) {
    while ($row = $r->fetch_assoc()) {
        echo $row['Field'] . ' -> ' . $row['Type'] . "\n";
    }
} else {
    echo "No property_issuance table or error.\n";
}
