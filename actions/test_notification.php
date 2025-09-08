<?php
session_start();
include '../includes/db.php';
include '../includes/notification_helper.php';

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    echo "Please login first";
    exit;
}

$user_id = $_SESSION['user']['id'];

// Create a test notification
$title = "🧪 Test Notification";
$message = "This is a test notification to verify the notification system is working properly. Created at " . date('Y-m-d H:i:s');

try {
    $result = createNotification($user_id, 'request', $title, $message, null, 'test', $conn);
    
    if ($result) {
        echo "✅ Test notification created successfully!<br>";
        echo "Title: $title<br>";
        echo "Message: $message<br>";
        echo "User ID: $user_id<br>";
        echo "<br><a href='../pages/notifications.php'>View Notifications</a>";
    } else {
        echo "❌ Failed to create test notification";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

$conn->close();
?>
