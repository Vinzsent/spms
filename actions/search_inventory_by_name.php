<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $term = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($term === '') {
        echo json_encode(['success' => false, 'message' => 'Missing search term']);
        exit;
    }
    
    // Debug logging
    error_log("Search term: " . $term);
    error_log("Database connection: " . ($conn ? 'Connected' : 'Failed'));

    // First try exact match (case-insensitive)
    $sql_exact = "SELECT inventory_id, item_name, type, brand, size, color, current_stock, unit, unit_cost, inventory_status, category, location FROM inventory WHERE LOWER(item_name) = LOWER(?) LIMIT 1";
    $stmt = $conn->prepare($sql_exact);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('s', $term);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Database execute failed: ' . $stmt->error]);
        exit;
    }
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $item = $res->fetch_assoc();
        echo json_encode(['success' => true, 'match' => 'exact', 'item' => $item]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Fallback to partial matches (top 5)
    $like = '%' . $term . '%';
    $sql_like = "SELECT inventory_id, item_name, type, brand, size, color, current_stock, unit, unit_cost, inventory_status, category, location FROM inventory WHERE item_name LIKE ? ORDER BY item_name ASC LIMIT 5";
    $stmt2 = $conn->prepare($sql_like);
    if (!$stmt2) {
        error_log("Prepare failed for partial search: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database prepare failed for partial search: ' . $conn->error]);
        exit;
    }
    $stmt2->bind_param('s', $like);
    if (!$stmt2->execute()) {
        error_log("Execute failed for partial search: " . $stmt2->error);
        echo json_encode(['success' => false, 'message' => 'Database execute failed for partial search: ' . $stmt2->error]);
        exit;
    }
    $res2 = $stmt2->get_result();

    $items = [];
    while ($row = $res2->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt2->close();

    if (count($items) === 0) {
        echo json_encode(['success' => false, 'message' => 'No matching inventory items found']);
        exit;
    }

    echo json_encode(['success' => true, 'match' => 'partial', 'items' => $items]);
} catch (Exception $e) {
    error_log('search_inventory_by_name error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>