<?php
session_start();
include '../includes/db.php';

  // Get user ID from session
    $user_id = $_SESSION['user']['id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $item_name = trim($_POST['item_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = trim($_POST['status'] ?? 'Working');
    $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
    $warranty_expiry = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
    $last_service_date = !empty($_POST['last_service']) ? $_POST['last_service'] : null;
    $maintenance_schedule = trim($_POST['maintenance_schedule'] ?? '');
    $installation_date = !empty($_POST['installation_date']) ? $_POST['installation_date'] : null;
    $energy_efficiency_rating = trim($_POST['energy_efficient'] ?? '');
    $power_consumption = !empty($_POST['power_consumption']) ? floatval($_POST['power_consumption']) : null;
    $notes = trim($_POST['notes'] ?? '');
    $purchase_price = !empty($_POST['purchase_price']) ? floatval($_POST['purchase_price']) : 0.00;
    $depreciated_value = !empty($_POST['depreciated_value']) ? floatval($_POST['depreciated_value']) : 0.00;
    $receiver = trim($_POST['receiver'] ?? 'Property Custodian');
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    

    // Insert new aircon into database
    $sql = "INSERT INTO aircons ( 
        item_number,
        category,
        brand, 
        model, 
        type, 
        capacity, 
        serial_number, 
        location, 
        status, 
        purchase_date, 
        warranty_expiry, 
        last_service_date, 
        maintenance_schedule, 
        installation_date, 
        energy_efficiency_rating, 
        power_consumption, 
        notes,
        purchase_price, 
        depreciated_value, 
        receiver, 
        supplier_id,
        created_by, 
        date_created
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../pages/aircon_list.php");
        exit();
    }
    
    // Bind parameters - 22 parameters total    
    // Types: s=string, d=double, i=integer
    $stmt->bind_param(
        "sssssssssssssssdsddsii",
        $item_name,
        $category,
        $brand,
        $model,
        $type,
        $capacity,
        $serial_number,
        $location,
        $status,
        $purchase_date,
        $warranty_expiry,
        $last_service_date,
        $maintenance_schedule,
        $installation_date,
        $energy_efficiency_rating,
        $power_consumption,
        $notes,
        $purchase_price,
        $depreciated_value,
        $receiver,
        $supplier_id,
        $user_id
    );
    
    if ($stmt->execute()) {
        $aircon_id = $conn->insert_id;
        $_SESSION['message'] = "Aircon unit '$model' ($brand $model) has been added successfully.";
        
    } else {
        $_SESSION['error'] = "Error adding aircon: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    
    header("Location: ../pages/aircon_list.php");
    exit();
} else {
    // If not POST request, redirect back
    header("Location: ../pages/aircon_list.php");
    exit();
}
