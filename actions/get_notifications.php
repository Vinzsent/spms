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
    // Get notifications for the current user, ordered by most recent first
    $sql = "SELECT id, type, title, message, related_id, related_type, is_read, created_at 
            FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 20";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'type' => $row['type'],
            'title' => $row['title'],
            'message' => $row['message'],
            'related_id' => $row['related_id'],
            'related_type' => $row['related_type'],
            'is_read' => (bool)$row['is_read'],
            'created_at' => $row['created_at']
        ];
    }
    
    // Get unread count
    $count_sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $unread_count = $count_result->fetch_assoc()['unread_count'];
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);
    
} catch (Exception $e) {
    error_log("Error getting notifications: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error retrieving notifications']);
}

$conn->close();
?> 