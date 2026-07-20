<?php
include '../includes/db.php';
session_start();

// Check if authenticated
if (!isset($_SESSION['user'])) {
    echo "Unauthorized access.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = intval($_POST['emp_inv_id'] ?? 0);
    $item_type     = trim($_POST['item_type'] ?? 'Supply');
    $item_name     = trim($_POST['item_name'] ?? '');
    $brand         = trim($_POST['brand'] ?? '');
    $size          = trim($_POST['size'] ?? '');
    $color         = trim($_POST['color'] ?? '');
    $type          = trim($_POST['type'] ?? '');
    $category      = trim($_POST['category'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $quantity      = intval($_POST['quantity'] ?? 1);
    $unit          = trim($_POST['unit'] ?? 'pcs');
    $date_issued   = !empty($_POST['date_issued']) ? $_POST['date_issued'] : date('Y-m-d');
    $status        = trim($_POST['status'] ?? 'Issued');
    $remarks       = trim($_POST['remarks'] ?? '');
    $office        = trim($_POST['office'] ?? '');

    if ($id <= 0 || empty($item_name)) {
        echo "<script>alert('Invalid parameters.'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE employee_inventory 
        SET item_type = ?, item_name = ?, brand = ?, size = ?, color = ?, type = ?, category = ?, 
            serial_number = ?, quantity = ?, unit = ?, date_issued = ?, status = ?, remarks = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssssssissssi", 
        $item_type, $item_name, $brand, $size, $color, $type, $category, 
        $serial_number, $quantity, $unit, $date_issued, $status, $remarks, $id
    );

    if ($stmt->execute()) {
        $msg = "Inventory item updated successfully!";
    } else {
        $msg = "Error updating item: " . $stmt->error;
    }
    $stmt->close();

    $redirect = "../pages/employee_inventory_list.php";
    if (!empty($office)) {
        $redirect .= "?office=" . urlencode($office);
    }
    echo "<script>alert('" . addslashes($msg) . "'); window.location.href='$redirect';</script>";
    exit;
} else {
    echo "Invalid request method.";
}
?>
