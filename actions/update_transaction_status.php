<?php
session_start();
include '../includes/db.php';
include '../includes/notification_helper.php';

// Set header for JSON response
header('Content-Type: application/json');

// Get form data
$transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

// For supply_request actions
$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$status_action = isset($_POST['status_action']) ? trim($_POST['status_action']) : '';
$action_by = isset($_POST['action_by']) ? trim($_POST['action_by']) : '';
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

// For quantity computation
$quantity_to_issue = isset($_POST['quantity_to_issue']) ? intval($_POST['quantity_to_issue']) : 0;
$remaining_quantity = isset($_POST['remaining_quantity']) ? intval($_POST['remaining_quantity']) : 0;

// If supply_request update (status_action and request_id present)
if ($request_id > 0 && !empty($status_action) && !empty($action_by)) {
    // Validate status action
    $valid_actions = ['noted', 'checked', 'verified', 'issued', 'approved'];
    if (!in_array($status_action, $valid_actions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status action']);
        exit;
    }
    try {
        switch ($status_action) {
            case 'noted':
                $sql = "UPDATE supply_request SET noted_by = ?, noted_date = NOW() WHERE request_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $action_by, $request_id);
                break;
            case 'checked':
                $sql = "UPDATE supply_request SET checked_by = ?, checked_date = NOW() WHERE request_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $action_by, $request_id);
                break;
            case 'verified':
                $sql = "UPDATE supply_request SET verified_by = ?, verified_date = NOW() WHERE request_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $action_by, $request_id);
                break;
            case 'approved':
                $sql = "UPDATE supply_request SET approved_by = ?, approved_date = NOW() WHERE request_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $action_by, $request_id);
                break;
            case 'issued':
                $sql = "UPDATE supply_request SET issued_by = ?, issued_date = NOW() WHERE request_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $action_by, $request_id);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid status action']);
                exit;
        }
        if ($stmt->execute()) {
            $action_display = ucfirst($status_action);
            $message = "Request status updated to '$action_display' by $action_by";
            if (!empty($remarks)) {
                $message .= " with remarks: $remarks";
            }

            // Get supply request details for notification (directly use requester user_id)
            $request_sql = "SELECT user_id, request_description FROM supply_request WHERE request_id = ?";
            $request_stmt = $conn->prepare($request_sql);
            $request_stmt->bind_param("i", $request_id);
            $request_stmt->execute();
            $request_result = $request_stmt->get_result();
            
            if ($request_result && $request_result->num_rows > 0) {
                $request_data = $request_result->fetch_assoc();
                $requester_id = (int)$request_data['user_id'];
                $description = $request_data['request_description'];
                
                if ($requester_id > 0) {
                    // Send notification based on status action
                    if ($status_action === 'approved') {
                        notifyRequestApproved($request_id, $action_by, $requester_id, $conn);
                    } elseif ($status_action === 'issued') {
                        // Notify only the requester
                        notifyItemIssued($request_id, $action_by, $requester_id, $description, $conn);
                    } else {
                        // For other status updates (noted, checked, verified)
                        notifyRequestStatusUpdate($request_id, $status_action, $action_by, $requester_id, $conn);
                    }
                }
            }
            
            $request_stmt->close();

            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error updating issuance status: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
    $conn->close();
    exit;
}

// Otherwise, handle supplier_transaction update as before
if ($transaction_id <= 0 || empty($new_status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID or status']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Update the transaction status
    $sql = "UPDATE supplier_transaction SET status = ? WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $transaction_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update transaction status');
    }

    // If this is an issuance and there's a request_id, update the supply_request table
    if ($new_status === 'Issued' && $request_id > 0) {
        $user_name = $_SESSION['name'] ?? $_SESSION['user']['name'] ?? $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'] ?? 'System';
        $update_request_sql = "UPDATE supply_request SET issued_by = ?, issued_date = NOW() WHERE request_id = ?";
        $update_request_stmt = $conn->prepare($update_request_sql);
        $update_request_stmt->bind_param("si", $user_name, $request_id);
        if (!$update_request_stmt->execute()) {
            throw new Exception('Failed to update supply request status');
        }

        // Send notification for issuance (directly use requester user_id)
        $request_sql = "SELECT user_id, request_description FROM supply_request WHERE request_id = ?";
        $request_stmt = $conn->prepare($request_sql);
        $request_stmt->bind_param("i", $request_id);
        $request_stmt->execute();
        $request_result = $request_stmt->get_result();
        
        if ($request_result && $request_result->num_rows > 0) {
            $request_data = $request_result->fetch_assoc();
            $requester_id = (int)$request_data['user_id'];
            $description = $request_data['request_description'];
            
            if ($requester_id > 0) {
                // Notify only the requester
                notifyItemIssued($transaction_id, $user_name, $requester_id, $description, $conn);
            }
        }
        
        $request_stmt->close();
    }

    // If there's quantity computation, update the quantity, amount, and total
    if ($new_status === 'Issued' && $quantity_to_issue > 0 && $remaining_quantity >= 0) {
        $get_price_sql = "SELECT unit_price FROM supplier_transaction WHERE transaction_id = ?";
        $get_price_stmt = $conn->prepare($get_price_sql);
        $get_price_stmt->bind_param("i", $transaction_id);
        $get_price_stmt->execute();
        $price_result = $get_price_stmt->get_result();
        if ($price_result->num_rows > 0) {
            $price_row = $price_result->fetch_assoc();
            $unit_price = $price_row['unit_price'];
            $new_amount = $remaining_quantity * $unit_price;
            $update_quantity_sql = "UPDATE supplier_transaction SET quantity = ?, amount = ? WHERE transaction_id = ?";
            $update_quantity_stmt = $conn->prepare($update_quantity_sql);
            $update_quantity_stmt->bind_param("idi", $remaining_quantity, $new_amount, $transaction_id);
            if (!$update_quantity_stmt->execute()) {
                throw new Exception('Failed to update quantity and amount');
            }
        }
        $get_price_stmt->close();
    }

    $conn->commit();
    $message = 'Status updated to ' . $new_status;
    if ($quantity_to_issue > 0) {
        $message .= '. Quantity updated: ' . $quantity_to_issue . ' units issued, ' . $remaining_quantity . ' units remaining.';
    }
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
if (isset($stmt)) $stmt->close();
if (isset($update_request_stmt)) $update_request_stmt->close();
if (isset($update_quantity_stmt)) $update_quantity_stmt->close();
$conn->close();
?>