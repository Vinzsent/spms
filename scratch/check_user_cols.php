<?php
include 'includes/db.php';
$r = $conn->query('DESCRIBE user');
while ($row = $r->fetch_assoc()) {
    echo $row['Field'] . ' -> ' . $row['Type'] . "\n";
}
