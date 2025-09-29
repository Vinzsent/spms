<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../pages/property_inventory.php');
    exit();
}

// Collect and sanitize inputs
$inventory_id  = isset($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : 0;
$item_name     = trim($_POST['item_name'] ?? '');
$category      = trim($_POST['category'] ?? '');
$current_stock = isset($_POST['current_stock']) ? (int)$_POST['current_stock'] : 0;
$unit          = trim($_POST['unit'] ?? '');
$reorder_level = isset($_POST['reorder_level']) ? (int)$_POST['reorder_level'] : 0;
$supplier_id   = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
$unit_cost     = isset($_POST['unit_cost']) ? (float)$_POST['unit_cost'] : 0.0;
$location      = trim($_POST['location'] ?? '');
// New optional fields
$description   = trim($_POST['description'] ?? '');
$quantity      = isset($_POST['quantity']) && $_POST['quantity'] !== '' ? (int)$_POST['quantity'] : null;
$receiver      = trim($_POST['receiver'] ?? '');
$status        = trim($_POST['status'] ?? 'Active');
$received_notes= trim($_POST['received_notes'] ?? '');
$brand         = trim($_POST['brand'] ?? '');
$size          = trim($_POST['size'] ?? '');
$color         = trim($_POST['color'] ?? '');
$type          = trim($_POST['type'] ?? '');

// Basic validation
if ($inventory_id <= 0 || $item_name === '' || $unit === '' || $current_stock < 0 || $reorder_level < 0) {
    $_SESSION['error'] = 'Please fill in all required fields with valid values.';
    header('Location: ../pages/property_inventory.php');
    exit();
}

// Fetch previous stock for logging
$prev_stock = null;
$fetch_sql = "SELECT current_stock FROM property_inventory WHERE inventory_id = ?";
if ($fetch_stmt = $conn->prepare($fetch_sql)) {
    $fetch_stmt->bind_param('i', $inventory_id);
    $fetch_stmt->execute();
    $res = $fetch_stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $prev_stock = (int)$row['current_stock'];
    } else {
        $_SESSION['error'] = 'Inventory item not found.';
        header('Location: ../pages/property_inventory.php');
        exit();
    }
    $fetch_stmt->close();
} else {
    $_SESSION['error'] = 'Database error (prepare fetch).';
    header('Location: ../pages/property_inventory.php');
    exit();
}

// Normalize status to allowed values
$allowed_status = ['Active','Inactive','Discontinued'];
if (!in_array($status, $allowed_status, true)) {
    $status = 'Active';
}

// Update the inventory item (including new fields)
$update_sql = "UPDATE property_inventory 
               SET item_name = ?, category = ?, description = ?, current_stock = ?, quantity = ?, unit = ?, unit_cost = ?, reorder_level = ?, supplier_id = ?, location = ?, receiver = ?, status = ?, received_notes = ?, date_updated = NOW(), brand = ?, size = ?, color = ?, type = ?
               WHERE inventory_id = ?";

$stmt = $conn->prepare($update_sql);
if (!$stmt) {
    $_SESSION['error'] = 'Database error (prepare update): ' . $conn->error;
    header('Location: ../pages/property_inventory.php');
    exit();
}

// Bind parameters
// Types: s (item_name)
//        s (category)
//        s (description)
//        i (current_stock)
//        i (quantity nullable -> use i with null handling below)
//        s (unit)
//        d (unit_cost)
//        i (reorder_level)
//        i (supplier_id)
//        s (location)
//        s (receiver)
//        s (status)
//        s (received_notes)
//        i (inventory_id)

// Ensure quantity is null-safe for bind_param by converting to null and using proper type. Since mysqli doesn't support nullable ints directly in bind_param, we'll cast null to null and use i; this sets 0 if null. To truly set NULL, we'll use SET quantity = NULL when $quantity is null via dynamic SQL.

if ($quantity === null) {
    // Rebuild SQL with quantity = NULL
    $update_sql = "UPDATE property_inventory 
                   SET item_name = ?, category = ?, description = ?, current_stock = ?, quantity = NULL, unit = ?, unit_cost = ?, reorder_level = ?, supplier_id = ?, location = ?, receiver = ?, status = ?, received_notes = ?, date_updated = NOW(), brand = ?, size = ?, color = ?, type = ?
                   WHERE inventory_id = ?";
    $stmt->close();
    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        $_SESSION['error'] = 'Database error (prepare update with NULL qty): ' . $conn->error;
        header('Location: ../pages/property_inventory.php');
        exit();
    }
    $stmt->bind_param(
        'sssisdiissssssssi',
        $item_name,
        $category,
        $description,
        $current_stock,
        $unit,
        $unit_cost,
        $reorder_level,
        $supplier_id,
        $location,
        $receiver,
        $status,
        $received_notes,
        $brand,
        $size,
        $color,
        $type,
        $inventory_id
    );
} else {
    $stmt->bind_param(
        'sssiisdiissssssssi',
        $item_name,
        $category,
        $description,
        $current_stock,
        $quantity,
        $unit,
        $unit_cost,
        $reorder_level,
        $supplier_id,
        $location,
        $receiver,
        $status,
        $received_notes,
        $brand,
        $size,
        $color,
        $type,
        $inventory_id
    );
}

if (!$stmt->execute()) {
    $_SESSION['error'] = 'Error updating inventory item: ' . $stmt->error;
    header('Location: ../pages/property_inventory.php');
    exit();
}
$stmt->close();

// Log stock change if modified
if ($prev_stock !== null && $prev_stock !== $current_stock) {
    $user_id = $_SESSION['user']['id'] ?? 1;
    $movement_type = ($current_stock > $prev_stock) ? 'IN' : 'OUT';
    $qty = abs($current_stock - $prev_stock);
    $notes = 'Edited item quantity via Edit Inventory.';

    $log_sql = "INSERT INTO property_stock_logs (inventory_id, movement_type, quantity, previous_stock, new_stock, notes, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($log_stmt = $conn->prepare($log_sql)) {
        $log_stmt->bind_param('isiiisi', $inventory_id, $movement_type, $qty, $prev_stock, $current_stock, $notes, $user_id);
        $log_stmt->execute();
        $log_stmt->close();
    }
}

$_SESSION['message'] = "Inventory item '{$item_name}' has been updated successfully.";
header('Location: ../pages/property_inventory.php');
exit();

