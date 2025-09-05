<?php
header('Content-Type: application/json');
include '../includes/auth.php';
include '../includes/db.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['po_id']) || !is_numeric($input['po_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid purchase order ID']);
    exit;
}

$po_id = intval($input['po_id']);

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if purchase order exists and get details
    $check_query = "SELECT po_number, status FROM purchase_orders WHERE po_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Purchase order not found']);
        exit;
    }
    
    $po = $result->fetch_assoc();
    
    // Check if purchase order can be deleted (only Draft and Cancelled can be deleted)
    if (!in_array($po['status'], ['Draft', 'Cancelled'])) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Cannot delete purchase order with status: ' . $po['status']]);
        exit;
    }
    
    // Delete purchase order items first (due to foreign key constraint)
    $delete_items_query = "DELETE FROM purchase_order_items WHERE po_id = ?";
    $stmt = $conn->prepare($delete_items_query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    
    // Delete purchase order status history
    $delete_history_query = "DELETE FROM purchase_order_status_history WHERE po_id = ?";
    $stmt = $conn->prepare($delete_history_query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    
    // Delete purchase order attachments
    $delete_attachments_query = "DELETE FROM purchase_order_attachments WHERE po_id = ?";
    $stmt = $conn->prepare($delete_attachments_query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    
    // Delete the purchase order record
    $delete_po_query = "DELETE FROM purchase_orders WHERE po_id = ?";
    $stmt = $conn->prepare($delete_po_query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete purchase order']);
        exit;
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Purchase order ' . $po['po_number'] . ' deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
