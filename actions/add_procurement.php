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
    error_log('Procurement form submitted with data: ' . print_r($_POST, true));
    
    // Debug: Check if all required fields are present
    $required_fields = ['item_name', 'supplier_id', 'quantity', 'unit', 'unit_price'];
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
        $item_name = trim($_POST['item_name'] ?? '');
        $invoice_no = trim($_POST['invoice_no'] ?? '');
        $sales_type = trim($_POST['sales_type'] ?? '');
        $supplier_id = trim($_POST['supplier_id'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $unit_price = trim($_POST['unit_price'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $status = 'Pending'; // Default status
        $total_amount = $quantity * $unit_price;
        
        // Handle file uploads
        $invoice_path = '';
        $delivery_receipt_path = '';
        
        // Create upload directory if it doesn't exist
        $upload_dir = '../uploads/procurement/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Handle invoice upload
        if (isset($_FILES['invoice']) && $_FILES['invoice']['error'] == 0) {
            $invoice_file = $_FILES['invoice'];
            $invoice_ext = pathinfo($invoice_file['name'], PATHINFO_EXTENSION);
            $invoice_filename = 'invoice_' . time() . '_' . uniqid() . '.' . $invoice_ext;
            $invoice_path = $upload_dir . $invoice_filename;
            
            if (move_uploaded_file($invoice_file['tmp_name'], $invoice_path)) {
                $invoice_path = 'uploads/procurement/' . $invoice_filename;
            } else {
                throw new Exception("Failed to upload invoice file");
            }
        }
        
        // Handle delivery receipt upload
        if (isset($_FILES['delivery_receipt']) && $_FILES['delivery_receipt']['error'] == 0) {
            $receipt_file = $_FILES['delivery_receipt'];
            $receipt_ext = pathinfo($receipt_file['name'], PATHINFO_EXTENSION);
            $receipt_filename = 'receipt_' . time() . '_' . uniqid() . '.' . $receipt_ext;
            $delivery_receipt_path = $upload_dir . $receipt_filename;
            
            if (move_uploaded_file($receipt_file['tmp_name'], $delivery_receipt_path)) {
                $delivery_receipt_path = 'uploads/procurement/' . $receipt_filename;
            } else {
                throw new Exception("Failed to upload delivery receipt file");
            }
        }
        
        // Prepare and execute SQL statement for procurement table
        $stmt = $conn->prepare("
            INSERT INTO supplier_transaction (
                item_name, invoice_no, sales_type, supplier_id, quantity, unit, unit_price, total_amount, 
                invoice_path, delivery_receipt_path, notes, status, 
                created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $user_id = $_SESSION['user']['id'] ?? 1;

        $stmt->bind_param(
            "sssiisdsssssi",
            $item_name,
            $invoice_no,
            $sales_type,
            $supplier_id,
            $quantity,
            $unit,
            $unit_price,
            $total_amount,
            $invoice_path,
            $delivery_receipt_path,
            $notes,
            $status,
            $user_id
        );

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $_SESSION['message'] = "Procurement record added successfully";

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            echo json_encode(['status' => 'success']);
            exit;
        }

        echo "<script>alert('Purchase Record Added Successfully!'); window.location.href='../pages/procurement.php';</script>";
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

        header("Location: ../pages/procurement.php");
        exit;
    }
} 