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

    $conn->begin_transaction();

    try {
        // Reset all school years
        $reset_stmt = $conn->prepare("UPDATE school_year SET current_year = NULL");
        $reset_stmt->execute();
        $reset_stmt->close();

        // Set the selected one as active
        $set_stmt = $conn->prepare("UPDATE school_year SET current_year = 'Yes' WHERE shoo_year_id = ?");
        $set_stmt->bind_param("i", $shoo_year_id);
        $set_stmt->execute();
        $set_stmt->close();

        $conn->commit();
        $_SESSION['success'] = 'Active school year updated successfully.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error updating active school year: ' . $e->getMessage();
    }

    header("Location: ../pages/school_year.php");
    exit();
}
?>
