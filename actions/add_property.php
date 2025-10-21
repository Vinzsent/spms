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
    $type = trim($_POST['type']);
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
    $sql = "INSERT INTO property_inventory (item_name, brand, size, color, type, category, description, current_stock, unit, unit_cost, reorder_level, supplier_id, location, created_by, receiver, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $user_id = $_SESSION['user']['id'] ?? 1;
    // Types for values: s,s,s,s,s,s,i,s,d,i,i,s,i,s (14 params)
    $stmt->bind_param("sssssssisdiisiss", $item_name, $brand, $size, $color, $type, $category, $description, $current_stock, $unit, $unit_cost, $reorder_level, $supplier_id, $location, $user_id, $receiver, $status);
    
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