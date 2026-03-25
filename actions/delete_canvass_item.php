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
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$canvass_item_id = $input['canvass_item_id'] ?? null;

if (!$canvass_item_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Get canvass_id from the item before deletion
    $check_sql = "SELECT canvass_id FROM canvass_items WHERE canvass_item_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $canvass_item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Item not found');
    }

    $row = $result->fetch_assoc();
    $canvass_id = $row['canvass_id'];

    // 2. Delete the item
    $delete_sql = "DELETE FROM canvass_items WHERE canvass_item_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $canvass_item_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to delete item');
    }

    // 3. Recalculate canvass total
    $sum_sql = "SELECT SUM(total_cost) as grand_total FROM canvass_items WHERE canvass_id = ?";
    $stmt = $conn->prepare($sum_sql);
    $stmt->bind_param("i", $canvass_id);
    $stmt->execute();
    $sum_result = $stmt->get_result();
    $sum_row = $sum_result->fetch_assoc();
    $new_grand_total = $sum_row['grand_total'] ?? 0;

    // 4. Update canvass total
    $update_canvass_sql = "UPDATE canvass SET total_amount = ? WHERE canvass_id = ?";
    $stmt = $conn->prepare($update_canvass_sql);
    $stmt->bind_param("di", $new_grand_total, $canvass_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update canvass total');
    }

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
