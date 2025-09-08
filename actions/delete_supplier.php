<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supplier_id'])) {
    $supplier_id = $_POST['supplier_id'];
    $supplier_name = $_POST['supplier_name'] ?? 'Unknown Supplier';
    
    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM supplier WHERE supplier_id = ?");
    $stmt->bind_param("i", $supplier_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Supplier '$supplier_name' has been deleted successfully.";
        } else {
            $_SESSION['error'] = "Supplier not found or already deleted.";
        }
    } else {
        $_SESSION['error'] = "Error deleting supplier: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
}

$conn->close();
header("Location: ../pages/suppliers.php");
exit;
?> 