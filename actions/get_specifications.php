<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $transaction_id = $_GET['transaction_id'] ?? '';

    if (empty($transaction_id)) {
        echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
        exit;
    }

    try {
        $sql = "SELECT * FROM transaction_specifications WHERE transaction_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $specifications = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $specifications]);
        } else {
            echo json_encode(['success' => true, 'data' => null, 'message' => 'No specifications found']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 