<?php
session_start();
include '../includes/db.php';

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
$valid_actions = ['noted', 'checked', 'verified', 'issued', 'approved'];
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
            
        case 'issued':
            $sql = "UPDATE supply_request SET issued_by = ?, issued_date = NOW() WHERE request_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $action_by, $request_id);
            break;
            
        case 'approved':
            $sql = "UPDATE supply_request SET approved_by = ?, approved_date = NOW() WHERE request_id = ?";
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
?> 