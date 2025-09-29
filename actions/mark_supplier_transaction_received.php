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
            // 1. Check if the transaction exists and is pending
            $check_stmt = $conn->prepare("SELECT * FROM supplier_transaction WHERE procurement_id = ? AND status = 'Pending'");
            $check_stmt->bind_param("i", $procurement_id);
            $check_stmt->execute();
            $transaction = $check_stmt->get_result()->fetch_assoc();
            
            if (!$transaction) {
                throw new Exception("Transaction not found or already processed");
            }

            // 2. Update transaction status to received
            $stmt = $conn->prepare(
                "UPDATE supplier_transaction SET status = 'Received', date_updated = CURRENT_TIMESTAMP WHERE procurement_id = ?"
            );

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("i", $procurement_id);

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            if ($stmt->affected_rows === 0) {
                throw new Exception("No changes were made or transaction not found");
            }
            $stmt->close();
            $check_stmt->close();

            // 3. Insert or update inventory
            // Check if item already exists in inventory (no status filter here)
            $checkStmt = $conn->prepare("SELECT inventory_id, current_stock FROM inventory WHERE item_name = ? AND supplier_id = ? LIMIT 1");
            $checkStmt->bind_param("si", $transaction['item_name'], $transaction['supplier_id']);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing inventory
                $row = $result->fetch_assoc();
                $new_stock = $row['current_stock'] + $transaction['quantity'];
                $updateStmt = $conn->prepare(
                    "UPDATE inventory SET current_stock = ?, unit_cost = ?, last_updated_by = ?, date_updated = CURRENT_TIMESTAMP WHERE inventory_id = ?"
                );
                $updateStmt->bind_param("ddii", $new_stock, $transaction['unit_price'], $user_id, $row['inventory_id']);
                $updateStmt->execute();
                $updateStmt->close();
                $inventory_id = $row['inventory_id'];
            } else {
                // Insert new inventory item (align columns with values)
                $insertStmt = $conn->prepare(
                    "INSERT INTO inventory (item_name, category, description, current_stock, unit, unit_cost, reorder_level, supplier_id, status, created_by, last_updated_by, receiver) VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'Active', ?, ?, ?)"
                );
                $insertStmt->bind_param(
                    "sssisdiiis",
                    $transaction['item_name'],
                    $transaction['category'],
                    $transaction['item_name'],
                    $transaction['quantity'],
                    $transaction['unit'],
                    $transaction['unit_price'],
                    $transaction['supplier_id'],
                    $user_id,
                    $user_id,
                    $transaction['receiver']
                );
                $insertStmt->execute();
                $inventory_id = $conn->insert_id;
                $insertStmt->close();
            }
            $checkStmt->close();

            // 4. Record the inventory movement (remove non-existent status column)
            $movementStmt = $conn->prepare(
                "INSERT INTO stock_logs (inventory_id, movement_type, quantity, previous_stock, new_stock, notes, created_by, receiver) VALUES (?, 'IN', ?, ?, ?, ?, ?, ?)"
            );

            if (!$movementStmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            // Get the current stock for this item
            $currentStock = isset($row['current_stock']) ? $row['current_stock'] : 0;
            $newStock = $currentStock + $transaction['quantity'];
            $notes = "Received from supplier transaction #" . $procurement_id . ". " . $received_notes;

            $movementStmt->bind_param(
                "iiiisis", 
                $inventory_id, 
                $transaction['quantity'],
                $currentStock,
                $newStock,
                $notes, 
                $user_id,
                $transaction['receiver']
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

        header("Location: ../pages/inventory.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../pages/inventory.php");
        exit;
    }
}
?>
