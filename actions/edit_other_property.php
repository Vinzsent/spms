<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../pages/other_property_logs.php');
    exit();
}

// Collect and sanitize inputs
$id                   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$item_number          = trim($_POST['item_number'] ?? '');
$item_name            = trim($_POST['item_name'] ?? ''); // Independent handling
$category             = trim($_POST['category'] ?? '');
$brand                = trim($_POST['brand'] ?? '');
$model                = trim($_POST['model'] ?? '');
$type                 = trim($_POST['type'] ?? '');
$serial_number        = trim($_POST['serial_number'] ?? '');
$location             = trim($_POST['location'] ?? '');
$status               = trim($_POST['status'] ?? 'Working');
$purchase_date        = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
$warranty_expiry      = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
$last_service_date    = !empty($_POST['last_service']) ? $_POST['last_service'] : null;
$supplier_id          = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
$notes                = trim($_POST['notes'] ?? '');
$purchase_price       = !empty($_POST['purchase_price']) ? floatval($_POST['purchase_price']) : null;
$depreciated_value    = !empty($_POST['depreciated_value']) ? floatval($_POST['depreciated_value']) : null;
$campus               = !empty($_POST['campus']) ? trim($_POST['campus']) : null;

// Basic validation
if ($id <= 0) {
    $_SESSION['error'] = 'Invalid property ID.';
    header('Location: ../pages/other_property_logs.php');
    exit();
}

if (empty($item_name)) {
    $_SESSION['error'] = 'Item Name is a required field.';
    header("Location: ../pages/other_property_logs.php");
    exit();
}

// Check if property exists
$check_sql = "SELECT id FROM other_property_logs WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result && $check_result->num_rows > 0) {
    $check_stmt->close();
} else {
    $_SESSION['error'] = 'Property not found.';
    $check_stmt->close();
    header('Location: ../pages/other_property_logs.php');
    exit();
}

// Update the property record
$update_sql = "UPDATE other_property_logs 
               SET item_number = ?, 
                   item_name = ?,
                   category = ?,
                   brand = ?, 
                   model = ?, 
                   type = ?, 
                   serial_number = ?, 
                   location = ?, 
                   status = ?, 
                   purchase_date = ?, 
                   warranty_expiry = ?,
                   last_service_date = ?,
                   supplier_id = ?,
                   notes = ?,
                   purchase_price = ?,
                   depreciated_value = ?,
                   campus = ?,
                   date_updated = NOW()
               WHERE id = ?";

$stmt = $conn->prepare($update_sql);
if (!$stmt) {
    $_SESSION['error'] = 'Database error (prepare update): ' . $conn->error;
    header('Location: ../pages/other_property_logs.php');
    exit();
}

// Bind parameters - 18 parameters
$types = 'ssssssssssssisddsi';
$params = [
    $item_number,
    $item_name,
    $category,
    $brand,
    $model,
    $type,
    $serial_number,
    $location,
    $status,
    $purchase_date,
    $warranty_expiry,
    $last_service_date,
    $supplier_id,
    $notes,
    $purchase_price,
    $depreciated_value,
    $campus,
    $id
];

$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    $_SESSION['error'] = 'Error updating property: ' . $stmt->error;
    header('Location: ../pages/other_property_logs.php');
    exit();
}

$stmt->close();

$_SESSION['message'] = "Property '{$item_name}' has been updated successfully.";
header('Location: ../pages/other_property_logs.php');
exit();
