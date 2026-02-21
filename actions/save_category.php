<?php
include '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$business_type_name = trim($_POST['business_type_name'] ?? '');
$category_name = trim($_POST['category_name'] ?? '');

if (empty($business_type_name) || empty($category_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Business Type and Category Name are required']);
    exit;
}

// 1. Find Business Type ID
$bt_stmt = $conn->prepare("SELECT id FROM business_types WHERE type_name = ?");
$bt_stmt->bind_param("s", $business_type_name);
$bt_stmt->execute();
$bt_result = $bt_stmt->get_result();

if ($bt_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Business Type not found']);
    $bt_stmt->close();
    exit;
}

$bt_row = $bt_result->fetch_assoc();
$business_type_id = $bt_row['id'];
$bt_stmt->close();

// 2. Check if category already exists for this business type
$check = $conn->prepare("SELECT id FROM supplier_categories WHERE business_type_id = ? AND category_name = ?");
$check->bind_param("is", $business_type_id, $category_name);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    $row = $check_result->fetch_assoc();
    echo json_encode(['status' => 'success', 'id' => $row['id'], 'name' => $category_name, 'message' => 'Category already exists']);
    $check->close();
    exit;
}
$check->close();

// 3. Insert new category
$stmt = $conn->prepare("INSERT INTO supplier_categories (business_type_id, category_name, status) VALUES (?, ?, 'Active')");
$stmt->bind_param("is", $business_type_id, $category_name);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'id' => $stmt->insert_id, 'name' => $category_name]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}
$stmt->close();
