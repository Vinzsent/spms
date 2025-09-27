<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $item_name = trim($_POST['item_name']);
    $status = trim($_POST['status']);
    $brand = trim($_POST['brand']);
    $size = trim($_POST['size']);
    $color = trim($_POST['color']);
    $category = trim($_POST['category']);
    $current_stock = intval($_POST['current_stock']);
    $unit = trim($_POST['unit']);
    $reorder_level = intval($_POST['reorder_level']);
    $supplier_id = intval($_POST['supplier_id']);
    $unit_cost = floatval($_POST['unit_cost']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $receiver = trim($_POST['receiver'] ?? '');
    $procurement_id = intval($_POST['procurement_id'] ?? 0);
    
    // Validation
    if (empty($item_name) || empty($category) || empty($unit) || $current_stock < 0 || $reorder_level < 0) {
        $_SESSION['error'] = "Please fill in all required fields with valid values.";
        header("Location: ../pages/property_inventory.php");
        exit();
    }
    
    // Check if item already exists in property inventory
    $check_sql = "SELECT inventory_id FROM property_inventory WHERE item_name = ? AND category = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $item_name, $category);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "An item with this name and category already exists.";
        header("Location: ../pages/property_inventory.php");
        exit();
    }
    
    // Update supplier transaction status FIRST if procurement_id is provided
    if ($procurement_id > 0) {
        $update_sql = "UPDATE supplier_transaction SET status = 'Added' WHERE procurement_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $procurement_id);
        
        if ($update_stmt->execute()) {
            // Check if any rows were affected
            if ($update_stmt->affected_rows > 0) {
                error_log("Status updated successfully for procurement_id: " . $procurement_id);
            } else {
                error_log("No rows updated for procurement_id: " . $procurement_id);
                $_SESSION['error'] = "Failed to update transaction status. Transaction may not exist.";
                header("Location: ../pages/property_inventory.php");
                exit();
            }
        } else {
            error_log("Error updating status: " . $update_stmt->error);
            $_SESSION['error'] = "Error updating transaction status: " . $update_stmt->error;
            header("Location: ../pages/property_inventory.php");
            exit();
        }
        $update_stmt->close();
    }
    
    // Insert new inventory item AFTER status update
    $sql = "INSERT INTO property_inventory (item_name, brand, size, color, category, description, current_stock, unit, unit_cost, reorder_level, supplier_id, location, created_by, receiver, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $user_id = $_SESSION['user']['id'] ?? 1;
    // Types for values: s,s,s,s,s,s,i,s,d,i,i,s,i,s (14 params)
    $stmt->bind_param("ssssssisdiisiss", $item_name, $brand, $size, $color, $category, $description, $current_stock, $unit, $unit_cost, $reorder_level, $supplier_id, $location, $user_id, $receiver, $status);
    
    if ($stmt->execute()) {
        $inventory_id = $conn->insert_id;
        
        // Log initial stock if greater than 0
        if ($current_stock > 0) {
            $log_sql = "INSERT INTO property_stock_logs (inventory_id, movement_type, quantity, previous_stock, new_stock, notes, created_by, receiver) 
                        VALUES (?, 'IN', ?, 0, ?, 'Initial stock entry', ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iiiis", $inventory_id, $current_stock, $current_stock, $user_id, $receiver);
            $log_stmt->execute();
        }
        
        $_SESSION['message'] = "Inventory item '$item_name' has been added successfully.";
    } else {
        // If inventory insertion fails after status update, we should rollback the status
        if ($procurement_id > 0) {
            $rollback_sql = "UPDATE supplier_transaction SET status = 'Received' WHERE procurement_id = ?";
            $rollback_stmt = $conn->prepare($rollback_sql);
            $rollback_stmt->bind_param("i", $procurement_id);
            $rollback_stmt->execute();
            $rollback_stmt->close();
        }
        $_SESSION['error'] = "Error adding inventory item: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: ../pages/property_inventory.php");

exit();
?>