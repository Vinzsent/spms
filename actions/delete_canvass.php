<?php
header('Content-Type: application/json');
include '../includes/auth.php';
include '../includes/db.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['canvass_id']) || !is_numeric($input['canvass_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid canvass ID']);
    exit;
}

$canvass_id = intval($input['canvass_id']);

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if canvass exists and get details
    $check_query = "SELECT canvass_id, status FROM canvass WHERE canvass_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $canvass_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Canvass not found']);
        exit;
    }

    $canvass = $result->fetch_assoc();

    // Check if canvass can be deleted (only Draft, Canvassed and Cancelled can be deleted)
    if (!in_array($canvass['status'], ['Draft', 'Canvassed', 'Cancelled'])) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Cannot delete canvass with status: ' . $canvass['status']]);
        exit;
    }

    // Delete canvass items first (due to foreign key constraint)
    $delete_items_query = "DELETE FROM canvass_items WHERE canvass_id = ?";
    $stmt = $conn->prepare($delete_items_query);
    $stmt->bind_param("i", $canvass_id);
    $stmt->execute();

    // Delete canvass status history
    $delete_history_query = "DELETE FROM canvass_status_history WHERE canvass_id = ?";
    $stmt = $conn->prepare($delete_history_query);
    $stmt->bind_param("i", $canvass_id);
    $stmt->execute();

    // Delete the canvass record
    $delete_canvass_query = "DELETE FROM canvass WHERE canvass_id = ?";
    $stmt = $conn->prepare($delete_canvass_query);
    $stmt->bind_param("i", $canvass_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete canvass']);
        exit;
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Canvass ID ' . $canvass['canvass_id'] . ' deleted successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
