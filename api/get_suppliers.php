<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

try {
    // Fetch active suppliers
    $sql = "SELECT supplier_id, supplier_name, address, city, province, zip_code 
            FROM supplier 
            WHERE status = 'Active' 
            ORDER BY supplier_name ASC";

    $result = $conn->query($sql);

    if ($result) {
        $suppliers = [];
        while ($row = $result->fetch_assoc()) {
            // Construct full address
            $fullAddress = $row['address'];
            if ($row['city']) $fullAddress .= ', ' . $row['city'];
            if ($row['province']) $fullAddress .= ', ' . $row['province'];
            if ($row['zip_code']) $fullAddress .= ' ' . $row['zip_code'];

            $suppliers[] = [
                'id' => $row['supplier_id'],
                'name' => $row['supplier_name'],
                'address' => trim($fullAddress, ', '),
                'raw_address' => $row['address'],
                'city' => $row['city'],
                'province' => $row['province']
            ];
        }

        echo json_encode([
            'success' => true,
            'suppliers' => $suppliers
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch suppliers: ' . $conn->error
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
