<?php
// Clean output buffer to prevent any HTML/whitespace before JSON
ob_clean();
session_start();

// Set JSON header immediately
header('Content-Type: application/json');

// Disable error display to prevent HTML in JSON response
error_reporting(0);
ini_set('display_errors', 0);

include '../includes/auth.php';
include '../includes/db.php';

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
    echo json_encode(['success' => false, 'message' => $error_message, 'raw_input' => substr($raw_input, 0, 200)]);
    exit;
}

// Check if decoded data is valid
if (!$input || !is_array($input)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data structure']);
    exit;
}

function savePurchaseOrder($data, $conn, $user_id) {
    try {
        $conn->begin_transaction();
        
        // Validate required fields
        if (empty($data['po_number']) || empty($data['po_date']) || empty($data['supplier_name'])) {
            throw new Exception('Required fields are missing: PO Number, Date, and Supplier Name');
        }
        
        // Prepare main PO data with proper escaping
        $po_number = $conn->real_escape_string(trim($data['po_number']));
        $po_date = $conn->real_escape_string($data['po_date']);
        $supplier_name = $conn->real_escape_string(trim($data['supplier_name']));
        $supplier_address = $conn->real_escape_string($data['supplier_address'] ?? '');
        $payment_method = $conn->real_escape_string($data['payment_method'] ?? 'Check');
        $payment_details = $conn->real_escape_string($data['payment_details'] ?? '');
        $cash_amount = floatval($data['cash_amount'] ?? 0);
        $notes = $conn->real_escape_string($data['notes'] ?? '');
        
        // Check if PO number already exists (for new POs)
        $po_id = isset($data['po_id']) ? intval($data['po_id']) : null;
        
        if (!$po_id) {
            $check_sql = "SELECT po_id FROM purchase_orders WHERE po_number = '$po_number'";
            $check_result = $conn->query($check_sql);
            if ($check_result && $check_result->num_rows > 0) {
                throw new Exception('PO Number already exists: ' . $po_number);
            }
        }
        
        if ($po_id) {
            // Update existing PO
            $sql = "UPDATE purchase_orders SET 
                    po_date = '$po_date',
                    supplier_name = '$supplier_name',
                    supplier_address = '$supplier_address',
                    payment_method = '$payment_method',
                    payment_details = '$payment_details',
                    cash_amount = $cash_amount,
                    notes = '$notes',
                    updated_at = NOW()
                    WHERE po_id = $po_id";
        } else {
            // Insert new PO
            $sql = "INSERT INTO purchase_orders (
                    po_number, po_date, supplier_name, supplier_address,
                    payment_method, payment_details, cash_amount, notes, created_by
                    ) VALUES (
                    '$po_number', '$po_date', '$supplier_name', '$supplier_address',
                    '$payment_method', '$payment_details', $cash_amount, '$notes', $user_id
                    )";
        }
        
        if (!$conn->query($sql)) {
            throw new Exception('Failed to save purchase order: ' . $conn->error);
        }
        
        if (!$po_id) {
            $po_id = $conn->insert_id;
        }
        
        // Delete existing items for update
        $conn->query("DELETE FROM purchase_order_items WHERE po_id = $po_id");
        
        // Insert items
        $total_amount = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                if (!empty($item['description'])) {
                    $item_number = $index + 1;
                    $description = $conn->real_escape_string(trim($item['description']));
                    $quantity = floatval($item['quantity'] ?? 0);
                    $unit_cost = floatval($item['unit_cost'] ?? 0);
                    $line_total = $quantity * $unit_cost;
                    $total_amount += $line_total;
                    
                    $item_sql = "INSERT INTO purchase_order_items 
                                (po_id, item_number, item_description, quantity, unit_cost, line_total)
                                VALUES ($po_id, $item_number, '$description', $quantity, $unit_cost, $line_total)";
                    
                    if (!$conn->query($item_sql)) {
                        throw new Exception('Failed to save item: ' . $conn->error);
                    }
                }
            }
        }
        
        // Update total amount
        $update_total_sql = "UPDATE purchase_orders SET total_amount = $total_amount WHERE po_id = $po_id";
        if (!$conn->query($update_total_sql)) {
            throw new Exception('Failed to update total amount: ' . $conn->error);
        }
        
        $conn->commit();
        return [
            'success' => true, 
            'message' => 'Purchase order saved successfully', 
            'po_id' => $po_id,
            'po_number' => $po_number,
            'total_amount' => $total_amount
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Process the save request
try {
    $result = savePurchaseOrder($input, $conn, $user_id);
    
    // Ensure clean JSON output
    ob_clean();
    echo json_encode($result);
    exit;
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
?>
