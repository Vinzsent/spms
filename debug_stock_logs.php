<?php
include 'includes/db.php';

echo "<h3>Stock Logs Table Structure:</h3>";
$structure_result = $conn->query("DESCRIBE stock_logs");
if ($structure_result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

echo "<h3>Sample Stock Logs Data:</h3>";
$data_result = $conn->query("SELECT * FROM stock_logs ORDER BY date_created DESC LIMIT 10");
if ($data_result) {
    echo "<table border='1'>";
    // Get column names
    $fields = $data_result->fetch_fields();
    echo "<tr>";
    foreach ($fields as $field) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "</tr>";
    
    while ($row = $data_result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

if ($data_result && $data_result->num_rows > 0) {
    echo "<h3>Count of stock logs by receiver (if receiver column exists):</h3>";
    $receiver_count = $conn->query("SELECT receiver, COUNT(*) as count FROM stock_logs GROUP BY receiver");
    if ($receiver_count) {
        echo "<table border='1'>";
        echo "<tr><th>Receiver</th><th>Count</th></tr>";
        while ($row = $receiver_count->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['receiver'] ?? 'NULL') . "</td><td>" . $row['count'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Receiver column does not exist in stock_logs table, or error: " . $conn->error;
    }
}

$conn->close();
?>
