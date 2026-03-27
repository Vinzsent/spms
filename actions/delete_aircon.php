<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['aircon_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data'
        ]);
        exit;
    }

    $aircon_id = intval($data['aircon_id']);

    try {
        // Start transaction
        $conn->begin_transaction();

        // Delete the aircon record
        $delete_sql = "DELETE FROM aircons WHERE aircon_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $aircon_id);

        if (!$delete_stmt->execute()) {
            throw new Exception('Failed to delete aircon record');
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Aircon deleted successfully'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
