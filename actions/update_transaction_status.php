<?php
session_start();
include '../includes/db.php';

// Set header for JSON response
header('Content-Type: application/json');

// Get form data
$transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

// Validate input
if ($transaction_id <= 0 || empty($new_status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID or status']);
    exit;
}

// Update the transaction status
$sql = "UPDATE supplier_transaction SET status = ? WHERE transaction_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_status, $transaction_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Status updated to ' . $new_status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>