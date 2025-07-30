<?php
include '../includes/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login first";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $procurement_id = trim($_POST['procurement_id'] ?? '');
        $received_date = trim($_POST['received_date'] ?? '');
        $received_notes = trim($_POST['received_notes'] ?? '');
        
        // Validation
        if (empty($procurement_id)) {
            throw new Exception("Procurement ID is required");
        }
        if (empty($received_date)) {
            throw new Exception("Received date is required");
        }
        
        $user_id = $_SESSION['user']['id'] ?? 1;
        
        // Update procurement status to received
        $stmt = $conn->prepare("
            UPDATE procurement SET 
                status = 'Received',
                received_by = ?,
                date_received = ?,
                received_notes = ?,
                last_updated_by = ?,
                date_updated = CURRENT_TIMESTAMP
            WHERE procurement_id = ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("issii", $user_id, $received_date, $received_notes, $user_id, $procurement_id);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Item marked as received successfully";
        } else {
            throw new Exception("No changes were made or procurement not found");
        }

        $stmt->close();
        $conn->close();

        header("Location: ../pages/procurement.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../pages/procurement.php");
        exit;
    }
}
?>