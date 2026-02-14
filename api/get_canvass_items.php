<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

try {
    // Get all item details including supplier info
    $sql = "SELECT ci.item_description, ci.supplier_name, ci.quantity, ci.unit_cost 
            FROM canvass_items ci
            WHERE ci.item_description IS NOT NULL 
            AND ci.item_description != '' 
            ORDER BY ci.item_description ASC";


    $result = $conn->query($sql);

    if ($result) {
        $unique_items = [];
        $items = [];

        // Filter unique items (keeping most recent due to ORDER BY)
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'description' => $row['item_description'],
                'supplier' => $row['supplier_name'],
                'quantity' => $row['quantity'],
                'unit_cost' => $row['unit_cost']
            ];
        }

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch canvass items: ' . $conn->error
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
