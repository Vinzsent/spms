<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

$user_type = $_SESSION['user_type'] ?? '';
if (strtolower($user_type) !== 'admin') {
    $_SESSION['error'] = 'Access denied.';
    header("Location: ../pages/positions.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $position_id = (int)$_POST['position_id'];
    $position_name = trim($_POST['position_name']);

    if (empty($position_name) || $position_id <= 0) {
        $_SESSION['error'] = 'Invalid input provided.';
        header("Location: ../pages/positions.php");
        exit;
    }

    // Check if it already exists for ANOTHER record
    $check_stmt = $conn->prepare("SELECT position_id FROM positions WHERE position_name = ? AND position_id != ?");
    $check_stmt->bind_param("si", $position_name, $position_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'This position name is already used by another record.';
        header("Location: ../pages/positions.php");
        exit;
    }
    $check_stmt->close();

    $stmt = $conn->prepare("UPDATE positions SET position_name = ? WHERE position_id = ?");
    $stmt->bind_param("si", $position_name, $position_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Position updated successfully.';
    } else {
        $_SESSION['error'] = 'Error updating position: ' . $conn->error;
    }

    $stmt->close();
    header("Location: ../pages/positions.php");
    exit();
}
?>
