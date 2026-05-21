<?php
include '../includes/db.php';
$res = $conn->query('SELECT inventory_id, item_name, supplier_id, location FROM inventory LIMIT 5');
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
