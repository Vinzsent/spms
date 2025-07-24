<?php
include '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and assign variables
    $id = (int)$_POST['supplier_id'];
    $supplier_name = trim($_POST['supplier_name']);
    $contact_person = trim($_POST['contact_person']);
    $contact_number = trim($_POST['contact_number']);
    $email_address = trim($_POST['email_address']);
    $fax_number = trim($_POST['fax_number']);
    $website = trim($_POST['website']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $zip_code = trim($_POST['zip_code']);
    $country = trim($_POST['country']);
    $business_type = trim($_POST['business_type']);
    $category = trim($_POST['category']);
    $payment_terms = trim($_POST['payment_terms']);
    $tin = trim($_POST['tax_identification_number']);
    $date_registered = trim($_POST['date_registered']);
    $status = trim($_POST['status']);
    $notes = trim($_POST['notes']);

    // Prepare SQL query using bind_param to prevent SQL injection
    $stmt = $conn->prepare("UPDATE supplier SET 
        supplier_name=?, contact_person=?, contact_number=?, email_address=?, fax_number=?, website=?, address=?, 
        city=?, province=?, zip_code=?, country=?, business_type=?, category=?, payment_terms=?, 
        tax_identification_number=?, date_registered=?, status=?, notes=? 
        WHERE supplier_id=?");

    $stmt->bind_param("ssssssssssssssssssi",
        $supplier_name, $contact_person, $contact_number, $email_address, $fax_number, $website, $address,
        $city, $province, $zip_code, $country, $business_type, $category, $payment_terms,
        $tin, $date_registered, $status, $notes, $id
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update supplier. Please try again.";
        error_log("MySQL Error: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    header("Location: ../pages/suppliers.php");
    exit();
}
?>
