<?php
include '../includes/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the procurement ID and new status
$procurement_id = trim($_POST['procurement_id'] ?? '');
$new_status = trim($_POST['new_status'] ?? '');

// Validate required fields
if (empty($procurement_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Procurement ID is required']);
    exit;
}

if (empty($new_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New status is required']);
    exit;
}

// Validate that new status is valid
$allowed_statuses = ['Pending', 'Received', 'Approved', 'In Progress', 'Completed'];
if (!in_array($new_status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

try {
    // First, check if the procurement record exists and get current status
    $check_stmt = $conn->prepare("SELECT status, item_name FROM supplier_transaction WHERE id = ?");
    if (!$check_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $check_stmt->bind_param("i", $conn->real_escape_string($procurement_id));
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Procurement record not found");
    }

    $record = $result->fetch_assoc();
    $current_status = $record['status'];
    $item_name = $record['item_name'];

    // Check if the current status is 'Pending' (only allow changes from Pending)
    if (strtolower(trim($current_status)) !== 'pending') {
        throw new Exception("Can only change status from 'Pending'. Current status is: " . $current_status);
    }

    // Update the status
    $update_stmt = $conn->prepare("UPDATE supplier_transaction SET status = ?, updated_at = NOW() WHERE id = ?");
    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $update_stmt->bind_param("si", $new_status, $procurement_id);

    if (!$update_stmt->execute()) {
        throw new Exception("Execute failed: " . $update_stmt->error);
    }

    if ($update_stmt->affected_rows === 0) {
        throw new Exception("No records were updated");
    }

    // Log the status change
    $user_id = $_SESSION['user']['id'] ?? 1;
    $user_name = $_SESSION['user']['name'] ?? 'Unknown User';
    
    $log_stmt = $conn->prepare("
        INSERT INTO procurement_status_log (
            procurement_id, 
            old_status, 
            new_status, 
            changed_by, 
            changed_at, 
            notes
        ) VALUES (?, ?, ?, ?, NOW(), ?)
    ");

    if ($log_stmt) {
        $notes = "Status changed from '{$current_status}' to '{$new_status}' by {$user_name}";
        $log_stmt->bind_param("issis", $procurement_id, $current_status, $new_status, $user_id, $notes);
        $log_stmt->execute();
    }

    // Success response
    echo json_encode([
        'success' => true, 
        'message' => "Status successfully changed from '{$current_status}' to '{$new_status}' for item: {$item_name}",
        'old_status' => $current_status,
        'new_status' => $new_status,
        'item_name' => $item_name
    ]);

} catch (Exception $e) {
    error_log("Error changing procurement status: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error changing status: ' . $e->getMessage()
    ]);
}

// Close statements
if (isset($check_stmt)) $check_stmt->close();
if (isset($update_stmt)) $update_stmt->close();
if (isset($log_stmt)) $log_stmt->close();

$conn->close();
?>
