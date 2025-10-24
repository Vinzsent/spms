<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$aircon_id = isset($_POST['aircon_id']) ? (int)$_POST['aircon_id'] : 0;
$service_date = isset($_POST['service_date']) ? trim($_POST['service_date']) : '';
$service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
$technician = isset($_POST['technician']) ? trim($_POST['technician']) : '';
$next_scheduled_date = isset($_POST['next_scheduled_date']) ? trim($_POST['next_scheduled_date']) : null;
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
$created_by = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Unknown';

// Validate required fields
if ($aircon_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid aircon ID']);
    exit;
}

if (empty($service_date)) {
    echo json_encode(['success' => false, 'message' => 'Service date is required']);
    exit;
}

if (empty($service_type)) {
    echo json_encode(['success' => false, 'message' => 'Service type is required']);
    exit;
}

try {
    // Insert maintenance record
    $sql = "INSERT INTO aircon_maintenance 
            (aircon_id, service_date, service_type, technician, next_scheduled_date, remarks, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Handle null next_scheduled_date
    if (empty($next_scheduled_date)) {
        $next_scheduled_date = null;
    }
    
    $stmt->bind_param(
        "issssss",
        $aircon_id,
        $service_date,
        $service_type,
        $technician,
        $next_scheduled_date,
        $remarks,
        $created_by
    );
    
    if ($stmt->execute()) {
        $maintenance_id = $stmt->insert_id;
        
        // Update the aircon's last_service_date
        $update_sql = "UPDATE aircons SET last_service_date = ? WHERE aircon_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $service_date, $aircon_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Maintenance record added successfully',
            'maintenance_id' => $maintenance_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add maintenance record']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
