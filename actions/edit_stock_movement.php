<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $log_id = intval($_POST['log_id']);
    $movement_type = $_POST['movement_type'];
    $quantity = intval($_POST['quantity']);
    $receiver = trim($_POST['receiver']);
    $notes = trim($_POST['notes']);
    $date_created = $_POST['date_created'];
    
    // Validate required fields
    if (empty($log_id) || empty($movement_type) || empty($quantity) || empty($date_created)) {
        echo json_encode([
            'success' => false,
            'message' => 'All required fields must be filled'
        ]);
        exit;
    }
    
    // Validate movement type
    if (!in_array($movement_type, ['IN', 'OUT'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid movement type'
        ]);
        exit;
    }
    
    // Validate quantity
    if ($quantity <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Quantity must be greater than 0'
        ]);
        exit;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get the current stock movement record
        $get_log_sql = "SELECT sl.*, i.current_stock, i.item_name 
                       FROM stock_logs sl 
                       JOIN inventory i ON sl.inventory_id = i.inventory_id 
                       WHERE sl.log_id = ?";
        $get_stmt = $conn->prepare($get_log_sql);
        $get_stmt->bind_param("i", $log_id);
        $get_stmt->execute();
        $log_result = $get_stmt->get_result();
        
        if ($log_result->num_rows === 0) {
            throw new Exception('Stock movement record not found');
        }
        
        $log_data = $log_result->fetch_assoc();
        $inventory_id = $log_data['inventory_id'];
        $old_movement_type = $log_data['movement_type'];
        $old_quantity = $log_data['quantity'];
        $old_previous_stock = $log_data['previous_stock'];
        $old_new_stock = $log_data['new_stock'];
        
        // Calculate the difference in stock change
        $old_stock_change = ($old_movement_type === 'IN') ? $old_quantity : -$old_quantity;
        $new_stock_change = ($movement_type === 'IN') ? $quantity : -$quantity;
        $stock_difference = $new_stock_change - $old_stock_change;
        
        // Calculate new values
        $new_previous_stock = $old_previous_stock;
        $new_new_stock = $old_new_stock + $stock_difference;
        
        // Ensure new stock doesn't go negative
        if ($new_new_stock < 0) {
            throw new Exception('Stock adjustment would result in negative inventory');
        }
        
        // Update the stock movement record
        $update_log_sql = "UPDATE stock_logs 
                          SET movement_type = ?, quantity = ?, previous_stock = ?, 
                              new_stock = ?, receiver = ?, notes = ?, date_created = ? 
                          WHERE log_id = ?";
        $update_stmt = $conn->prepare($update_log_sql);
        $update_stmt->bind_param("siiisssi", $movement_type, $quantity, $new_previous_stock, 
                                $new_new_stock, $receiver, $notes, $date_created, $log_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception('Failed to update stock movement record');
        }
        
        // Update the inventory stock
        $update_inventory_sql = "UPDATE inventory 
                                SET current_stock = ?, date_updated = NOW() 
                                WHERE inventory_id = ?";
        $update_inventory_stmt = $conn->prepare($update_inventory_sql);
        $update_inventory_stmt->bind_param("ii", $new_new_stock, $inventory_id);
        
        if (!$update_inventory_stmt->execute()) {
            throw new Exception('Failed to update inventory stock');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Stock movement updated successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
