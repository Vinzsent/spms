<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user']['id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

function generatePONumber($conn) {
    try {
        // Get the highest existing PO number from database
        $query = "SELECT MAX(CAST(po_number AS UNSIGNED)) as max_po FROM purchase_orders WHERE po_number REGEXP '^[0-9]+$'";
        $result = $conn->query($query);
        
        $nextNumber = 4296; // Starting number
        
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['max_po'] && $row['max_po'] >= 4296) {
                $nextNumber = $row['max_po'] + 1;
            }
        }
        
        return [
            'success' => true, 
            'po_number' => (string)$nextNumber
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Process the generate request
$result = generatePONumber($conn);
echo json_encode($result);
?>
