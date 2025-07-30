<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $inventory_id = intval($_POST['inventory_id']);
    $movement_type = trim($_POST['movement_type']);
    $quantity = intval($_POST['quantity']);
    $notes = trim($_POST['notes']);
    
    // Validation
    if ($inventory_id <= 0 || !in_array($movement_type, ['IN', 'OUT']) || $quantity <= 0) {
        $_SESSION['error'] = "Please provide valid movement details.";
        header("Location: ../pages/Inventory.php");
        exit();
    }
    
    // Get current inventory item
    $item_sql = "SELECT item_name, current_stock FROM inventory WHERE inventory_id = ?";
    $item_stmt = $conn->prepare($item_sql);
    $item_stmt->bind_param("i", $inventory_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();
    
    if ($item_result->num_rows == 0) {
        $_SESSION['error'] = "Inventory item not found.";
        header("Location: ../pages/Inventory.php");
        exit();
    }
    
    $item = $item_result->fetch_assoc();
    $previous_stock = $item['current_stock'];
    
    // Calculate new stock
    if ($movement_type == 'IN') {
        $new_stock = $previous_stock + $quantity;
    } else { // OUT
        if ($previous_stock < $quantity) {
            $_SESSION['error'] = "Insufficient stock. Current stock: $previous_stock, Requested: $quantity";
            header("Location: ../pages/Inventory.php");
            exit();
        }
        $new_stock = $previous_stock - $quantity;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update inventory stock
        $update_sql = "UPDATE inventory SET current_stock = ?, last_updated_by = ? WHERE inventory_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $user_id = $_SESSION['user']['id'] ?? 1;
        $update_stmt->bind_param("iii", $new_stock, $user_id, $inventory_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Error updating inventory stock");
        }
        
        // Log the movement
        $log_sql = "INSERT INTO stock_logs (inventory_id, movement_type, quantity, previous_stock, new_stock, notes, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("isiissi", $inventory_id, $movement_type, $quantity, $previous_stock, $new_stock, $notes, $user_id);
        
        if (!$log_stmt->execute()) {
            throw new Exception("Error logging stock movement");
        }
        
        // Commit transaction
        $conn->commit();
        
        $movement_text = $movement_type == 'IN' ? 'added to' : 'removed from';
        $_SESSION['message'] = "$quantity units $movement_text '$item[item_name]' successfully. New stock: $new_stock";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Error processing stock movement: " . $e->getMessage();
    }
    
    $update_stmt->close();
    $log_stmt->close();
    
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: ../pages/Inventory.php");
exit();
?> 