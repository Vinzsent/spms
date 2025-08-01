<?php

include '../includes/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $transaction_id = $_POST['transaction_id'] ?? '';
        $status = $_POST['status'] ?? '';

        if (empty($transaction_id) || empty($status)) {
            throw new Exception('Missing required fields');
        }

        // Update the transaction status
        $stmt = $conn->prepare("UPDATE supplier_transaction SET status = ? WHERE transaction_id = ?");
        $stmt->bind_param("si", $status, $transaction_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to update status');
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>