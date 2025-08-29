<?php
include '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "User deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete user: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid user ID.";
    }
    header("Location: ../pages/users.php");
    exit;
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../pages/users.php");
    exit;
}
