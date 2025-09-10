<?php
// Test the AJAX response from Inventory.php
$url = 'http://localhost/spms/pages/Inventory.php?ajax=1&page=1';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-type: application/x-www-form-urlencoded\r\n'
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "Error: Could not fetch AJAX response\n";
} else {
    echo "AJAX Response Length: " . strlen($response) . " characters\n";
    echo "First 500 characters:\n";
    echo substr($response, 0, 500) . "\n\n";
    
    // Check if response contains expected elements
    if (strpos($response, 'inventoryTable') !== false) {
        echo "✓ Contains inventoryTable\n";
    } else {
        echo "✗ Missing inventoryTable\n";
    }
    
    if (strpos($response, 'Supply In-charge') !== false) {
        echo "✓ Contains Supply In-charge data\n";
    } else {
        echo "✗ Missing Supply In-charge data\n";
    }
    
    if (strpos($response, 'table') !== false) {
        echo "✓ Contains table elements\n";
    } else {
        echo "✗ Missing table elements\n";
    }
}
?>
