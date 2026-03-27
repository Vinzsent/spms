<?php
header('Content-Type: application/json');
include '../includes/auth.php';
include '../includes/db.php';

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get input
$data = json_decode(file_get_contents('php://input'), true);
$inventory_id = $data['inventory_id'] ?? null;

if (!$inventory_id) {
    echo json_encode(['success' => false, 'message' => 'Inventory ID is required']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Check if the item exists
    $check_sql = "SELECT item_name FROM property_inventory WHERE inventory_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Item not found');
    }

    // 2. Delete the item
    // Note: In some systems, we might want to delete related logs first to avoid foreign key constraints,
    // but here we'll assume cascading delete or no constraints if not specified.
    // However, it's safer to just delete the item if that's what the user asked.
    $delete_sql = "DELETE FROM property_inventory WHERE inventory_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $inventory_id);

    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
    } else {
        throw new Exception('Failed to delete item');
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
