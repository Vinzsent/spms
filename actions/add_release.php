<?php
session_start();
include '../includes/db.php';

  // Get user ID from session
    $user_id = $_SESSION['user']['id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $date = trim($_POST['date'] ?? '');
    $facility_name = trim($_POST['facility_name'] ?? '');
    $item_description = trim($_POST['item_description'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $campus = trim($_POST['campus'] ?? '');
    $notes= trim($_POST['notes'] ?? '');

    // Insert new release log into database
    $sql = "INSERT INTO release_logs (
        date,
        facility_name,
        item_description,
        quantity,
        unit,
        campus,
        notes,
        created_by,
        date_created
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../pages/property_release_logs.php");
        exit();
    }

    // Types: s=string, d=double, i=integer
    // date (s), facility_name (s), item_description (s), quantity (i), unit (s), campus (s), notes (s), created_by (i)
    $stmt->bind_param(
        "sssisssi",
        $date,
        $facility_name,
        $item_description,
        $quantity,
        $unit,
        $campus,
        $notes,
        $user_id
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Release log has been added successfully.";
    } else {
        $_SESSION['error'] = "Error adding release log: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: ../pages/property_release_logs.php");
    exit();
} else {
    // If not POST request, redirect back
    header("Location: ../pages/property_release_logs.php");
    exit();
}
