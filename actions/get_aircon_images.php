<?php
include '../includes/db.php';
header('Content-Type: application/json');

$aircon_id = isset($_GET['aircon_id']) ? (int)$_GET['aircon_id'] : 0;

if ($aircon_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Aircon ID']);
    exit();
}

$images = [];
$sql = "SELECT image_path FROM aircon_images WHERE aircon_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $aircon_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $images[] = $row['image_path'];
}

echo json_encode(['success' => true, 'images' => $images]);

$stmt->close();
$conn->close();
