<?php
include 'includes/db.php';
$res = $conn->query('SHOW COLUMNS FROM purchase_orders');
$cols = [];
while ($r = $res->fetch_assoc()) {
    $cols[] = $r['Field'];
}
file_put_contents('po_cols.txt', implode(", ", $cols));
echo "Done\n";
