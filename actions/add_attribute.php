<?php
include '../../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$type = $_POST['type'] ?? '';

if ($type === 'business_type') {
    $business_type = trim($_POST['business_type_name'] ?? '');
    if (empty($business_type)) {
        echo json_encode(['status' => 'error', 'message' => 'Business Type Name is required']);
        exit;
    }

    // Check availability
    $check = $conn->prepare("SELECT id FROM business_types WHERE type_name = ?");
    $check->bind_param("s", $business_type);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Business Type already exists']);
        exit;
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO business_types (type_name) VALUES (?)");
    $stmt->bind_param("s", $business_type);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'id' => $stmt->insert_id, 'name' => $business_type]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} elseif ($type === 'category') {
    $business_type_id = $_POST['business_type_id'] ?? 0;
    $category_name = trim($_POST['category_name'] ?? '');

    if (!$business_type_id || empty($category_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Business Type and Category Name are required']);
        exit;
    }

    // Check availability
    $check = $conn->prepare("SELECT id FROM supplier_categories WHERE business_type_id = ? AND category_name = ?");
    $check->bind_param("is", $business_type_id, $category_name);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Category already exists for this Business Type']);
        exit;
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO supplier_categories (business_type_id, category_name) VALUES (?, ?)");
    $stmt->bind_param("is", $business_type_id, $category_name);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'id' => $stmt->insert_id, 'name' => $category_name]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
}
