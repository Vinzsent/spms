<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $log_id = intval($_POST['log_id']);
    $inventory_id = intval($_POST['inventory_id']);
    $movement_type = $_POST['movement_type'];
    $quantity = intval($_POST['quantity']);
    $receiver = trim($_POST['receiver']);
    $notes = trim($_POST['notes']);
    $previous_stock = intval($_POST['previous_stock']);
    
    // Validate required fields
    if (empty($log_id) || empty($movement_type) || empty($quantity)) {
        $_SESSION['error'] = 'All required fields must be filled';
        header('Location: ../pages/property_inventory.php');
        exit;
    }
    
    // Validate movement type
    if (!in_array($movement_type, ['IN', 'OUT'])) {
        $_SESSION['error'] = 'Invalid movement type';
        header('Location: ../pages/property_inventory.php');
        exit;
    }
    
    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['error'] = 'Quantity must be greater than 0';
        header('Location: ../pages/property_inventory.php');
        exit;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get the current stock movement record
        $get_log_sql = "SELECT sl.*, pi.current_stock, pi.item_name 
                       FROM property_stock_logs sl 
                       JOIN property_inventory pi ON sl.inventory_id = pi.inventory_id 
                       WHERE sl.log_id = ?";
        $get_stmt = $conn->prepare($get_log_sql);
        $get_stmt->bind_param("i", $log_id);
        $get_stmt->execute();
        $log_result = $get_stmt->get_result();
        
        if ($log_result->num_rows === 0) {
            throw new Exception('Stock movement record not found');
        }
        
        $log_data = $log_result->fetch_assoc();
        $old_movement_type = $log_data['movement_type'];
        $old_quantity = $log_data['quantity'];
        $current_inventory_stock = $log_data['current_stock'];
        
        // Calculate the stock adjustment needed
        // First, reverse the old movement
        if ($old_movement_type === 'IN') {
            $current_inventory_stock -= $old_quantity;
        } else {
            $current_inventory_stock += $old_quantity;
        }
        
        // Then apply the new movement
        if ($movement_type === 'IN') {
            $new_stock = $current_inventory_stock + $quantity;
        } else {
            $new_stock = $current_inventory_stock - $quantity;
        }
        
        // Ensure new stock doesn't go negative
        if ($new_stock < 0) {
            throw new Exception('Stock adjustment would result in negative inventory');
        }
        
        // Update the stock movement record
        $update_log_sql = "UPDATE property_stock_logs 
                          SET movement_type = ?, quantity = ?, previous_stock = ?, 
                              new_stock = ?, receiver = ?, notes = ? 
                          WHERE log_id = ?";
        $update_stmt = $conn->prepare($update_log_sql);
        $update_stmt->bind_param("siiissi", $movement_type, $quantity, $current_inventory_stock, 
                                $new_stock, $receiver, $notes, $log_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to update stock movement record');
        }
        
        // Update the inventory stock
        $update_inventory_sql = "UPDATE property_inventory 
                                SET current_stock = ?, date_updated = NOW() 
                                WHERE inventory_id = ?";
        $update_inventory_stmt = $conn->prepare($update_inventory_sql);
        $update_inventory_stmt->bind_param("ii", $new_stock, $inventory_id);
        
        if (!$update_inventory_stmt->execute()) {
            throw new Exception('Failed to update inventory stock');
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = 'Stock movement updated successfully';
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    
    $conn->close();
    header('Location: ../pages/property_inventory.php');
    exit;
} else {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: ../pages/property_inventory.php');
    exit;
}
?>
