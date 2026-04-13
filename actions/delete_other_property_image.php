<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$image_path = $_POST['image_path'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (empty($image_path) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

// Ensure the path is safe
$image_path = basename($image_path);
// Actually $image_path in DB is 'uploads/property/filename.jpg', let's just use what's passed 
// but query db to make sure it belongs to that ID
$full_path = $_POST['image_path']; // trust db lookup for safety

$sql = "SELECT id, image_path FROM other_property_images WHERE property_id = ? AND image_path = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $full_path);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $img_id = $row['id'];
    
    // Delete from database
    $del = "DELETE FROM other_property_images WHERE id = ?";
    $del_stmt = $conn->prepare($del);
    $del_stmt->bind_param("i", $img_id);
    if ($del_stmt->execute()) {
        // Delete physical file
        $physical_path = '../' . $full_path;
        if (file_exists($physical_path)) {
            unlink($physical_path);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Image not found']);
}
