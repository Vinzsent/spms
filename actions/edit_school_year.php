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
    $school_year_name = trim($_POST['school_year_name']);

    if (empty($school_year_name) || $shoo_year_id <= 0) {
        $_SESSION['error'] = 'Invalid input provided.';
        header("Location: ../pages/school_year.php");
        exit;
    }

    // Check if it already exists for ANOTHER record
    $check_stmt = $conn->prepare("SELECT shoo_year_id FROM school_year WHERE school_year_name = ? AND shoo_year_id != ?");
    $check_stmt->bind_param("si", $school_year_name, $shoo_year_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'This school year already exists.';
        header("Location: ../pages/school_year.php");
        exit;
    }
    $check_stmt->close();

    $stmt = $conn->prepare("UPDATE school_year SET school_year_name = ? WHERE shoo_year_id = ?");
    $stmt->bind_param("si", $school_year_name, $shoo_year_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'School year updated successfully.';
    } else {
        $_SESSION['error'] = 'Error updating school year: ' . $conn->error;
    }

    $stmt->close();
    header("Location: ../pages/school_year.php");
    exit();
}
?>
