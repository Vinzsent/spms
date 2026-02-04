<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user']['id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

function generateCanvassNumber($conn)
{
    try {
        $year = date('Y');
        $query = "SELECT canvass_id FROM canvass ORDER BY canvass_id DESC LIMIT 1";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $lastCanvassId = $result->fetch_assoc()['canvass_id'];
            $newNumber = str_pad($lastCanvassId + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return [
            'success' => true,
            'canvass_number' => "CV-$year-$newNumber"
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Process the generate request
$result = generateCanvassNumber($conn);
echo json_encode($result);
