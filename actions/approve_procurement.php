<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login first";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $procurement_id = intval($_POST['procurement_id'] ?? 0);
        $received_date = $_POST['received_date'] ?? '';
        $received_notes = $_POST['received_notes'] ?? '';
        $quantity = intval($_POST['quantity'] ?? 0);
        $unit = $_POST['unit'] ?? '';
        $supplier_id = intval($_POST['supplier'] ?? 0);
        $unit_cost = floatval($_POST['price'] ?? 0);
        $item_name = $_POST['item_name'] ?? '';
        $notes = $_POST['notes'] ?? '';

        if ($procurement_id <= 0) {
            throw new Exception("Invalid procurement ID");
        }

        if (empty($received_date)) {
            throw new Exception("Received date is required");
        }

        $user_id = $_SESSION['user']['id'] ?? 1;

        // Start transaction
        $conn->begin_transaction();

        try {
            // 1. Update procurement status to received
            $stmt = $conn->prepare("
                UPDATE supplier_transaction SET 
                    status = 'Received',
                    received_by = ?,
                    date_received = ?,
                    received_notes = ?,
                    last_updated_by = ?,
                    date_updated = CURRENT_TIMESTAMP
                WHERE procurement_id = ?
            ");

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("issii", $user_id, $received_date, $received_notes, $user_id, $procurement_id);

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            if ($stmt->affected_rows === 0) {
                throw new Exception("No changes were made or procurement not found");
            }
            $stmt->close();

            // 2. Insert or update inventory
            // Check if item already exists in inventory
            $checkStmt = $conn->prepare("SELECT inventory_id, current_stock FROM inventory WHERE item_name = ? AND supplier_id = ? LIMIT 1");
            $checkStmt->bind_param("si", $item_name, $supplier_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing inventory
                $row = $result->fetch_assoc();
                $new_stock = $row['current_stock'] + $quantity;
                $updateStmt = $conn->prepare("
                    UPDATE inventory SET 
                        current_stock = ?,
                        unit_cost = ?,
                        last_updated_by = ?,
                        date_updated = CURRENT_TIMESTAMP
                    WHERE inventory_id = ?
                ");
                $updateStmt->bind_param("diii", $new_stock, $unit_cost, $user_id, $row['inventory_id']);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                // Insert new inventory item
                $insertStmt = $conn->prepare("
                    INSERT INTO inventory 
                    (item_name, category, description, current_stock, unit, unit_cost, 
                     reorder_level, supplier_id, status, created_by, last_updated_by)
                    VALUES (?, 'Uncategorized', ?, ?, ?, ?, 0, ?, 'Active', ?, ?)
                ");
                $insertStmt->bind_param(
                    "ssisdiii",
                    $item_name,
                    $notes,
                    $quantity,
                    $unit,
                    $unit_cost,
                    $supplier_id,
                    $user_id,
                    $user_id
                );
                $insertStmt->execute();
                $insertStmt->close();
            }
            $checkStmt->close();

            // 3. Record the inventory movement
            // First, get the correct inventory_id
            $inventory_id = 0;
            if (isset($row['inventory_id'])) {
                $inventory_id = $row['inventory_id'];
            } else {
                // If it's a new item, get the last inserted ID
                $inventory_id = $conn->insert_id;
                
                // If we still don't have an ID, try to get it from the database
                if (empty($inventory_id)) {
                    $getIdStmt = $conn->prepare("SELECT inventory_id FROM inventory WHERE item_name = ? AND supplier_id = ? ORDER BY inventory_id DESC LIMIT 1");
                    $getIdStmt->bind_param("si", $item_name, $supplier_id);
                    $getIdStmt->execute();
                    $result = $getIdStmt->get_result();
                    if ($result->num_rows > 0) {
                        $inventory_id = $result->fetch_assoc()['inventory_id'];
                    }
                    $getIdStmt->close();
                }
            }

            if (empty($inventory_id)) {
                throw new Exception("Could not determine inventory ID for the item");
            }

            // Now prepare the stock logs statement
            $movementStmt = $conn->prepare("
                INSERT INTO stock_logs 
                (inventory_id, movement_type, quantity, previous_stock, new_stock, notes, created_by)
                VALUES (?, 'IN', ?, ?, ?, ?, ?)
            ");

            if (!$movementStmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            // Get the current stock for this item
            $currentStock = 0;
            if (isset($row['current_stock'])) {
                $currentStock = $row['current_stock'];
            } else if (isset($new_stock)) {
                $currentStock = $new_stock - $quantity;
            }
            
            $newStock = $currentStock + $quantity;

            $movementStmt->bind_param(
                "iiiisi", 
                $inventory_id, 
                $quantity,
                $currentStock,
                $newStock,
                $received_notes, 
                $user_id
            );
            
            if (!$movementStmt->execute()) {
                throw new Exception("Failed to record inventory movement: " . $movementStmt->error);
            }
            $movementStmt->close();

            // Commit transaction
            $conn->commit();

            $_SESSION['message'] = "Item marked as received and inventory updated successfully";

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }

        $conn->close();

        header("Location: ../pages/procurement.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../pages/procurement.php");
        exit;
    }
}
