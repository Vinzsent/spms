<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if procurement_id is provided
if (!isset($_POST['procurement_id']) || empty($_POST['procurement_id'])) {
    echo json_encode(['success' => false, 'message' => 'Procurement ID is required']);
    exit;
}

$procurementId = $_POST['procurement_id'];
$conn->begin_transaction();

try {
    // 1. Get the procurement details
    $stmt = $conn->prepare("SELECT * FROM procurement WHERE procurement_id = ? AND status = 'Pending'");
    $stmt->bind_param("i", $procurementId);
    $stmt->execute();
    $procurement = $stmt->get_result()->fetch_assoc();
    
    if (!$procurement) {
        throw new Exception('Procurement not found or already processed');
    }
    
    // 2. Check if item already exists in inventory
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE item_name = ?");
    $stmt->bind_param("s", $procurement['item_name']);
    $stmt->execute();
    $inventoryItem = $stmt->get_result()->fetch_assoc();
    
    if ($inventoryItem) {
        // Update existing inventory item
        $newStock = $inventoryItem['current_stock'] + $procurement['quantity'];
        $stmt = $conn->prepare("UPDATE inventory SET 
                              current_stock = ?,
                              unit_cost = ?,
                              date_updated = NOW()
                              WHERE inventory_id = ?");
        $stmt->bind_param("ddi", 
            $newStock,
            $procurement['unit_price'],
            $inventoryItem['inventory_id']
        );
        $stmt->execute();
        $inventoryId = $inventoryItem['inventory_id'];
    } else {
        // Insert new inventory item
        $stmt = $conn->prepare("INSERT INTO inventory 
                              (item_name, category, unit, current_stock, unit_cost, date_created, date_updated)
                              VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssdd",
            $procurement['item_name'],
            $procurement['category'],
            $procurement['unit'],
            $procurement['quantity'],
            $procurement['unit_price']
        );
        $stmt->execute();
        $inventoryId = $conn->insert_id;
    }
    
    // 3. Record the stock movement
    $stmt = $conn->prepare("INSERT INTO stock_movements 
                          (inventory_id, movement_type, quantity, notes, created_by, created_at)
                          VALUES (?, 'IN', ?, 'Received from procurement #?', ?, NOW())");
    $notes = "Received from procurement #" . $procurement['procurement_id'];
    $stmt->bind_param("iisi", 
        $inventoryId, 
        $procurement['quantity'],
        $procurement['procurement_id'],
        $_SESSION['user_id']
    );
    $stmt->execute();
    
    // 4. Update procurement status to 'Received'
    $stmt = $conn->prepare("UPDATE procurement SET status = 'Received', date_updated = NOW() WHERE procurement_id = ?");
    $stmt->bind_param("i", $procurementId);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Items have been successfully added to inventory',
        'inventory_id' => $inventoryId
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing the request: ' . $e->getMessage()
    ]);
}
?>
