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

    // Use direct SQL query to avoid bind_param issues
    $sql = "INSERT INTO supplier_transaction (date_received, invoice_no, sales_type, category, supplier_id, item_description, quantity, unit, status, unit_price, amount) 
            VALUES ('" . $conn->real_escape_string($date_received) . "', 
                    '" . $conn->real_escape_string($invoice_no) . "', 
                    '" . $conn->real_escape_string($sales_type) . "', 
                    '" . $conn->real_escape_string($category) . "', 
                    " . (int)$supplier_id . ", 
                    '" . $conn->real_escape_string($item_description) . "', 
                    " . (int)$quantity . ", 
                    '" . $conn->real_escape_string($unit) . "', 
                    '" . $conn->real_escape_string($status) . "', 
                    " . (float)$unit_price . ", 
                    " . (float)$amount . ")";

    if ($conn->query($sql)) {
        echo "<script>alert('Transaction saved successfully!'); window.location.href='../pages/transaction_list.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
