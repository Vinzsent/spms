<?php
// Include database connection and auth
include '../includes/auth.php';
include '../includes/db.php';

/**
 * Parse school year string into start and end dates
 * Format: "2023-2024" -> ["2023-07-01", "2024-06-30"]
 */
function parse_school_year_range($raw)
{
    $raw = trim((string)$raw);
    if ($raw === '') return [null, null];
    if (preg_match('/^(20\d{2})\s*-\s*(20\d{2})$/', $raw, $m)) {
        $startY = (int)$m[1];
        $endY = (int)$m[2];
        if ($endY === $startY + 1) {
            return [sprintf('%04d-07-01', $startY), sprintf('%04d-06-30', $endY)];
        }
    } elseif (preg_match('/^(19|20)\d{2}$/', $raw)) {
        $startY = (int)$raw;
        return [sprintf('%04d-07-01', $startY), sprintf('%04d-06-30', $startY + 1)];
    }
    return [null, null];
}

// Get filter parameters
$sy_logs_raw = $_GET['sy_logs'] ?? '';
$search_value = $_GET['search'] ?? '';

// Set headers for CSV file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=property_stock_movements_export_' . date('Y-m-d') . '.csv');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, "\xEF\xBB\xBF");

// Define CSV headers
$headers = array('Date', 'Item Name', 'Movement Type', 'Quantity', 'Previous Stock', 'New Stock', 'Receiver', 'Notes');
fputcsv($output, $headers);

// Parse school year range
list($sy_logs_start, $sy_logs_end) = parse_school_year_range($sy_logs_raw);

// Build WHERE conditions
$logs_where_conditions = [];

// Receiver filter for Property Custodian
$logs_where_conditions[] = "sl.receiver = 'Property Custodian'";

// Add school year filter if provided
if ($sy_logs_start && $sy_logs_end) {
    $start_esc = $conn->real_escape_string($sy_logs_start);
    $end_esc = $conn->real_escape_string($sy_logs_end);
    $logs_where_conditions[] = "sl.date_created >= '$start_esc' AND sl.date_created <= '$end_esc'";
}

// Add item name search filter if provided
if (!empty($search_value)) {
    $search_esc = $conn->real_escape_string($search_value);
    $logs_where_conditions[] = "(pi.item_name LIKE '%$search_esc%' OR sl.notes LIKE '%$search_esc%')";
}

// Build final WHERE clause
$logs_where = !empty($logs_where_conditions) ? ' WHERE ' . implode(' AND ', $logs_where_conditions) : '';

// Get stock movements data
$sql = "SELECT sl.date_created, pi.item_name, sl.movement_type, sl.quantity, sl.previous_stock, sl.new_stock, sl.receiver, sl.notes 
        FROM property_stock_logs sl 
        LEFT JOIN property_inventory pi ON sl.inventory_id = pi.inventory_id 
        $logs_where
        ORDER BY sl.date_created DESC";

$result = $conn->query($sql);

// Output data rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data = array(
            date('M d, Y H:i', strtotime($row['date_created'])),
            $row['item_name'],
            $row['movement_type'],
            $row['quantity'],
            $row['previous_stock'],
            $row['new_stock'],
            $row['receiver'] ?? 'N/A',
            $row['notes']
        );
        fputcsv($output, $data);
    }
} else {
    fputcsv($output, array('No property stock movements found matching the criteria'));
}

fclose($output);
exit;
