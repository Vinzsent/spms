<?php
header('Content-Type: application/json');
include '../includes/auth.php';
include '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid purchase order ID']);
    exit;
}

$po_id = intval($_GET['id']);

try {
    // Get purchase order details
    $po_query = "
        SELECT 
            p.po_id,
            p.po_number,
            p.po_date,
            p.supplier_name,
            p.supplier_address,
            p.payment_method,
            p.payment_details,
            p.cash_amount,
            p.subtotal,
            p.total_amount,
            p.status,
            p.notes,
            p.created_at,
            p.updated_at,
            CONCAT(u.first_name, ' ', u.last_name) as created_by_name
        FROM purchase_orders p
        LEFT JOIN user u ON p.created_by = u.id
        WHERE p.po_id = ?
    ";
    
    $stmt = $conn->prepare($po_query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    $po_result = $stmt->get_result();
    
    if ($po_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Purchase order not found']);
        exit;
    }
    
    $purchase_order = $po_result->fetch_assoc();
    
    // Get purchase order items
    $items_query = "
        SELECT 
            poi_id,
            item_number,
            item_description,
            quantity,
            unit_cost,
            line_total
        FROM purchase_order_items
        WHERE po_id = ?
        ORDER BY item_number ASC
    ";
    
    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    echo json_encode([
        'success' => true,
        'purchase_order' => $purchase_order,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
