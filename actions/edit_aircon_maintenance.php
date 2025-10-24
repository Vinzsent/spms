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
$maintenance_id = isset($_POST['maintenance_id']) ? (int)$_POST['maintenance_id'] : 0;
$service_date = isset($_POST['service_date']) ? trim($_POST['service_date']) : '';
$service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
$technician = isset($_POST['technician']) ? trim($_POST['technician']) : '';
$next_scheduled_date = isset($_POST['next_scheduled_date']) ? trim($_POST['next_scheduled_date']) : null;
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

// Validate required fields
if ($maintenance_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid maintenance ID']);
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
    // Get aircon_id for this maintenance record
    $get_aircon_sql = "SELECT aircon_id FROM aircon_maintenance WHERE maintenance_id = ?";
    $get_stmt = $conn->prepare($get_aircon_sql);
    $get_stmt->bind_param("i", $maintenance_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $maintenance = $result->fetch_assoc();
    $get_stmt->close();
    
    if (!$maintenance) {
        echo json_encode(['success' => false, 'message' => 'Maintenance record not found']);
        exit;
    }
    
    $aircon_id = $maintenance['aircon_id'];
    
    // Update maintenance record
    $sql = "UPDATE aircon_maintenance 
            SET service_date = ?, 
                service_type = ?, 
                technician = ?, 
                next_scheduled_date = ?, 
                remarks = ?
            WHERE maintenance_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    // Handle null next_scheduled_date
    if (empty($next_scheduled_date)) {
        $next_scheduled_date = null;
    }
    
    $stmt->bind_param(
        "sssssi",
        $service_date,
        $service_type,
        $technician,
        $next_scheduled_date,
        $remarks,
        $maintenance_id
    );
    
    if ($stmt->execute()) {
        // Update the aircon's last_service_date to the most recent service date
        $update_sql = "UPDATE aircons a 
                       SET a.last_service_date = (
                           SELECT MAX(service_date) 
                           FROM aircon_maintenance 
                           WHERE aircon_id = ?
                       )
                       WHERE a.aircon_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $aircon_id, $aircon_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Maintenance record updated successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update maintenance record']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
