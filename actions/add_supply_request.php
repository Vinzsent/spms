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
    error_log('Form submitted with data: ' . print_r($_POST, true));
    
    // Debug: Check if all required fields are present
    $required_fields = ['date_requested', 'date_needed', 'department_unit', 'purpose', 'sales_type', 'category', 'request_description', 'unit_cost', 'total_cost', 'quantity_requested', 'unit', 'quality_issued', 'amount'];
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

        $date_requested           = trim($_POST['date_requested'] ?? '');
        $date_needed              = trim($_POST['date_needed'] ?? '');
        $department_unit          = trim($_POST['department_unit'] ?? '');
        $purpose                  = trim($_POST['purpose'] ?? '');
        $sales_type               = trim($_POST['sales_type'] ?? '');
        $category                 = trim($_POST['category'] ?? '');
        $request_description      = trim($_POST['request_description'] ?? '');
        $unit_cost                = trim($_POST['unit_cost'] ?? '');
        $total_cost               = trim($_POST['total_cost'] ?? '');
        $quantity_requested       = trim($_POST['quantity_requested'] ?? '');
        $unit                     = trim($_POST['unit'] ?? '');
        $quality_issued           = trim($_POST['quality_issued'] ?? '');
        $amount                   = trim($_POST['amount'] ?? '');

        // Prepare and execute SQL statement for supply_request table
        $stmt = $conn->prepare("
            INSERT INTO supply_request (
                date_requested, date_needed, department_unit, purpose, sales_type, category, request_description, unit_cost, total_cost, quantity_requested, unit, quality_issued, amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssssss",
            $date_requested,
            $date_needed,
            $department_unit,
            $purpose,
            $sales_type,
            $category,
            $request_description,
            $unit_cost,
            $total_cost,
            $quantity_requested,
            $unit,
            $quality_issued,
            $amount
        );

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $_SESSION['request_success'] = true;
        $_SESSION['message'] = "Supply request added successfully";

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            echo json_encode(['status' => 'success']);
            exit;
        }

        echo "<script>alert('Request Successful!'); window.location.href='../pages/supply_request.php';</script>";
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
