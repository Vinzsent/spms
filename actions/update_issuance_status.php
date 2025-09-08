<?php
session_start();
include '../includes/db.php';
include '../includes/notification_helper.php';

// Set header for JSON response
header('Content-Type: application/json');

// Get form data
$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$status_action = isset($_POST['status_action']) ? trim($_POST['status_action']) : '';
$action_by = isset($_POST['action_by']) ? trim($_POST['action_by']) : '';
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

// Debug logging
error_log("update_issuance_status.php - POST data: " . print_r($_POST, true));
error_log("update_issuance_status.php - request_id: " . $request_id);
error_log("update_issuance_status.php - status_action: " . $status_action);
error_log("update_issuance_status.php - action_by: " . $action_by);

// Validate input
if ($request_id <= 0 || empty($status_action) || empty($action_by)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID, status action, or action by']);
    exit;
}

// Validate status action
$valid_actions = ['noted', 'checked', 'verified', 'approved', 'issued'];
if (!in_array($status_action, $valid_actions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status action']);
    exit;
}

try {
    // Prepare the SQL statement based on the action
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

        // Log the action if remarks are provided
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

        // Only update main status to 'Issued' if the action is specifically 'issued'
        if ($status_action === 'issued') {
            $status_update_sql = "UPDATE supply_request SET status = 'Issued' WHERE request_id = ?";
            $status_update_stmt = $conn->prepare($status_update_sql);
            $status_update_stmt->bind_param("i", $request_id);
            $status_update_stmt->execute();
            $status_update_stmt->close();
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
