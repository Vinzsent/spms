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

try {
    // Mark all notifications as read for the current user
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        echo json_encode([
            'success' => true, 
            'message' => "Marked $affected_rows notifications as read"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
    }
    
} catch (Exception $e) {
    error_log("Error marking all notifications as read: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating notifications']);
}

$conn->close();
?> 