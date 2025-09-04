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

$po_id = isset($_GET['po_id']) ? intval($_GET['po_id']) : 0;

if (!$po_id) {
    echo json_encode(['success' => false, 'message' => 'Purchase Order ID is required']);
    exit;
}

function loadPurchaseOrder($po_id, $conn) {
    try {
        // Get PO data
        $po_sql = "SELECT * FROM purchase_orders WHERE po_id = $po_id";
        $po_result = $conn->query($po_sql);
        
        if (!$po_result || $po_result->num_rows === 0) {
            return ['success' => false, 'message' => 'Purchase order not found'];
        }
        
        $po_data = $po_result->fetch_assoc();
        
        // Get items
        $items_sql = "SELECT * FROM purchase_order_items WHERE po_id = $po_id ORDER BY item_number";
        $items_result = $conn->query($items_sql);
        
        $items = [];
        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = [
                    'item_number' => $item['item_number'],
                    'item_description' => $item['item_description'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['line_total']
                ];
            }
        }
        
        return [
            'success' => true,
            'po_number' => $po_data['po_number'],
            'po_date' => $po_data['po_date'],
            'supplier_name' => $po_data['supplier_name'],
            'supplier_address' => $po_data['supplier_address'],
            'payment_method' => $po_data['payment_method'],
            'payment_details' => $po_data['payment_details'],
            'cash_amount' => $po_data['cash_amount'],
            'total_amount' => $po_data['total_amount'],
            'status' => $po_data['status'],
            'notes' => $po_data['notes'],
            'items' => $items
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Process the load request
$result = loadPurchaseOrder($po_id, $conn);
echo json_encode($result);
?>
