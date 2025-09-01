<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $item_name = trim($_POST['item_name']);
    $category = trim($_POST['category']);
    $current_stock = intval($_POST['current_stock']);
    $unit = trim($_POST['unit']);
    $reorder_level = intval($_POST['reorder_level']);
    $supplier_id = intval($_POST['supplier_id']);
    $unit_cost = floatval($_POST['unit_cost']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location'] ?? '');
    
    // Validation
    if (empty($item_name) || empty($category) || empty($unit) || $current_stock < 0 || $reorder_level < 0) {
        $_SESSION['error'] = "Please fill in all required fields with valid values.";
        header("Location: ../pages/Inventory.php");
        exit();
    }
    
    // Check if item already exists
    $check_sql = "SELECT inventory_id FROM inventory WHERE item_name = ? AND category = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $item_name, $category);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "An item with this name and category already exists.";
        header("Location: ../pages/Inventory.php");
        exit();
    }
    
    // Insert new inventory item
    $sql = "INSERT INTO inventory (item_name, category, description, current_stock, unit, unit_cost, reorder_level, supplier_id, location, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $user_id = $_SESSION['user']['id'] ?? 1;
    $stmt->bind_param("sssisidssi", $item_name, $category, $description, $current_stock, $unit, $unit_cost, $reorder_level, $supplier_id, $location, $user_id);
    
    if ($stmt->execute()) {
        $inventory_id = $conn->insert_id;
        
        // Log initial stock if greater than 0
        if ($current_stock > 0) {
            $log_sql = "INSERT INTO stock_logs (inventory_id, movement_type, quantity, previous_stock, new_stock, notes, created_by) 
                        VALUES (?, 'IN', ?, 0, ?, 'Initial stock entry', ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iiii", $inventory_id, $current_stock, $current_stock, $user_id);
            $log_stmt->execute();
        }
        
        $_SESSION['message'] = "Inventory item '$item_name' has been added successfully.";
    } else {
        $_SESSION['error'] = "Error adding inventory item: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: ../pages/Inventory.php");
exit();
?> 