<?php
include '../includes/db.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login first";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Form submitted with data: ' . print_r($_POST, true));
    try {
        $supplier_name            = trim($_POST['supplier_name'] ?? '');
        $contact_person           = trim($_POST['contact_person'] ?? '');
        $landline_number          = trim($_POST['landline_number'] ?? '');
        $contact_number           = trim($_POST['contact_number'] ?? '');
        $business_type            = trim($_POST['business_type'] ?? '');
        $category                 = trim($_POST['category'] ?? '');
        $payment_terms            = trim($_POST['payment_terms'] ?? '');
        $date_registered          = $_POST['date_registered'] ?? null;
        $email_address            = trim($_POST['email_address'] ?? '');
        $fax_number               = trim($_POST['fax_number'] ?? '');
        $website                  = trim($_POST['website'] ?? '');
        $status                   = trim($_POST['status'] ?? '');
        $notes                    = trim($_POST['notes'] ?? '');
        $address                  = trim($_POST['address'] ?? '');
        $city                     = trim($_POST['city'] ?? '');
        $province                 = trim($_POST['province'] ?? '');
        $zip_code                 = trim($_POST['zip_code'] ?? '');
        $country                  = trim($_POST['country'] ?? '');
        $tax_identification_number = trim($_POST['tax_identification_number'] ?? '');

        $created_by = $_SESSION['user']['id'] ?? null;
        $date_created = date('Y-m-d H:i:s');

        error_log("Business Type: " . $_POST['business_type']);
        error_log("Product Category: " . $_POST['category']);

        // Validation - Supplier Name is still good to have but user said "must not be required"
        // I'll keep it as a soft check or just allow empty if they really want
        if (empty($supplier_name)) {
            $supplier_name = "Unnamed Supplier " . date('YmdHis');
        }

        // Prepare and execute SQL statement
        $stmt = $conn->prepare("
            INSERT INTO supplier (
                supplier_name, contact_person, landline_number, contact_number, email_address,
                fax_number, website, address, city, province, zip_code, country,
                business_type, category, tax_identification_number, payment_terms,
                date_registered, status, created_by, date_created, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssssssssssssss", // 21 strings
            $supplier_name,
            $contact_person,
            $landline_number,
            $contact_number,
            $email_address,
            $fax_number,
            $website,
            $address,
            $city,
            $province,
            $zip_code,
            $country,
            $business_type,
            $category,
            $tax_identification_number,
            $payment_terms,
            $date_registered,
            $status,
            $created_by,
            $date_created,
            $notes
        );

        if (!$stmt->execute()) {
            error_log('Database error: ' . $stmt->error);
            throw new Exception("Execute failed: " . $stmt->error);
        }
        error_log('Supplier added successfully');

        $_SESSION['message'] = "Supplier added successfully";

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            echo json_encode(['status' => 'success']);
            exit;
        }

        header("Location: ../pages/suppliers.php");
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

        header("Location: ../pages/suppliers.php");
        exit;
    }
}
