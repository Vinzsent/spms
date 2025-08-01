<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_received = $_POST['date_received'];
    $invoice_no = $_POST['invoice_no'];
    $sales_type = $_POST['sales_type'];
    $category = $_POST['category'];
    $supplier_id = (int)$_POST['supplier_id'];
    $item_description = $_POST['item_description'];
    $quantity = (int)$_POST['quantity'];
    $unit = $_POST['unit'];
    $status = $_POST['status'];
    $unit_price = (float)$_POST['unit_price'];
    $amount = $quantity * $unit_price;

    // Fix: Use correct binding types
    $stmt = $conn->prepare("INSERT INTO supplier_transaction (date_received, invoice_no, sales_type, category, supplier_id, item_description, quantity, unit, status, unit_price, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisssdd", $date_received, $invoice_no, $sales_type,  $category, $supplier_id,  $item_description, $quantity, $unit, $status, $unit_price, $amount);
    //              Types:   s     s      s      s      i       s                 i        s      d          d

    if ($stmt->execute()) {
        echo "<script>alert('Transaction saved successfully!'); window.location.href='../pages/transaction_list.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
