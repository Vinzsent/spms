<?php
include 'c:/xampp/htdocs/darts/includes/db.php';

$sql = "ALTER TABLE supply_request 
        ADD COLUMN IF NOT EXISTS item_number INT DEFAULT 0, 
        ADD COLUMN IF NOT EXISTS semester VARCHAR(50), 
        ADD COLUMN IF NOT EXISTS school_year VARCHAR(50)";

if ($conn->query($sql)) {
    echo "Columns added successfully or already exist.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}
?>
