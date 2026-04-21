<?php
include '../includes/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login first";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Edit office supply action submitted with data: ' . print_r($_POST, true));
    
    // Check required fields
    $required_fields = ['request_id', 'date_requested', 'date_needed', 'department_unit', 'purpose', 'category', 'item_name', 'request_description', 'quantity_requested', 'unit'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        $val = $_POST[$field] ?? '';
        if (is_array($val) || strlen(trim((string)$val)) === 0) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $_SESSION['error'] = "Please fill in all required fields: " . implode(', ', array_map('ucwords', array_map(function($f){ return str_replace('_', ' ', $f); }, $missing_fields)));
        $department_unit = trim($_POST['department_unit'] ?? '');
        header("Location: ../pages/office_supply_requests.php" . ($department_unit ? "?office=" . urlencode($department_unit) : ""));
        exit;
    }
    
    try {
        $request_id                = trim($_POST['request_id'] ?? '');
        $date_requested           = trim($_POST['date_requested'] ?? '');
        $date_needed              = trim($_POST['date_needed'] ?? '');
        $department_unit          = trim($_POST['department_unit'] ?? '');
        $purpose                  = trim($_POST['purpose'] ?? '');
        $category                 = trim($_POST['category'] ?? '');
        $item_name                = trim($_POST['item_name'] ?? '');
        $request_description      = trim($_POST['request_description'] ?? '');

        // Fallback for item_name
        if (empty($item_name) && !empty($request_description)) {
            $item_name = substr($request_description, 0, 50);
        }

        $brand                    = trim($_POST['brand'] ?? '');
        $color                    = trim($_POST['color'] ?? '');
        $unit_cost                = trim($_POST['unit_cost'] ?? '');
        $total_cost               = trim($_POST['total_cost'] ?? '');
        $quantity_requested       = trim($_POST['quantity_requested'] ?? '');
        $unit                     = trim($_POST['unit'] ?? '');
        $quality_issued           = trim($_POST['quality_issued'] ?? '');
        $item_number              = (int)($_POST['item_number'] ?? 0);
        
        $stmt = $conn->prepare("
            UPDATE supply_request SET 
                date_requested = ?, 
                date_needed = ?, 
                department_unit = ?, 
                purpose = ?, 
                category = ?, 
                item_name = ?,
                request_description = ?, 
                brand = ?,
                color = ?,
                unit_cost = ?, 
                total_cost = ?, 
                quantity_requested = ?, 
                unit = ?,
                quality_issued = ?, 
                amount = ?,
                item_number = ?
            WHERE request_id = ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssssssssssssssi",
            $date_requested,
            $date_needed,
            $department_unit,
            $purpose,
            $category,
            $item_name,
            $request_description,
            $brand,
            $color,
            $unit_cost,
            $total_cost,
            $quantity_requested,
            $unit,
            $quality_issued,
            $amount,
            $item_number,
            $request_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $_SESSION['request_success'] = true;
        $_SESSION['message'] = "Supply request updated successfully";

        $redirect_url = "../pages/office_supply_requests.php?office=" . urlencode($department_unit);
        echo "<script>alert('Request Updated Successfully!'); window.location.href='$redirect_url';</script>";
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        $department_unit = trim($_POST['department_unit'] ?? '');
        $redirect_url = "../pages/office_supply_requests.php" . ($department_unit ? "?office=" . urlencode($department_unit) : "");
        header("Location: $redirect_url");
        exit;
    }
}
