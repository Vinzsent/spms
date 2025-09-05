<?php
header('Content-Type: application/json');
include '../includes/auth.php';
include '../includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid canvass ID']);
    exit;
}

$canvass_id = intval($_GET['id']);

try {
    // Get canvass details
    $canvass_query = "
        SELECT 
            c.canvass_id,
            c.canvass_date,
            c.total_amount,
            c.status,
            c.notes,
            c.created_at,
            c.updated_at,
            CONCAT(u.first_name, ' ', u.last_name) as created_by_name
        FROM canvass c
        LEFT JOIN user u ON c.created_by = u.id
        WHERE c.canvass_id = ?
    ";
    
    $stmt = $conn->prepare($canvass_query);
    $stmt->bind_param("i", $canvass_id);
    $stmt->execute();
    $canvass_result = $stmt->get_result();
    
    if ($canvass_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Canvass not found']);
        exit;
    }
    
    $canvass = $canvass_result->fetch_assoc();
    
    // Get canvass items
    $items_query = "
        SELECT 
            canvass_item_id,
            item_number,
            supplier_name,
            item_description,
            quantity,
            unit_cost,
            total_cost
        FROM canvass_items
        WHERE canvass_id = ?
        ORDER BY item_number ASC
    ";
    
    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("i", $canvass_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    echo json_encode([
        'success' => true,
        'canvass' => $canvass,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
