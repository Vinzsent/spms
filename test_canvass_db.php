<?php
// Test script to check and create canvass tables
include 'includes/db.php';

echo "Testing database connection and canvass tables...\n";

// Check if tables exist
$tables_to_check = ['canvass', 'canvass_items'];
$missing_tables = [];

foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $missing_tables[] = $table;
    } else {
        echo "✓ Table '$table' exists\n";
    }
}

if (!empty($missing_tables)) {
    echo "Missing tables: " . implode(', ', $missing_tables) . "\n";
    echo "Creating tables...\n";

    // Read and execute the SQL file
    $sql_content = file_get_contents('db/canvass_tables.sql');

    // Split by semicolon and execute each statement
    $statements = explode(';', $sql_content);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement)) {
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } else {
                echo "✗ Error: " . $conn->error . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
} else {
    echo "All required tables exist!\n";
}

// Test insert functionality
echo "\nTesting canvass save functionality...\n";

$test_data = [
    'canvass_number' => 'TEST-' . date('Y-m-d-His'),
    'canvass_date' => date('Y-m-d'),
    'items' => [
        [
            'supplier' => 'Test Supplier',
            'description' => 'Test Item',
            'quantity' => 1,
            'unit_cost' => 100.00
        ]
    ]
];

// Simulate the save function
try {
    $conn->begin_transaction();

    $canvass_date = $conn->real_escape_string($test_data['canvass_date']);

    $sql = "INSERT INTO canvass (canvass_date, created_by) VALUES ('$canvass_date', 1)";

    if ($conn->query($sql)) {
        $canvass_id = $conn->insert_id;
        echo "✓ Canvass created with ID: $canvass_id\n";

        // Insert test item
        $item = $test_data['items'][0];
        $supplier_name = $conn->real_escape_string($item['supplier']);
        $description = $conn->real_escape_string($item['description']);
        $quantity = floatval($item['quantity']);
        $unit_cost = floatval($item['unit_cost']);
        $total_cost = $quantity * $unit_cost;

        $item_sql = "INSERT INTO canvass_items (canvass_id, item_number, supplier_name, item_description, quantity, unit_cost, total_cost) VALUES ($canvass_id, 1, '$supplier_name', '$description', $quantity, $unit_cost, $total_cost)";

        if ($conn->query($item_sql)) {
            echo "✓ Test item inserted successfully\n";
            $conn->commit();
            echo "✓ Test completed successfully!\n";
        } else {
            throw new Exception("Failed to insert item: " . $conn->error);
        }
    } else {
        throw new Exception("Failed to insert canvass: " . $conn->error);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo "✗ Test failed: " . $e->getMessage() . "\n";
}

$conn->close();
