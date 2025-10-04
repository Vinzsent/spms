<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['log_id'])) {
    $log_id = intval($_GET['log_id']);
    
    $sql = "SELECT sl.*, i.item_name 
            FROM stock_logs sl 
            LEFT JOIN inventory i ON sl.inventory_id = i.inventory_id 
            WHERE sl.log_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $log_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $movement = $result->fetch_assoc();
        
        // Format date for datetime-local input
        $formatted_date = date('Y-m-d\TH:i', strtotime($movement['date_created']));
        $movement['formatted_date'] = $formatted_date;
        
        echo json_encode([
            'success' => true,
            'movement' => $movement
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Stock movement not found'
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
