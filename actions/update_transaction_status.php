<?php
session_start();
include '../includes/db.php';

// Set header for JSON response
header('Content-Type: application/json');

// Get form data
$transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';
$issued_to_department = isset($_POST['issued_to_department']) ? trim($_POST['issued_to_department']) : '';

// Validate input
if ($transaction_id <= 0 || empty($new_status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID or status']);
    exit;
}

// If status is "Issued", validate that a department is selected
if ($new_status === 'Issued' && empty($issued_to_department)) {
    echo json_encode(['success' => false, 'message' => 'Please select a department/unit to issue the item to']);
    exit;
}

// Update the transaction status and issued_to_department if provided
if ($new_status === 'Issued' && !empty($issued_to_department)) {
    $sql = "UPDATE supplier_transaction SET status = ?, issued_to_department = ?, issued_date = NOW() WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_status, $issued_to_department, $transaction_id);
} else {
    $sql = "UPDATE supplier_transaction SET status = ? WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $transaction_id);
}

if ($stmt->execute()) {
    $message = 'Status updated to ' . $new_status;
    if ($new_status === 'Issued' && !empty($issued_to_department)) {
        $message .= ' and assigned to ' . $issued_to_department;
    }
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>