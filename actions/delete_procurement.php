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
    $procurement_id = trim($_POST['id'] ?? '');
    
    if (empty($procurement_id)) {
        $_SESSION['error'] = "Procurement ID is required";
        header("Location: ../pages/procurement.php");
        exit;
    }
    
    try {
        // First, check if the procurement record exists
        $check_stmt = $conn->prepare("SELECT procurement_id, item_name FROM supplier_transaction WHERE procurement_id = ?");
        if (!$check_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $check_stmt->bind_param("s", $procurement_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Procurement record not found");
        }
        
        $record = $result->fetch_assoc();
        $item_name = $record['item_name'];
        
        // Delete the procurement record
        $delete_stmt = $conn->prepare("DELETE FROM supplier_transaction WHERE procurement_id = ?");
        if (!$delete_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $delete_stmt->bind_param("s", $procurement_id);
        
        if (!$delete_stmt->execute()) {
            throw new Exception("Failed to delete procurement record: " . $delete_stmt->error);
        }
        
        if ($delete_stmt->affected_rows > 0) {
            echo "<script>alert('Procurement record for \"$item_name\" has been deleted successfully'); window.location.href='../pages/procurement.php';</script>";
            exit;
        } else {
            throw new Exception("No records were deleted");
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='../pages/procurement.php';</script>";
        exit;
    }
} else {
    $_SESSION['error'] = "Invalid request method";
    echo "<script>alert('Error: Invalid request method'); window.location.href='../pages/procurement.php';</script>";
    exit;
}
?>
