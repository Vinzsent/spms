<?php
/**
 * Notification Helper Functions
 * Provides utility functions to create notifications for different system events
 */

// Ensure database connection is available
if (!isset($conn)) {
    require_once __DIR__ . '/db.php';
}

/**
 * Create a notification for a user
 * 
 * @param int $user_id The user ID to notify
 * @param string $type The notification type (request, approved, rejected, issued)
 * @param string $title The notification title
 * @param string $message The notification message
 * @param int|null $related_id Related record ID (optional)
 * @param string|null $related_type Related record type (optional)
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function createNotification($user_id, $type, $title, $message, $related_id = null, $related_type = null, $conn) {
    try {
        $sql = "INSERT INTO notifications (user_id, type, title, message, related_id, related_type) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $user_id, $type, $title, $message, $related_id, $related_type);
        
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for supply request submission
 * 
 * @param int $request_id The supply request ID
 * @param string $department The requesting department
 * @param string $description The request description
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function notifySupplyRequestSubmitted($request_id, $department, $description, $conn) {
    // Get all users with roles that should be notified (Immediate Head, Supply In-charge, etc.)
    $sql = "SELECT id FROM user WHERE user_type IN ('Immediate Head', 'Supply In-charge', 'Purchasing Officer')";
    $result = $conn->query($sql);
    
    $success = true;
    while ($row = $result->fetch_assoc()) {
        $title = "New Supply Request";
        $message = "A new supply request has been submitted by $department: $description";
        
        if (!createNotification($row['id'], 'request', $title, $message, $request_id, 'supply_request', $conn)) {
            $success = false;
        }
    }
    
    return $success;
}

/**
 * Create notification for request approval
 * 
 * @param int $request_id The supply request ID
 * @param string $approved_by The person who approved
 * @param int $requester_id The user who made the request
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function notifyRequestApproved($request_id, $approved_by, $requester_id, $conn) {
    $title = "Request Approved";
    $message = "Your supply request has been approved by $approved_by";
    
    return createNotification($requester_id, 'approved', $title, $message, $request_id, 'supply_request', $conn);
}

/**
 * Create notification for request rejection
 * 
 * @param int $request_id The supply request ID
 * @param string $rejected_by The person who rejected
 * @param int $requester_id The user who made the request
 * @param string $reason The rejection reason
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function notifyRequestRejected($request_id, $rejected_by, $requester_id, $reason = '', $conn) {
    $title = "Request Rejected";
    $message = "Your supply request has been rejected by $rejected_by";
    if ($reason) {
        $message .= ". Reason: $reason";
    }
    
    return createNotification($requester_id, 'rejected', $title, $message, $request_id, 'supply_request', $conn);
}

/**
 * Create notification for item issuance
 * 
 * @param int $transaction_id The transaction ID
 * @param string $issued_by The person who issued
 * @param int $requester_id The user who requested the item
 * @param string $item_description The item description
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function notifyItemIssued($transaction_id, $issued_by, $requester_id, $item_description, $conn) {
    $title = "Item Issued";
    $message = "Your requested item has been issued by $issued_by: $item_description";
    
    return createNotification($requester_id, 'issued', $title, $message, $transaction_id, 'supplier_transaction', $conn);
}

/**
 * Find requester user ID based on supply request department
 * 
 * @param string $department The department from supply request
 * @param mysqli $conn Database connection
 * @return int|null User ID or null if not found
 */
function findRequesterByDepartment($department, $conn) {
    try {
        // First try to find by department field
        $sql = "SELECT id FROM user WHERE department = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }
        
        // If not found by department, try by user_type
        $sql = "SELECT id FROM user WHERE user_type = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Error finding requester by department: " . $e->getMessage());
        return null;
    }
}

/**
 * Create notification for request status update
 * 
 * @param int $request_id The supply request ID
 * @param string $status The new status (noted, checked, verified, approved)
 * @param string $updated_by The person who updated the status
 * @param int $requester_id The user who made the request
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function notifyRequestStatusUpdate($request_id, $status, $updated_by, $requester_id, $conn) {
    $status_display = ucfirst($status);
    $title = "Request Status Updated";
    $message = "Your supply request status has been updated to '$status_display' by $updated_by";
    
    return createNotification($requester_id, 'request', $title, $message, $request_id, 'supply_request', $conn);
}

/**
 * Get notification count for a user
 * 
 * @param int $user_id The user ID
 * @param mysqli $conn Database connection
 * @return int The number of unread notifications
 */
function getUnreadNotificationCount($user_id, $conn) {
    try {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    } catch (Exception $e) {
        error_log("Error getting notification count: " . $e->getMessage());
        return 0;
    }
}
?> 