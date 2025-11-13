<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get room ID
    $room_id = intval($_POST['room_id'] ?? 0);

    // Validation
    if ($room_id <= 0) {
        $_SESSION['error'] = "Invalid room ID.";
        header("Location: ../pages/rooms_inventory.php");
        exit;
    }

    // Get room information before deletion for message
    $get_stmt = $conn->prepare("SELECT building_name, room_number FROM rooms_inventory WHERE id = ?");
    $get_stmt->bind_param("i", $room_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    
    if ($result->num_rows === 0) {
        $get_stmt->close();
        $_SESSION['error'] = "Room not found.";
        header("Location: ../pages/rooms_inventory.php");
        exit;
    }
    
    $room = $result->fetch_assoc();
    $building_name = $room['building_name'];
    $room_number = $room['room_number'];
    $get_stmt->close();

    // Delete the room from the database
    $stmt = $conn->prepare("DELETE FROM rooms_inventory WHERE id = ?");
    $stmt->bind_param("i", $room_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Room '$building_name - Room $room_number' has been deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete room. Room may not exist.";
        }
    } else {
        $_SESSION['error'] = "Error deleting room: " . $conn->error;
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: ../pages/rooms_inventory.php");
exit;
?>

