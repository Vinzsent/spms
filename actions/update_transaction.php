<?php
include '../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['transaction_id'];
  $date_received = $_POST['date_received'];
  $invoice_no = $_POST['invoice_no'];
  $sales_type = $_POST['sales_type'];
  $category = $_POST['category'];
  $description = $_POST['item_description'];
  $brand = $_POST['brand'];
  $type = $_POST['type'];
  $color = $_POST['color'];
  $quantity = $_POST['quantity'];
  $unit = $_POST['unit'];
  $unit_price = $_POST['unit_price'];
  $amount = $quantity * $unit_price;

  $stmt = $conn->prepare("UPDATE supplier_transaction SET date_received=?, invoice_no=?, sales_type=?, category=?, item_description=?, brand=?, type=?, color=?, quantity=?, unit=?, unit_price=?, amount=? WHERE transaction_id=?");
  $stmt->bind_param("ssssssssisdis", $date_received, $invoice_no, $sales_type, $category, $description, $brand, $type, $color, $quantity, $unit, $unit_price, $amount, $id);

  if ($stmt->execute()) {
    header("Location: ../pages/transaction_list.php?updated=1");
    exit;
  } else {
    die("Update failed: " . $conn->error);
  }
}
?>
