<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $serial_number = $_POST['serial_number'] ?? '';
    $type = $_POST['type'] ?? '';
    $size = $_POST['size'] ?? '';
    $model = $_POST['model'] ?? '';
    $warranty_info = $_POST['warranty_info'] ?? '';
    $additional_notes = $_POST['additional_notes'] ?? '';

    if (empty($transaction_id)) {
        echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
        exit;
    }

    try {
        // Check if specifications already exist for this transaction
        $check_sql = "SELECT spec_id FROM transaction_specifications WHERE transaction_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $transaction_id);
        $check_stmt->execute();
        $existing_spec = $check_stmt->get_result()->fetch_assoc();

        if ($existing_spec) {
            // Update existing specifications
            $sql = "UPDATE transaction_specifications SET 
                    brand = ?, 
                    serial_number = ?, 
                    type = ?, 
                    size = ?, 
                    model = ?, 
                    warranty_info = ?, 
                    additional_notes = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE transaction_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $brand, $serial_number, $type, $size, $model, $warranty_info, $additional_notes, $transaction_id);
        } else {
            // Insert new specifications
            $sql = "INSERT INTO transaction_specifications 
                    (transaction_id, brand, serial_number, type, size, model, warranty_info, additional_notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssssss", $transaction_id, $brand, $serial_number, $type, $size, $model, $warranty_info, $additional_notes);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Specifications saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving specifications: ' . $stmt->error]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 