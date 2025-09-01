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
        
        if (empty($procurement_id)) {
            throw new Exception("Procurement ID is required");
        }
        
        $user_id = $_SESSION['user']['id'] ?? 1;
        
        // Update supplier_transaction status to received
        $stmt = $conn->prepare("
            UPDATE supplier_transaction SET 
                status = 'Received',
                updated_at = CURRENT_TIMESTAMP
            WHERE transaction_id = ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $procurement_id);

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Item marked as received successfully";
        } else {
            throw new Exception("No changes were made or procurement not found");
        }

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            echo json_encode(['status' => 'success']);
            exit;
        }

        header("Location: ../pages/procurement.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }

        header("Location: ../pages/procurement.php");
        exit;
    }
} 