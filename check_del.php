<?php
include 'includes/db.php';
$result = $conn->query("SHOW COLUMNS FROM purchase_orders LIKE 'is_deleted'");
if ($result->num_rows > 0) {
    echo "Exists\n";
} else {
    echo "Missing\n";
}
