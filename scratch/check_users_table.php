<?php
include 'includes/db.php';
$r = $conn->query('DESCRIBE users');
if ($r) {
    while ($row = $r->fetch_assoc()) {
        echo $row['Field'] . ' -> ' . $row['Type'] . "\n";
    }
} else {
    echo "No users table.\n";
}
