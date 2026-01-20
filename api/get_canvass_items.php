<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

try {
    // Get items with details, prioritizing most recent
    $sql = "SELECT ci.item_description, ci.quantity, ci.unit_cost 
            FROM canvass_items ci
            JOIN canvass c ON ci.canvass_id = c.canvass_id
            WHERE ci.item_description IS NOT NULL 
            AND ci.item_description != '' 
            ORDER BY c.created_at DESC";

    $result = $conn->query($sql);

    if ($result) {
        $unique_items = [];
        $items = [];

        // Filter unique items (keeping most recent due to ORDER BY)
        while ($row = $result->fetch_assoc()) {
            $desc_lower = strtolower(trim($row['item_description']));
            if (!isset($unique_items[$desc_lower])) {
                $unique_items[$desc_lower] = true;
                $items[] = [
                    'description' => $row['item_description'],
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost']
                ];
            }
        }

        // Sort alphabetically by description
        usort($items, function ($a, $b) {
            return strcasecmp($a['description'], $b['description']);
        });

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
