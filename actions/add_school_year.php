<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php'; // Ensure user is logged in

// Check if user is admin
$user_type = $_SESSION['user_type'] ?? '';
if (strtolower($user_type) !== 'admin') {
    $_SESSION['error'] = 'Access denied. Only administrators can perform this action.';
    header("Location: ../pages/school_year.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_year_name = trim($_POST['school_year_name']);

    if (empty($school_year_name)) {
        $_SESSION['error'] = 'School year name is required.';
        header("Location: ../pages/school_year.php");
        exit;
    }

    // Check if it already exists
    $check_stmt = $conn->prepare("SELECT shoo_year_id FROM school_year WHERE school_year_name = ?");
    $check_stmt->bind_param("s", $school_year_name);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'This school year already exists.';
        header("Location: ../pages/school_year.php");
        exit;
    }
    $check_stmt->close();

    $stmt = $conn->prepare("INSERT INTO school_year (school_year_name) VALUES (?)");
    $stmt->bind_param("s", $school_year_name);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'School year added successfully.';
    } else {
        $_SESSION['error'] = 'Error adding school year: ' . $conn->error;
    }

    $stmt->close();
    header("Location: ../pages/school_year.php");
    exit();
}
?>
