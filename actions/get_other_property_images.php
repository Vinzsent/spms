<?php
include '../includes/db.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Property ID']);
    exit();
}

$images = [];
$sql = "SELECT image_path FROM other_property_images WHERE property_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $images[] = $row['image_path'];
}

echo json_encode(['success' => true, 'images' => $images]);

$stmt->close();
$conn->close();
