<?php
include '../includes/auth.php';
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$po_id = $data['po_id'] ?? null;

if (!$po_id) {
    echo json_encode(['success' => false, 'message' => 'PO ID is required.']);
    exit;
}

// Check if user has permission
$user_type = $_SESSION['user_type'] ?? '';
$allowed_roles = ['Property Custodian', 'Supply In-charge'];

if (!in_array($user_type, $allowed_roles) && strtolower($user_type) !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$received_by = $_SESSION['user']['id'];
$received_date = date('Y-m-d H:i:s');

$stmt = $conn->prepare("UPDATE purchase_orders SET status = 'Received', received_by = ?, received_date = ? WHERE po_id = ? AND status != 'Received'");
$stmt->bind_param("isi", $received_by, $received_date, $po_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Purchase order marked as received successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Purchase order not found or already received.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
