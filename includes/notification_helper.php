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
 * Create notification for property request submission
 * Uses the tagging column to determine routing and labeling (consumables/nonconsumables)
 * and sets related_type to 'property_request'.
 *
 * @param int $request_id The property_request ID
 * @param string $department The requesting department/unit
 * @param string $description The request description
 * @param string $tagging Tagging value (e.g., 'consumables' or 'nonconsumables')
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function notifyPropertyRequestSubmitted($request_id, $department, $description, $tagging, $conn) {
    // Normalize tagging
    $raw_tag = strtolower(trim($tagging));
    $is_consumables = ($raw_tag === 'consumables' || $raw_tag === 'consumambles');
    $is_property = ($raw_tag === 'property');
    $is_nonconsumables = ($raw_tag === 'nonconsumables' || $raw_tag === 'non-consumables');

    // Construct label for clarity
    $type_label = $is_consumables ? 'Consumables' : ($is_property ? 'Property' : ($is_nonconsumables ? 'Non-Consumables' : ucfirst($raw_tag ?: 'Property')));

    $title = "New Property Request - {$type_label}";
    $message = "A new {$type_label} property request has been submitted by $department: $description";

    // Roles to notify
    $roles = [
        'Immediate Head',
        'Purchasing Officer',
        'VP for Finance \\u0026 Administration',
        'VP for Academic Affairs',
        'Admistrative Officer'
    ];

    // Conditional roles based on tagging
    if ($is_consumables) {
        $roles[] = 'Supply In-charge';
    } elseif ($is_property || $is_nonconsumables) {
        // Notify Property Custodian when tagging is explicitly 'property' or non-consumables
        $roles[] = 'Property Custodian';
    } else {
        // Unknown tagging: default to Property Custodian
        $roles[] = 'Property Custodian';
    }

    $success = true;
    try {
        $role_sql = "SELECT id FROM user WHERE user_type = ?";
        $stmt = $conn->prepare($role_sql);
        foreach ($roles as $role) {
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                if (!createNotification($row['id'], 'request', $title, $message, $request_id, 'property_request', $conn)) {
                    $success = false;
                }
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error notifying property request roles: " . $e->getMessage());
        $success = false;
    }

    return $success;
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
        
        $result = $stmt->execute();
        
        // Debug logging
        if ($result) {
            error_log("Notification created successfully for user $user_id: $title");
        } else {
            error_log("Failed to create notification for user $user_id: " . $stmt->error);
        }
        
        return $result;
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
function notifySupplyRequestSubmitted($request_id, $department, $description, $request_type, $conn) {
    // Normalize and label request type (handle possible misspelling "consumambles")
    $raw_type = strtolower(trim($request_type));
    $is_consumables = ($raw_type === 'consumables' || $raw_type === 'consumambles');
    $is_property = ($raw_type === 'property');
    $type_label = $is_consumables ? 'Consumables' : ($is_property ? 'Property' : 'Supply');

    // Title and message include the specific request type for clarity
    $title = "New {$type_label} Request";
    $message = "A new {$type_label} request has been submitted by $department: $description";

    // Base roles that should always be notified (excluding Supply In-charge and Property Custodian which are conditional)
    $base_roles = [
        'Immediate Head',
        'Purchasing Officer',
        'VP for Finance \u0026 Administration',
        'VP for Academic Affairs',
        'Admistrative Officer'
    ];

    // Determine conditional role based on request type
    if ($is_consumables) {
        // Notify Supply In-charge only for consumables
        $base_roles[] = 'Supply In-charge';
    } elseif ($is_property) {
        // Notify Property Custodian only for property
        $base_roles[] = 'Property Custodian';
    }

    $success = true;

    // Notify all users matching the selected roles
    try {
        $role_sql = "SELECT id FROM user WHERE user_type = ?";
        $stmt = $conn->prepare($role_sql);
        foreach ($base_roles as $role) {
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                if (!createNotification($row['id'], 'request', $title, $message, $request_id, 'supply_request', $conn)) {
                    $success = false;
                }
            }
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error notifying roles: " . $e->getMessage());
        $success = false;
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
    $title = "🎉 Request Approved!";
    $message = "Great news! Your supply request has been approved by $approved_by. Your items will be processed for issuance soon.";
    
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
    $title = "📦 Item Issued!";
    $message = "Your requested item has been issued by $issued_by: $item_description. You can now collect your items from the supply office.";
    
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
        // Map department names to user types for better matching
        $department_mapping = [
            'Faculty' => 'Faculty',
            'Staff' => 'Staff',
            'Admin' => 'Admin',
            'Immediate Head' => 'Immediate Head',
            'Supply In-charge' => 'Supply In-charge',
            'Purchasing Officer' => 'Purchasing Officer',
            'School President' => 'School President',
            'VP for Finance & Administration' => 'VP for Finance & Administration',
            'VP for Academic Affairs' => 'VP for Academic Affairs',
            'Admistrative Officer' => 'Admistrative Officer',
            'Property Custodian' => 'Property Custodian'
        ];
        
        // Try to find by exact department match first
        $sql = "SELECT id FROM user WHERE user_type = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }
        
        // Try mapped department name
        if (isset($department_mapping[$department])) {
            $mapped_type = $department_mapping[$department];
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $mapped_type);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['id'];
            }
        }
        
        // If still not found, try to find any user with similar user_type
        $sql = "SELECT id FROM user WHERE user_type LIKE ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $search_term = "%" . $department . "%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }
        
        // Default: return the first user (fallback)
        $sql = "SELECT id FROM user LIMIT 1";
        $result = $conn->query($sql);
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
    $status_icons = [
        'noted' => '📝',
        'checked' => '✅',
        'verified' => '🔍',
        'approved' => '🎉',
        'issued' => '📦'
    ];
    
    $icon = $status_icons[$status] ?? '📋';
    $title = "$icon Request Status Updated";
    $message = "Your supply request has been $status_display by $updated_by. The approval process is progressing.";
    
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

/**
 * Notify all Staff and Faculty users when an issuance has been made
 *
 * @param int $request_id The supply request ID related to the issuance
 * @param string $issued_by Name of the person who issued the items
 * @param string $description Description of the issued items/request
 * @param mysqli $conn Database connection
 * @return bool Success status (true if all inserts attempted; false if any failed)
 */
function notifyStaffAndFacultyForIssuance($request_id, $issued_by, $description, $conn) {
    $title = "📦 Items Issued";
    $message = "An issuance has been completed by $issued_by: $description you can now get the item to the supply officer";

    $success = true;
    try {
        $sql = "SELECT id FROM user WHERE user_type IN ('Staff','Faculty')";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if (!createNotification($row['id'], 'issued', $title, $message, $request_id, 'supply_request', $conn)) {
                    $success = false;
                }
            }
        } else {
            error_log("notifyStaffAndFacultyForIssuance query error: " . $conn->error);
            $success = false;
        }
    } catch (Exception $e) {
        error_log("Error notifying staff and faculty for issuance: " . $e->getMessage());
        $success = false;
    }

    return $success;
}

?>
