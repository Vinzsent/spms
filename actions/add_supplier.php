<?php
include '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_name            = trim($_POST['supplier_name'] ?? '');
    $contact_person           = trim($_POST['contact_person'] ?? '');
    $contact_number           = trim($_POST['contact_number'] ?? '');
    $email_address            = trim($_POST['email_address'] ?? '');
    $fax_number               = trim($_POST['fax_number'] ?? '');
    $website                  = trim($_POST['website'] ?? '');
    $address                  = trim($_POST['address'] ?? '');
    $city                     = trim($_POST['city'] ?? '');
    $province                 = trim($_POST['province'] ?? '');
    $zip_code                 = trim($_POST['zip_code'] ?? '');
    $country                  = trim($_POST['country'] ?? '');
    $business_type            = trim($_POST['business_type'] ?? '');
    $product_category         = trim($_POST['product_category'] ?? '');
    $payment_terms            = trim($_POST['payment_terms'] ?? '');
    $tax_identification_number = trim($_POST['tax_identification_number'] ?? '');
    $date_registered          = $_POST['date_registered'] ?? null;
    $status                   = trim($_POST['status'] ?? '');
    $notes                    = trim($_POST['notes'] ?? '');

    $created_by = $_SESSION['user']['id'] ?? null;
    $date_created = date('Y-m-d H:i:s');

    // Prepare and execute SQL statement
    $stmt = $conn->prepare("
        INSERT INTO supplier (
            supplier_name, contact_person, contact_number, email_address,
            fax_number, website, address, city, province, zip_code, country,
            business_type, product_category, payment_terms, tax_identification_number,
            date_registered, status, created_by, date_created, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssssssssssssssssis",
        $supplier_name, $contact_person, $contact_number, $email_address,
        $fax_number, $website, $address, $city, $province, $zip_code, $country,
        $business_type, $product_category, $payment_terms, $tax_identification_number,
        $date_registered, $status, $created_by, $date_created, $notes
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier added successfully.";
    } else {
        $_SESSION['error'] = "Error adding supplier: " . $stmt->error;
    }

    $stmt->close();
    header("Location: ../pages/suppliers.php");
    exit;
}
?>
