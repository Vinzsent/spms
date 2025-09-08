<?php
// Start output buffering and clean any previous output
ob_start();
ob_clean();

// Disable error display to prevent HTML in JSON response
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Set JSON header immediately
header('Content-Type: application/json');

// Clean any output from includes
ob_start();
include '../includes/auth.php';
include '../includes/db.php';
ob_end_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user']['id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get raw input
$raw_input = file_get_contents('php://input');

// Check if input is empty
if (empty($raw_input)) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Try to decode JSON
$input = json_decode($raw_input, true);

// Check for JSON decode errors
if (json_last_error() !== JSON_ERROR_NONE) {
    $error_message = 'JSON decode error: ' . json_last_error_msg();
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

// Check if decoded data is valid
if (!$input || !is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data structure']);
    exit;
}

function saveCanvass($data, $conn, $user_id) {
    try {
        $conn->begin_transaction();
        
        // Validate required fields
        if (empty($data['canvass_date'])) {
            throw new Exception('Required fields are missing: Canvass Number and Date');
        }
        
        // Prepare main canvass data
        $canvass_date = $conn->real_escape_string($data['canvass_date']);
        $notes = $conn->real_escape_string($data['notes'] ?? '');
        
        // Check if canvass number already exists (for new canvass)
        $canvass_id = isset($data['canvass_id']) ? intval($data['canvass_id']) : null;
        
        if (!$canvass_id) {
            $check_sql = "SELECT canvass_id FROM canvass WHERE canvass_date = '$canvass_date'";
            $check_result = $conn->query($check_sql);
            if ($check_result && $check_result->num_rows > 0) {
                throw new Exception('Canvass Date already exists: ' . $canvass_date);
            }
        }
        
        if ($canvass_id) {
            // Update existing canvass
            $sql = "UPDATE canvass SET 
                    canvass_date = '$canvass_date',
                    notes = '$notes',
                    updated_at = NOW()
                    WHERE canvass_id = $canvass_id";
        } else {
            // Insert new canvass
            $sql = "INSERT INTO canvass (
                    canvass_date, notes, created_by
                    ) VALUES (
                    '$canvass_date', '$notes', $user_id
                    )";
        }
        
        if (!$conn->query($sql)) {
            throw new Exception('Failed to save canvass: ' . $conn->error);
        }
        
        if (!$canvass_id) {
            $canvass_id = $conn->insert_id;
        }
        
        // Delete existing items for update
        $conn->query("DELETE FROM canvass_items WHERE canvass_id = $canvass_id");
        
        // Insert items
        $total_amount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                if (!empty($item['supplier']) && !empty($item['description'])) {
                    $item_number = $index + 1;
                    $supplier_name = $conn->real_escape_string(trim($item['supplier']));
                    $description = $conn->real_escape_string(trim($item['description']));
                    $quantity = floatval($item['quantity'] ?? 0);
                    $unit_cost = floatval($item['unit_cost'] ?? 0);
                    $total_cost = $quantity * $unit_cost;
                    $total_amount += $total_cost;
                    
                    $item_sql = "INSERT INTO canvass_items 
                                (canvass_id, item_number, supplier_name, item_description, quantity, unit_cost, total_cost)
                                VALUES ($canvass_id, $item_number, '$supplier_name', '$description', $quantity, $unit_cost, $total_cost)";
                    
                    if (!$conn->query($item_sql)) {
                        throw new Exception('Failed to save item: ' . $conn->error);
                    }
                }
            }
        }
        
        // Update total amount
        $update_total_sql = "UPDATE canvass SET total_amount = $total_amount WHERE canvass_id = $canvass_id";
        if (!$conn->query($update_total_sql)) {
            throw new Exception('Failed to update total amount: ' . $conn->error);
        }
        
        $conn->commit();
        return [
            'success' => true, 
            'message' => 'Canvass saved successfully', 
            'canvass_id' => $canvass_id,
            'total_amount' => $total_amount
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Process the save request
try {
    $result = saveCanvass($input, $conn, $user_id);
    
    // Ensure completely clean JSON output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Start fresh output buffer for JSON only
    ob_start();
    echo json_encode($result);
    ob_end_flush();
    exit;
} catch (Exception $e) {
    // Clean all output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Start fresh output buffer for error JSON only
    ob_start();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    ob_end_flush();
    exit;
}
?>
