<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs coming from Edit Release Log modal
    $logs_id = isset($_POST['logs_id']) ? (int)$_POST['logs_id'] : 0;
    $date = trim($_POST['date'] ?? '');
    $facility_name = trim($_POST['facility_name'] ?? '');
    $item_description = trim($_POST['item_description'] ?? '');
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $unit = trim($_POST['unit'] ?? '');
    $campus = trim($_POST['campus'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Basic validation: ensure we have a valid target row
    if ($logs_id <= 0) {
        $_SESSION['error'] = 'Invalid release log ID.';
        header('Location: ../pages/property_release_logs.php');
        exit();
    }

    $sql = "UPDATE release_logs
            SET date = ?,
                facility_name = ?,
                item_description = ?,
                quantity = ?,
                unit = ?,
                campus = ?,
                notes = ?
            WHERE logs_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = 'Database error: ' . $conn->error;
        header('Location: ../pages/property_release_logs.php');
        exit();
    }

    // Types: date (s), facility_name (s), item_description (s), quantity (i), unit (s), campus (s), notes (s), id (i)
    $stmt->bind_param(
        'sssisssi',
        $date,
        $facility_name,
        $item_description,
        $quantity,
        $unit,
        $campus,
        $notes,
        $logs_id
    );

    if ($stmt->execute()) {
        // Show JS alert then redirect back to Release Logs page
        echo "<script>alert('Release log updated successfully.'); window.location.href='../pages/property_release_logs.php';</script>";
    } else {
        $_SESSION['error'] = 'Error updating release log: ' . $stmt->error;
        header('Location: ../pages/property_release_logs.php');
    }

    $stmt->close();
    $conn->close();

    exit();
} else {
    header('Location: ../pages/property_release_logs.php');
    exit();
}
