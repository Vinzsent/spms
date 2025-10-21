<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../pages/aircon_list.php');
    exit();
}

// Collect and sanitize inputs
$aircon_id            = isset($_POST['aircon_id']) ? (int)$_POST['aircon_id'] : 0;
$item_name            = trim($_POST['item_name'] ?? '');
$category             = trim($_POST['category'] ?? '');
$brand                = trim($_POST['brand'] ?? '');
$model                = trim($_POST['model'] ?? '');
$type                 = trim($_POST['type'] ?? '');
$capacity             = trim($_POST['capacity'] ?? '');
$serial_number        = trim($_POST['serial_number'] ?? '');
$location             = trim($_POST['location'] ?? '');
$status               = trim($_POST['status'] ?? 'Working');
$purchase_date        = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
$warranty_expiry      = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
$last_service_date    = !empty($_POST['last_service']) ? $_POST['last_service'] : null;
$maintenance_schedule = !empty($_POST['maintenance_schedule']) ? $_POST['maintenance_schedule'] : null;
$supplier_id          = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
$installation_date    = !empty($_POST['installation_date']) ? $_POST['installation_date'] : null;
$energy_efficient     = trim($_POST['energy_efficient'] ?? '');
$power_consumption    = !empty($_POST['power_consumption']) ? floatval($_POST['power_consumption']) : null;
$notes                = trim($_POST['notes'] ?? '');
$purchase_price       = !empty($_POST['purchase_price']) ? floatval($_POST['purchase_price']) : null;
$depreciated_value    = !empty($_POST['depreciated_value']) ? floatval($_POST['depreciated_value']) : null;

// Basic validation
if ($aircon_id <= 0) {
    $_SESSION['error'] = 'Invalid aircon ID.';
    header('Location: ../pages/aircon_list.php');
    exit();
}

if (empty($brand) || empty($model)) {
    $_SESSION['error'] = 'Brand and Model are required fields.';
    header('Location: ../pages/aircon_list.php');
    exit();
}

// Check if aircon exists
$check_sql = "SELECT aircon_id FROM aircons WHERE aircon_id = ?";
if ($check_stmt = $conn->prepare($check_sql)) {
    $check_stmt->bind_param('i', $aircon_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        $_SESSION['error'] = 'Aircon not found.';
        header('Location: ../pages/aircon_list.php');
        exit();
    }
    $check_stmt->close();
} else {
    $_SESSION['error'] = 'Database error (check aircon).';
    header('Location: ../pages/aircon_list.php');
    exit();
}

// Update the aircon record
$update_sql = "UPDATE aircons 
               SET item_number = ?, 
                   category = ?,
                   brand = ?, 
                   model = ?, 
                   type = ?, 
                   capacity = ?,
                   serial_number = ?, 
                   location = ?, 
                   status = ?, 
                   purchase_date = ?, 
                   warranty_expiry = ?,
                   last_service_date = ?, 
                   maintenance_schedule = ?,
                   supplier_id = ?,
                   installation_date = ?,
                   energy_efficiency_rating = ?,
                   power_consumption = ?,
                   notes = ?,
                   purchase_price = ?,
                   depreciated_value = ?,
                   date_updated = NOW()
               WHERE aircon_id = ?";

$stmt = $conn->prepare($update_sql);
if (!$stmt) {
    $_SESSION['error'] = 'Database error (prepare update): ' . $conn->error;
    header('Location: ../pages/aircon_list.php');
    exit();
}

// Bind parameters: strings, dates, integers, and floats
// 21 parameters: item_number, category, brand, model, type, capacity, serial_number, location, status,
// purchase_date, warranty_expiry, last_service_date, maintenance_schedule, supplier_id, installation_date,
// energy_efficient, power_consumption, notes, purchase_price, depreciated_value, aircon_id
// Types: s=string/date, i=integer, d=double/float
$stmt->bind_param(
    'sssssssssssssissdsddi',
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
    $supplier_id,         
    $installation_date,   
    $energy_efficient,    
    $power_consumption,   
    $notes,               
    $purchase_price,      
    $depreciated_value,   
    $aircon_id            
);

if (!$stmt->execute()) {
    $_SESSION['error'] = 'Error updating aircon: ' . $stmt->error;
    header('Location: ../pages/aircon_list.php');
    exit();
}
$stmt->close();

$_SESSION['message'] = "Aircon '{$brand} {$model}' has been updated successfully.";
header('Location: ../pages/aircon_list.php');
exit();