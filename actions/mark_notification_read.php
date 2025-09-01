<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;

if ($notification_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    exit;
}

try {
    // Mark notification as read (only for the current user)
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notification_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
    }
    
} catch (Exception $e) {
    error_log("Error marking notification as read: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating notification']);
}

$conn->close();
?> 