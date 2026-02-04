<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

try {
    // Get distinct item descriptions from canvass_items table
    $sql = "SELECT DISTINCT item_description 
            FROM canvass_items 
            WHERE item_description IS NOT NULL 
            AND item_description != '' 
            ORDER BY item_description ASC";

    $result = $conn->query($sql);

    if ($result) {
        $unique_items = [];
        $items = [];

        // Filter unique items (keeping most recent due to ORDER BY)
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'description' => $row['item_description']
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
