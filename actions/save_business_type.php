<?php
include '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$type_name = trim($_POST['type_name'] ?? '');

if (empty($type_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Business Type Name is required']);
    exit;
}

// Check if it already exists
$check = $conn->prepare("SELECT id FROM business_types WHERE type_name = ?");
$check->bind_param("s", $type_name);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'id' => $row['id'], 'name' => $type_name, 'message' => 'Business type already exists']);
    $check->close();
    exit;
}
$check->close();

// Insert new type
$stmt = $conn->prepare("INSERT INTO business_types (type_name, status) VALUES (?, 'Active')");
$stmt->bind_param("s", $type_name);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'id' => $stmt->insert_id, 'name' => $type_name]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}
$stmt->close();
