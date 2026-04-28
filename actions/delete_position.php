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

    if ($position_id <= 0) {
        $_SESSION['error'] = 'Invalid position specified.';
        header("Location: ../pages/positions.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM positions WHERE position_id = ?");
        $stmt->bind_param("i", $position_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Position deleted successfully.';
        } else {
            $_SESSION['error'] = 'Error deleting position. It might be in use.';
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Cannot delete position. It is likely being used by one or more user accounts.';
    }

    header("Location: ../pages/positions.php");
    exit();
}
?>
