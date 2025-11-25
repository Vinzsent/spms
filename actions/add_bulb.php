<?php
session_start();
include '../includes/db.php';

  // Get user ID from session
    $user_id = $_SESSION['user']['id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $area_location = trim($_POST['area_location'] ?? '');
    $date_installed = trim($_POST['date_installed'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $remarks= trim($_POST['remarks'] ?? '');

    // Insert new release log into database
    $sql = "INSERT INTO bulb_release_logs (
        area_location,
        date_installed,
        quantity,
        remarks,
        date_created
    ) VALUES (?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../pages/bulb_release_logs.php");
        exit();
    }

    $stmt->bind_param(
        "sdis",
        $area_location,
        $date_installed,
        $quantity,
        $remarks
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Release log has been added successfully.";
    } else {
        $_SESSION['error'] = "Error adding release log: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: ../pages/bulb_release_logs.php");
    exit();
} else {
    // If not POST request, redirect back
    header("Location: ../pages/bulb_release_logs.php");
    exit();
}
