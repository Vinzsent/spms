<?php
include '../includes/db.php';
header('Content-Type: application/json');

$sql = "
    SELECT 
        bt.type_name,
        sc.category_name
    FROM business_types bt
    LEFT JOIN supplier_categories sc ON bt.id = sc.business_type_id AND sc.status = 'Active'
    WHERE bt.status = 'Active'
    ORDER BY bt.type_name ASC, sc.category_name ASC
";

$result = $conn->query($sql);
$mapping = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $typeName = $row['type_name'];
        $catName = $row['category_name'];

        if (!isset($mapping[$typeName])) {
            $mapping[$typeName] = [];
        }

        if ($catName) {
            $mapping[$typeName][] = $catName;
        }
    }
    echo json_encode($mapping);
} else {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
}
