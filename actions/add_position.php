<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php'; // Ensure user is logged in

// Check if user is admin
$user_type = $_SESSION['user_type'] ?? '';
if (strtolower($user_type) !== 'admin') {
    $_SESSION['error'] = 'Access denied. Only administrators can perform this action.';
    header("Location: ../pages/positions.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $position_name = trim($_POST['position_name']);

    if (empty($position_name)) {
        $_SESSION['error'] = 'Position name is required.';
        header("Location: ../pages/positions.php");
        exit;
    }

    // Check if it already exists
    $check_stmt = $conn->prepare("SELECT position_id FROM positions WHERE position_name = ?");
    $check_stmt->bind_param("s", $position_name);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'This position already exists.';
        header("Location: ../pages/positions.php");
        exit;
    }
    $check_stmt->close();

    $stmt = $conn->prepare("INSERT INTO positions (position_name) VALUES (?)");
    $stmt->bind_param("s", $position_name);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Position added successfully.';
    } else {
        $_SESSION['error'] = 'Error adding position: ' . $conn->error;
    }

    $stmt->close();
    header("Location: ../pages/positions.php");
    exit();
}
?>
