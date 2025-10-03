<?php
session_start();
include '../includes/db.php';

$isAjax = isset($_POST['ajax']) && (string)$_POST['ajax'] !== '';
if ($isAjax) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $inventory_id = intval($_POST['inventory_id'] ?? 0);
    $movement_type = trim($_POST['movement_type'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $request_id = intval($_POST['request_id'] ?? 0);

    // Validation
    if ($inventory_id <= 0 || !in_array($movement_type, ['IN', 'OUT']) || $quantity <= 0) {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Please provide valid movement details.']);
            exit;
        }
        $_SESSION['error'] = "Please provide valid movement details.";
        header("Location: ../pages/property_inventory.php");
        exit();
    }

    // Get current inventory item
    $item_sql = "SELECT item_name, current_stock, receiver FROM property_inventory WHERE inventory_id = ?";
    $item_stmt = $conn->prepare($item_sql);
    $item_stmt->bind_param("i", $inventory_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();

    if ($item_result->num_rows == 0) {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Inventory item not found.']);
            exit;
        }
        $_SESSION['error'] = "Inventory item not found.";
        header("Location: ../pages/property_inventory.php");
        exit();
    }

    $item = $item_result->fetch_assoc();
    $previous_stock = (int)$item['current_stock'];

    // Calculate new stock
    if ($movement_type == 'IN') {
        $new_stock = $previous_stock + $quantity;
    } else { // OUT
        if ($previous_stock < $quantity) {
            $msg = "Insufficient stock. Current stock: $previous_stock, Requested: $quantity";
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
            header("Location: ../pages/property_inventory.php");
            exit();
        }
        $new_stock = $previous_stock - $quantity;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update inventory stock
        $update_sql = "UPDATE property_inventory SET current_stock = ?, last_updated_by = ? WHERE inventory_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $user_id = $_SESSION['user']['id'] ?? ($_SESSION['user_id'] ?? 1);
        $update_stmt->bind_param("iii", $new_stock, $user_id, $inventory_id);

        if (!$update_stmt->execute()) {
            throw new Exception("Error updating inventory stock");
        }

        // Log the movement
        $log_sql = "INSERT INTO property_stock_logs (inventory_id, movement_type, quantity, previous_stock, new_stock, notes, request_id, created_by, receiver) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("isiissiis", $inventory_id, $movement_type, $quantity, $previous_stock, $new_stock, $notes, $request_id, $user_id, $item['receiver']);

        if (!$log_stmt->execute()) {
            throw new Exception("Error logging stock movement");
        }

        // Commit transaction
        $conn->commit();

        $movement_text = $movement_type == 'IN' ? 'added to' : 'removed from';

        if ($isAjax) {
            echo json_encode([
                'success' => true,
                'message' => "$quantity units $movement_text '{$item['item_name']}' successfully.",
                'new_stock' => $new_stock
            ]);
            exit;
        }

        $_SESSION['message'] = "$quantity units $movement_text '$item[item_name]' successfully. New stock: $new_stock";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Error processing stock movement: ' . $e->getMessage()]);
            exit;
        }
        $_SESSION['error'] = "Error processing stock movement: " . $e->getMessage();
    }

    if (isset($update_stmt)) $update_stmt->close();
    if (isset($log_stmt)) $log_stmt->close();

} else {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }
    $_SESSION['error'] = "Invalid request method.";
}

if (!$isAjax) {
    header("Location: ../pages/property_inventory.php");
    exit();
}
?>