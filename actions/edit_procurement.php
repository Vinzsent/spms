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
    try {
        $procurement_id = trim($_POST['procurement_id'] ?? '');
        $item_name = trim($_POST['item_name'] ?? '');
        $supplier_id = trim($_POST['supplier_id'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $unit_price = trim($_POST['unit_price'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $invoice_no = trim($_POST['invoice_no'] ?? '');
        $sales_type = trim($_POST['sales_type'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $total_amount = floatval($_POST['total_amount'] ?? 0);
        
        // Validation
        if (empty($procurement_id)) {
            throw new Exception("Procurement ID is required");
        }
        if (empty($item_name)) {
            throw new Exception("Item name is required");
        }
        if (empty($supplier_id)) {
            throw new Exception("Supplier is required");
        }
        if (empty($quantity) || !is_numeric($quantity) || $quantity <= 0) {
            throw new Exception("Valid quantity is required");
        }
        if (empty($unit)) {
            throw new Exception("Unit is required");
        }
        if (empty($unit_price) || !is_numeric($unit_price) || $unit_price <= 0) {
            throw new Exception("Valid unit price is required");
        }
        
        $user_id = $_SESSION['user']['id'] ?? 1;
        // Recalculate total amount to ensure accuracy
        $total_amount = floatval($quantity) * floatval($unit_price);
        
        // Update procurement record
        $stmt = $conn->prepare("
            UPDATE supplier_transaction SET 
                item_name = ?,
                supplier_id = ?,
                invoice_no = ?,
                sales_type = ?,
                category = ?,
                quantity = ?,
                unit = ?,
                unit_price = ?,
                total_amount = ?,
                notes = ?,
                last_updated_by = ?,
                date_updated = CURRENT_TIMESTAMP()
            WHERE procurement_id = ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sisssissdsii", $item_name, $supplier_id, $invoice_no, $sales_type, $category, $quantity, $unit, $unit_price, $total_amount, $notes, $user_id, $procurement_id);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Procurement record updated successfully";
        } else {
            throw new Exception("No changes were made or procurement not found");
        }

        $stmt->close();
        $conn->close();

        header("Location: ../pages/procurement.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../pages/procurement.php");
        exit;
    }
}
?>