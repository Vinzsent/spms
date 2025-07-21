<?php
include '../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['transaction_id'];
  $invoice_no = $_POST['invoice_no'];
  $sales_type = $_POST['sales_type'];
  $category = $_POST['category'];
  $description = $_POST['item_description'];
  $quantity = $_POST['quantity'];
  $unit = $_POST['unit'];
  $unit_price = $_POST['unit_price'];
  $amount = $quantity * $unit_price;

  $stmt = $conn->prepare("UPDATE supplier_transaction SET invoice_no=?, sales_type=?, category=?, item_description=?, quantity=?, unit=?, unit_price=?, amount=? WHERE transaction_id=?");
  $stmt->bind_param("ssssisdii", $invoice_no, $sales_type, $category, $description, $quantity, $unit, $unit_price, $amount, $id);

  if ($stmt->execute()) {
    header("Location: ../pages/transaction_list.php?updated=1");
    exit;
  } else {
    die("Update failed: " . $conn->error);
  }
}
?>
