<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['inventory_id'])) {
    $inventory_id = intval($_GET['inventory_id']);
    
    $sql = "SELECT pi.*, s.supplier_name 
            FROM property_inventory pi 
            LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id 
            WHERE pi.inventory_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'item' => $item
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?> 