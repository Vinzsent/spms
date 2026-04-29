<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

$user_type = $_SESSION['user_type'] ?? '';
if (strtolower($user_type) !== 'admin') {
    $_SESSION['error'] = 'Access denied.';
    header("Location: ../pages/school_year.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shoo_year_id = (int)$_POST['shoo_year_id'];

    if ($shoo_year_id <= 0) {
        $_SESSION['error'] = 'Invalid school year specified.';
        header("Location: ../pages/school_year.php");
        exit;
    }

    // Usually you would check if it's referenced elsewhere, 
    // but without explicit instructions, we'll perform a direct delete.
    // If it fails due to foreign key constraints, we catch it.
    try {
        $stmt = $conn->prepare("DELETE FROM school_year WHERE shoo_year_id = ?");
        $stmt->bind_param("i", $shoo_year_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'School year deleted successfully.';
        } else {
            $_SESSION['error'] = 'Error deleting school year. It might be in use.';
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Cannot delete school year. It is currently being used by other records.';
    }

    header("Location: ../pages/school_year.php");
    exit();
}
?>
