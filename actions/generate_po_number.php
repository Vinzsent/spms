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
        $year = date('Y');
        $query = "SELECT po_number FROM purchase_orders WHERE po_number LIKE 'PO-$year-%' ORDER BY po_number DESC LIMIT 1";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $lastPO = $result->fetch_assoc()['po_number'];
            $lastNumber = intval(substr($lastPO, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return [
            'success' => true, 
            'po_number' => "PO-$year-$newNumber"
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Process the generate request
$result = generatePONumber($conn);
echo json_encode($result);
?>
