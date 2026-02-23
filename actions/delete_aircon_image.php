<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$image_path = isset($_POST['image_path']) ? $_POST['image_path'] : '';
$aircon_id = isset($_POST['aircon_id']) ? (int)$_POST['aircon_id'] : 0;

if (empty($image_path) || $aircon_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit();
}

// 1. Delete from database
$sql = "DELETE FROM aircon_images WHERE aircon_id = ? AND image_path = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $aircon_id, $image_path);

if ($stmt->execute()) {
    // 2. Delete file from server
    $full_path = '../' . $image_path;
    if (file_exists($full_path)) {
        unlink($full_path);
    }
    echo json_encode(['success' => true, 'message' => 'Image deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
