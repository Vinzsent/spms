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

// Validate required fields
if ($maintenance_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid maintenance ID']);
    exit;
}

try {
    // Get aircon_id before deleting
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
    
    // Delete maintenance record
    $sql = "DELETE FROM aircon_maintenance WHERE maintenance_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $maintenance_id);
    
    if ($stmt->execute()) {
        // Update the aircon's last_service_date to the most recent remaining service date
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
            'message' => 'Maintenance record deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete maintenance record']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
