<?php
include 'includes/db.php';

$sql = "ALTER TABLE purchase_orders 
        ADD COLUMN IF NOT EXISTS received_by INT AFTER created_at,
        ADD COLUMN IF NOT EXISTS received_date DATETIME AFTER received_by,
        ADD CONSTRAINT fk_received_by FOREIGN KEY (received_by) REFERENCES user(id) ON DELETE SET NULL";

if ($conn->query($sql)) {
    echo "Migration successful: columns added.\n";
} else {
    echo "Migration failed or columns already exist: " . $conn->error . "\n";
}
