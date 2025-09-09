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
    
    // Log new fields specifically
    error_log('New fields - Date Purchase: ' . ($_POST['date_purchase'] ?? 'not set'));
    error_log('New fields - Category: ' . ($_POST['category'] ?? 'not set'));
    error_log('New fields - Brand Model: ' . ($_POST['brand_model'] ?? 'not set'));
    error_log('New fields - Color: ' . ($_POST['color'] ?? 'not set'));
    error_log('New fields - Type: ' . ($_POST['type'] ?? 'not set'));
    
    // Debug: Check if all required fields are present
    $required_fields = [
        'item_name', 'supplier_id', 'quantity', 'unit', 'unit_price', 
        'date_purchase', 'category', 'brand_model', 'color', 'type'
    ];
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
        $receiver = trim($_POST['receiver'] ?? '');
        
        // New fields
        $date_purchase = trim($_POST['date_purchase'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $brand_model = trim($_POST['brand_model'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $type = trim($_POST['type'] ?? '');
        
        // Validate new required fields
        if (empty($date_purchase)) {
            throw new Exception("Date of Purchase is required");
        }
        if (empty($category)) {
            throw new Exception("Category is required");
        }
        if (empty($brand_model)) {
            throw new Exception("Brand and Model is required");
        }
        if (empty($color)) {
            throw new Exception("Color is required");
        }
        if (empty($type)) {
            throw new Exception("Type is required");
        }
        
        $status = 'Pending'; // Default status
        $total_amount = $quantity * $unit_price;
        
        // Validate date format
        if (!strtotime($date_purchase)) {
            throw new Exception("Invalid date format for Date of Purchase");
        }
        
        // Validate numeric values
        if (!is_numeric($quantity) || $quantity <= 0) {
            throw new Exception("Quantity must be a positive number");
        }
        if (!is_numeric($unit_price) || $unit_price < 0) {
            throw new Exception("Unit price must be a non-negative number");
        }
        
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
                notes, status, date_created, category, brand_model, color, type, created_by, receiver
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $user_id = $_SESSION['user']['id'] ?? 1;

        $stmt->bind_param(
            "sssidssssssssssss",
            $item_name,
            $invoice_no,
            $sales_type,
            $supplier_id,
            $quantity,
            $unit,
            $unit_price,
            $total_amount,
            $notes,
            $status,
            $date_purchase,
            $category,
            $brand_model,
            $color,
            $type,
            $user_id,
            $receiver
        );

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            echo json_encode(['status' => 'success']);
            exit;
        }

        header("Location: ../pages/procurement.php");
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