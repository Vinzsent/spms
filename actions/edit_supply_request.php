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
    error_log('Edit form submitted with data: ' . print_r($_POST, true));
    
    // Debug: Check if all required fields are present
    $required_fields = ['request_id', 'date_requested', 'date_needed', 'department_unit', 'purpose', 'category', 'item_name', 'request_description', 'quantity_requested', 'unit', 'brand', 'color'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        throw new Exception("Missing required fields: " . implode(', ', $missing_fields));
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
        $brand                    = trim($_POST['brand'] ?? '');
        $color                    = trim($_POST['color'] ?? '');
        $unit_cost                = trim($_POST['unit_cost'] ?? '');
        $total_cost               = trim($_POST['total_cost'] ?? '');
        $quantity_requested       = trim($_POST['quantity_requested'] ?? '');
        $unit                     = trim($_POST['unit'] ?? '');
        $quality_issued           = trim($_POST['quality_issued'] ?? '');
        $amount                   = trim($_POST['amount'] ?? $total_cost); // Use total_cost as fallback for amount
        
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
                amount = ?
            WHERE request_id = ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssssssssi",
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
            $request_id
        );

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        error_log("Update executed. Affected rows: " . $stmt->affected_rows);
        error_log("Request ID: " . $request_id);

        if ($stmt->affected_rows > 0) {
            $_SESSION['request_success'] = true;
            $_SESSION['message'] = "Supply request updated successfully";
        } else {
            // Check if the record exists
            $check_stmt = $conn->prepare("SELECT request_id FROM supply_request WHERE request_id = ?");
            $check_stmt->bind_param("i", $request_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows == 0) {
                throw new Exception("Request not found with ID: " . $request_id);
            } else {
                throw new Exception("No changes were made to the request");
            }
        }

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            echo json_encode(['status' => 'success']);
            exit;
        }

        echo "<script>alert('Request Updated Successfully!'); window.location.href='../pages/supply_request.php';</script>";
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }

        header("Location: ../pages/supply_request.php");
        exit;
    }
}
