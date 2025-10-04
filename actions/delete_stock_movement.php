<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['log_id'])) {
    $log_id = intval($_POST['log_id']);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get the stock movement record to reverse the stock change
        $get_log_sql = "SELECT sl.*, i.current_stock 
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
        $movement_type = $log_data['movement_type'];
        $quantity = $log_data['quantity'];
        $previous_stock = $log_data['previous_stock'];
        
        // Calculate the stock adjustment needed (reverse the movement)
        $stock_adjustment = ($movement_type === 'IN') ? -$quantity : $quantity;
        $new_current_stock = $log_data['current_stock'] + $stock_adjustment;
        
        // Ensure stock doesn't go negative
        if ($new_current_stock < 0) {
            throw new Exception('Cannot delete movement: would result in negative inventory');
        }
        
        // Delete the stock movement record
        $delete_sql = "DELETE FROM stock_logs WHERE log_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $log_id);
        
        if (!$delete_stmt->execute()) {
            throw new Exception('Failed to delete stock movement record');
        }
        
        // Update inventory stock to reverse the movement
        $update_inventory_sql = "UPDATE inventory 
                                SET current_stock = ?, date_updated = NOW() 
                                WHERE inventory_id = ?";
        $update_inventory_stmt = $conn->prepare($update_inventory_sql);
        $update_inventory_stmt->bind_param("ii", $new_current_stock, $inventory_id);
        
        if (!$update_inventory_stmt->execute()) {
            throw new Exception('Failed to update inventory stock');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Stock movement deleted successfully'
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
        'message' => 'Invalid request'
    ]);
}
?>
