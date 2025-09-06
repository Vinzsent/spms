<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get form data
        $description = trim($_POST['description']);
        $serial_model = trim($_POST['serial_model']);
        $transfer_type = isset($_POST['transfer_type']) ? implode(', ', $_POST['transfer_type']) : '';
        $reason_transfer = trim($_POST['reason_transfer']);
        $transfer_date = $_POST['transfer_date'];
        $from_department = $_POST['from_department'];
        $from_property_code = trim($_POST['from_property_code']);
        $from_building_room = trim($_POST['from_building_room']);
        $to_department = $_POST['to_department'];
        $to_property_code = trim($_POST['to_property_code']);
        $to_building_room = trim($_POST['to_building_room']);
        
        // Get user info
        $user_id = $_SESSION['user']['user_id'];
        $created_by = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
        
        // Validate required fields
        if (empty($description) || empty($reason_transfer) || empty($transfer_date) || 
            empty($from_department) || empty($to_department) || empty($transfer_type)) {
            throw new Exception('Please fill in all required fields.');
        }
        
        // Check if departments are different
        if ($from_department === $to_department) {
            throw new Exception('Source and destination departments must be different.');
        }
        
        // Generate transfer request number
        $transfer_number = 'TR-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Check if transfer number already exists
        $check_sql = "SELECT transfer_id FROM transfer_requests WHERE transfer_number = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $transfer_number);
        $check_stmt->execute();
        
        // If exists, generate a new one
        while ($check_stmt->get_result()->num_rows > 0) {
            $transfer_number = 'TR-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $check_stmt->bind_param("s", $transfer_number);
            $check_stmt->execute();
        }
        $check_stmt->close();
        
        // Insert transfer request
        $sql = "INSERT INTO transfer_requests (
                    transfer_number, description, serial_model, transfer_type, 
                    reason_transfer, transfer_date, from_department, from_property_code, 
                    from_building_room, to_department, to_property_code, to_building_room,
                    status, created_by, user_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssi", 
            $transfer_number, $description, $serial_model, $transfer_type,
            $reason_transfer, $transfer_date, $from_department, $from_property_code,
            $from_building_room, $to_department, $to_property_code, $to_building_room,
            $created_by, $user_id
        );
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Transfer request submitted successfully! Request Number: " . $transfer_number;
            header('Location: ../pages/equipment_transfer_request.php');
        } else {
            throw new Exception('Error saving transfer request: ' . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../pages/equipment_transfer_request.php');
    }
} else {
    header('Location: ../pages/equipment_transfer_request.php');
}

$conn->close();
?>
