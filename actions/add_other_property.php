<?php
session_start();
include '../includes/db.php';

// Get user ID from session
$user_id = $_SESSION['user']['id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $item_number = trim($_POST['item_number'] ?? '');
    $item_name = trim($_POST['item_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = trim($_POST['status'] ?? 'Working');
    $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
    $warranty_expiry = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
    $last_service_date = !empty($_POST['last_service']) ? $_POST['last_service'] : null;
    $notes = trim($_POST['notes'] ?? '');
    $purchase_price = !empty($_POST['purchase_price']) ? floatval($_POST['purchase_price']) : 0.00;
    $depreciated_value = !empty($_POST['depreciated_value']) ? floatval($_POST['depreciated_value']) : 0.00;
    $receiver = trim($_POST['receiver'] ?? 'Property Custodian');
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $campus = !empty($_POST['campus']) ? trim($_POST['campus']) : null;

    // Insert new property into database
    $sql = "INSERT INTO other_property_logs ( 
        item_number,
        item_name,
        category,
        brand, 
        model, 
        type, 
        serial_number, 
        location, 
        status, 
        purchase_date, 
        warranty_expiry, 
        last_service_date,
        notes,
        purchase_price, 
        depreciated_value, 
        receiver, 
        supplier_id,
        created_by, 
        date_created,
        campus
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../pages/other_property_logs.php");
        exit();
    }

    // Bind parameters - 19 parameters
    $stmt->bind_param(
        "sssssssssssssddsiis",
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
        $notes,
        $purchase_price,
        $depreciated_value,
        $receiver,
        $supplier_id,
        $user_id,
        $campus
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Property unit '$item_number' has been added successfully.";
    } else {
        $_SESSION['error'] = "Error adding property: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: ../pages/other_property_logs.php");
    exit();
} else {
    // If not POST request, redirect back
    header("Location: ../pages/other_property_logs.php");
    exit();
}
