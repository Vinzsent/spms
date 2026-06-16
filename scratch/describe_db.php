<?php
include 'includes/db.php';

$table = 'supply_request';
$result = $conn->query("DESCRIBE $table");

if ($result) {
    echo "Columns in '$table':\n";
    printf("%-20s %-20s %-10s %-10s %-20s %-20s\n", "Field", "Type", "Null", "Key", "Default", "Extra");
    while ($row = $result->fetch_assoc()) {
        printf("%-20s %-20s %-10s %-10s %-20s %-20s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL', 
            $row['Extra']);
    }

} else {
    echo "Error describing table: " . $conn->error . "\n";
}
?>
