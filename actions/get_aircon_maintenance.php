<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$aircon_id = isset($_GET['aircon_id']) ? (int)$_GET['aircon_id'] : 0;

if ($aircon_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid aircon ID']);
    exit;
}

try {
    // Get aircon details
    $aircon_sql = "SELECT brand, model, serial_number FROM aircons WHERE aircon_id = ?";
    $stmt = $conn->prepare($aircon_sql);
    $stmt->bind_param("i", $aircon_id);
    $stmt->execute();
    $aircon_result = $stmt->get_result();
    $aircon = $aircon_result->fetch_assoc();
    $stmt->close();

    if (!$aircon) {
        echo json_encode(['success' => false, 'message' => 'Aircon not found']);
        exit;
    }

    // Get maintenance records
    $maintenance_sql = "SELECT 
                            maintenance_id,
                            service_date,
                            service_type,
                            technician,
                            next_scheduled_date,
                            remarks,
                            created_by,
                            date_created
                        FROM aircon_maintenance 
                        WHERE aircon_id = ? 
                        ORDER BY service_date DESC";
    
    $stmt = $conn->prepare($maintenance_sql);
    $stmt->bind_param("i", $aircon_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $maintenance_records = [];
    while ($row = $result->fetch_assoc()) {
        $maintenance_records[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'aircon' => $aircon,
        'maintenance_records' => $maintenance_records
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
