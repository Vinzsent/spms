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
    $picture = '';
    $additional_pictures = [];
    $campus = !empty($_POST['campus']) ? trim($_POST['campus']) : null;

    // Handle Multiple Pictures Upload
    if (isset($_FILES['pictures']) && is_array($_FILES['pictures']['name'])) {
        $upload_dir = '../uploads/aircons/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['pictures']['name'] as $key => $name) {
            if ($_FILES['pictures']['error'][$key] == 0) {
                $file_tmp = $_FILES['pictures']['tmp_name'][$key];
                $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = 'aircon_' . time() . '_' . uniqid() . '.' . $file_ext;
                $target_file = $upload_dir . $filename;

                if (move_uploaded_file($file_tmp, $target_file)) {
                    $path = 'uploads/aircons/' . $filename;
                    if (empty($picture)) {
                        $picture = $path; // First image as main picture
                    }
                    $additional_pictures[] = $path;
                }
            }
        }
    }

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
        date_created,
        campus
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../pages/aircon_list.php");
        exit();
    }

    // Bind parameters - 22 parameters
    $stmt->bind_param(
        "sssssssssssssssdsddsiis",
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
        $user_id,
        $campus
    );

    if ($stmt->execute()) {
        $aircon_id = $conn->insert_id;

        // Insert additional pictures if any
        if (!empty($additional_pictures)) {
            $img_stmt = $conn->prepare("INSERT INTO aircon_images (aircon_id, image_path) VALUES (?, ?)");
            foreach ($additional_pictures as $path) {
                $img_stmt->bind_param("is", $aircon_id, $path);
                $img_stmt->execute();
            }
            $img_stmt->close();
        }

        $_SESSION['message'] = "Aircon unit '$model' has been added successfully with images.";
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
