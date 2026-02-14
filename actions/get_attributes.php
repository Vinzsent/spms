<?php
include '../../includes/db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

if ($type === 'business_types') {
    $sql = "SELECT * FROM business_types WHERE status = 'Active' ORDER BY type_name ASC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} elseif ($type === 'categories') {
    $business_type_id = $_GET['business_type_id'] ?? 0;
    if (!$business_type_id) {
        echo json_encode([]);
        exit;
    }
    $stmt = $conn->prepare("SELECT * FROM supplier_categories WHERE business_type_id = ? AND status = 'Active' ORDER BY category_name ASC");
    $stmt->bind_param("i", $business_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Invalid request']);
}
