<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user']['id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$canvass_id = isset($_GET['canvass_id']) ? intval($_GET['canvass_id']) : 0;

if (!$canvass_id) {
    echo json_encode(['success' => false, 'message' => 'Canvass ID is required']);
    exit;
}

function loadCanvass($canvass_id, $conn) {
    try {
        // Get canvass data
        $canvass_sql = "SELECT * FROM canvass WHERE canvass_id = $canvass_id";
        $canvass_result = $conn->query($canvass_sql);
        
        if (!$canvass_result || $canvass_result->num_rows === 0) {
            return ['success' => false, 'message' => 'Canvass not found'];
        }
        
        $canvass_data = $canvass_result->fetch_assoc();
        
        // Get items
        $items_sql = "SELECT * FROM canvass_items WHERE canvass_id = $canvass_id ORDER BY item_number";
        $items_result = $conn->query($items_sql);
        
        $items = [];
        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = [
                    'item_number' => $item['item_number'],
                    'supplier_name' => $item['supplier_name'],
                    'item_description' => $item['item_description'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['total_cost']
                ];
            }
        }
        
        return [
            'success' => true,
            'canvass_number' => $canvass_data['canvass_number'],
            'canvass_date' => $canvass_data['canvass_date'],
            'total_amount' => $canvass_data['total_amount'],
            'status' => $canvass_data['status'],
            'notes' => $canvass_data['notes'],
            'items' => $items
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Process the load request
$result = loadCanvass($canvass_id, $conn);
echo json_encode($result);
?>
