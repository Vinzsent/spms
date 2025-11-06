<?php
// Include database connection and authentication
include '../includes/auth.php';
include '../includes/db.php';

// Helper function to parse School Year range
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

// Get filters from GET parameters
$search_term = trim($_GET['search'] ?? '');
$sy_inv_raw = $_GET['sy_inv'] ?? '';

// Build WHERE conditions
$where_conditions = [];
$where_conditions[] = "pi.receiver = 'Property Custodian'";

// Add search filter
if (!empty($search_term)) {
    $search_escaped = $conn->real_escape_string($search_term);
    $where_conditions[] = "(pi.item_name LIKE '%$search_escaped%' OR pi.brand LIKE '%$search_escaped%' OR pi.type LIKE '%$search_escaped%')";
}

// Add school year filter
list($sy_start, $sy_end) = parse_school_year_range($sy_inv_raw);
if ($sy_start && $sy_end) {
    $start_esc = $conn->real_escape_string($sy_start);
    $end_esc = $conn->real_escape_string($sy_end);
    $where_conditions[] = "pi.date_created >= '$start_esc' AND pi.date_created <= '$end_esc'";
}

$where_clause = ' WHERE ' . implode(' AND ', $where_conditions);

// Get all inventory data (no pagination for export)
$sql = "SELECT pi.*, s.supplier_name 
        FROM property_inventory pi 
        LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id 
        $where_clause
        ORDER BY pi.date_created DESC";
$result = $conn->query($sql);

// Set headers for Excel file download (CSV format that Excel opens correctly)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=property_inventory_export_' . date('Y-m-d_His') . '.csv');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, "\xEF\xBB\xBF");

// Define CSV headers
$headers = array(
    'Item Name',
    'Description',
    'Current Stock',
    'Unit',
    'Brand',
    'Color',
    'Size',
    'Last Updated',
    'Status',
    'Reorder Level',
    'Location',
    'Supplier',
    'Unit Cost',
    'Category',
    'Type'
);

// Output headers using fputcsv for proper CSV formatting
fputcsv($output, $headers);

// Output data rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Calculate stock status
        $stock_level = 'Normal';
        if ($row['current_stock'] == 0) {
            $stock_level = 'Out of Stock';
        } elseif ($row['current_stock'] <= $row['reorder_level']) {
            $stock_level = 'Critical';
        } elseif ($row['current_stock'] <= ($row['reorder_level'] * 1.5)) {
            $stock_level = 'Low';
        }
        
        $data = array(
            $row['item_name'] ?? '',
            $row['description'] ?? '',
            $row['current_stock'] ?? 0,
            $row['unit'] ?? '',
            $row['brand'] ?? '',
            $row['color'] ?? '',
            $row['size'] ?? '',
            date('M d, Y', strtotime($row['date_updated'] ?? $row['date_created'])),
            $stock_level,
            $row['reorder_level'] ?? 0,
            $row['location'] ?? '',
            $row['supplier_name'] ?? '',
            $row['unit_cost'] ?? 0,
            $row['category'] ?? '',
            $row['type'] ?? ''
        );
        
        // Use fputcsv to properly format CSV with correct escaping and quoting
        fputcsv($output, $data);
    }
} else {
    fputcsv($output, array('No inventory items found'));
}

fclose($output);
exit;

