<?php
session_start();
include '../includes/db.php';

// Set header for JSON response
header('Content-Type: application/json');

// Get form data
$transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';
$issued_to_department = isset($_POST['issued_to_department']) ? trim($_POST['issued_to_department']) : '';

// Get additional data for quantity computation
$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$quantity_to_issue = isset($_POST['quantity_to_issue']) ? intval($_POST['quantity_to_issue']) : 0;
$remaining_quantity = isset($_POST['remaining_quantity']) ? intval($_POST['remaining_quantity']) : 0;

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

try {
    // Start transaction
    $conn->begin_transaction();

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

    if (!$stmt->execute()) {
        throw new Exception('Failed to update transaction status');
    }

    // If this is an issuance, update the supply_request table
    if ($new_status === 'Issued') {
        // Get the user name for issued_by
        $user_name = $_SESSION['name'] ?? $_SESSION['user']['name'] ?? $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'] ?? 'System';
        
        // If there's a request_id from the form, update that specific request
        if ($request_id > 0) {
            $update_request_sql = "UPDATE supply_request SET issued_by = ?, issued_date = NOW() WHERE request_id = ?";
            $update_request_stmt = $conn->prepare($update_request_sql);
            $update_request_stmt->bind_param("si", $user_name, $request_id);
            
            if (!$update_request_stmt->execute()) {
                throw new Exception('Failed to update supply request status');
            }
        } else {
            // If no specific request_id, find the most recent approved request that hasn't been issued yet
            $find_request_sql = "SELECT request_id FROM supply_request 
                                WHERE approved_by IS NOT NULL 
                                AND issued_by IS NULL 
                                ORDER BY approved_date DESC 
                                LIMIT 1";
            $find_request_result = $conn->query($find_request_sql);
            
            if ($find_request_result && $find_request_result->num_rows > 0) {
                $request_row = $find_request_result->fetch_assoc();
                $auto_request_id = $request_row['request_id'];
                
                $update_request_sql = "UPDATE supply_request SET issued_by = ?, issued_date = NOW() WHERE request_id = ?";
                $update_request_stmt = $conn->prepare($update_request_sql);
                $update_request_stmt->bind_param("si", $user_name, $auto_request_id);
                
                if (!$update_request_stmt->execute()) {
                    throw new Exception('Failed to update supply request status');
                }
            }
        }
        
        // If there's quantity computation, update the quantity
        if ($quantity_to_issue > 0 && $remaining_quantity >= 0) {
            $update_quantity_sql = "UPDATE supplier_transaction SET quantity = ? WHERE transaction_id = ?";
            $update_quantity_stmt = $conn->prepare($update_quantity_sql);
            $update_quantity_stmt->bind_param("ii", $remaining_quantity, $transaction_id);
            
            if (!$update_quantity_stmt->execute()) {
                throw new Exception('Failed to update quantity');
            }
        }
    }

    // Commit transaction
    $conn->commit();

    $message = 'Status updated to ' . $new_status;
    if ($new_status === 'Issued' && !empty($issued_to_department)) {
        $message .= ' and assigned to ' . $issued_to_department;
    }
    if ($quantity_to_issue > 0) {
        $message .= '. Quantity updated: ' . $quantity_to_issue . ' units issued, ' . $remaining_quantity . ' units remaining.';
    }
    
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close statements and connection
if (isset($stmt)) $stmt->close();
if (isset($update_quantity_stmt)) $update_quantity_stmt->close();
if (isset($update_request_stmt)) $update_request_stmt->close();
$conn->close();
?>