<?php
// Include database connection and authentication
include '../includes/auth.php';
include '../includes/db.php';

// Set headers for CSV file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_export_' . date('Y-m-d') . '.csv');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, "\xEF\xBB\xBF");

// Get only the necessary fields for the visible table
$sql = "SELECT 
            i.item_name,
            i.current_stock,
            i.unit,
            i.brand,
            i.color,
            i.size,
            i.date_updated,
            CASE 
                WHEN i.current_stock = 0 THEN 'Out of Stock'
                WHEN i.current_stock <= i.reorder_level THEN 'Critical'
                WHEN i.current_stock <= (i.reorder_level * 1.5) THEN 'Low'
                ELSE 'In Stock'
            END as stock_status
        FROM inventory i 
        WHERE i.receiver = 'Supply In-charge'
        ORDER BY i.item_name ASC";

$result = $conn->query($sql);

// Define CSV headers to match the visible table columns
$headers = array(
    'Item Name',
    'Current Stock',
    'Unit',
    'Brand',
    'Color',
    'Size',
    'Last Updated',
    'Status'
);

// Output headers
fputcsv($output, $headers);

// Output data rows
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $data = array(
            $row['item_name'],
            $row['current_stock'],
            $row['unit'],
            $row['brand'] ?? '',
            $row['color'] ?? '',
            $row['size'] ?? '',
            date('M d, Y', strtotime($row['date_updated'])),
            $row['stock_status']
        );
        
        fputcsv($output, $data);
    }
} else {
    fputcsv($output, array('No inventory items found'));
}

fclose($output);
exit;
