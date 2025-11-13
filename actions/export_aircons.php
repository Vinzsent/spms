<?php
// Suppress notices/warnings from leaking into CSV output
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

include '../includes/auth.php';
include '../includes/db.php';

// Helper to parse school year into date range (July 1 to June 30)
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

$fmt_date = function ($val) {
    if (empty($val) || $val === '0000-00-00' || $val === '0000-00-00 00:00:00') return '';
    $ts = strtotime($val);
    if ($ts === false) return (string)$val;
    return date('Y-m-d', $ts);
};

$fmt_num = function ($val, $dec = 2) {
    if ($val === null || $val === '') return '';
    return number_format((float)$val, $dec, '.', '');
};

$search_term = trim($_GET['search'] ?? '');
$sy_inv_raw  = $_GET['sy_inv'] ?? '';
$campus_raw  = strtoupper(trim($_GET['campus'] ?? ($_GET['dept'] ?? '')));

list($sy_inv_start, $sy_inv_end) = parse_school_year_range($sy_inv_raw);

$inv_where_conditions = [];

if (!empty($search_term)) {
    $search_escaped = $conn->real_escape_string($search_term);
    $inv_where_conditions[] = "(i.brand LIKE '%$search_escaped%' OR i.model LIKE '%$search_escaped%' OR i.type LIKE '%$search_escaped%' OR i.serial_number LIKE '%$search_escaped%' OR i.location LIKE '%$search_escaped%')";
}

if ($sy_inv_start && $sy_inv_end) {
    $start_esc = $conn->real_escape_string($sy_inv_start);
    $end_esc   = $conn->real_escape_string($sy_inv_end);
    $inv_where_conditions[] = "i.date_created >= '$start_esc' AND i.date_created <= '$end_esc'";
}

if ($campus_raw === 'BED' || $campus_raw === 'TED') {
    $campus_esc = $conn->real_escape_string($campus_raw);
    $inv_where_conditions[] = "i.campus = '$campus_esc'";
}

$inv_where = !empty($inv_where_conditions) ? ' WHERE ' . implode(' AND ', $inv_where_conditions) : '';

$sql = "SELECT i.item_number, i.campus, i.brand, i.model, i.type, i.capacity, i.serial_number, i.location, i.status,
               i.purchase_date, i.warranty_expiry, i.last_service_date, i.maintenance_schedule, i.installation_date,
               i.energy_efficiency_rating, i.power_consumption, i.purchase_price, i.depreciated_value, s.supplier_name, i.date_created
        FROM aircons i
        LEFT JOIN supplier s ON i.supplier_id = s.supplier_id
        $inv_where
        ORDER BY i.date_created DESC";
$result = $conn->query($sql);

while (ob_get_level() > 0) { ob_end_clean(); }

$filename = 'aircons_export_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');
fputcsv($out, [
    'Item Number', 'Campus', 'Brand', 'Model', 'Type', 'Capacity', 'Serial Number', 'Location', 'Status',
    'Purchase Date', 'Warranty Expiry', 'Last Service Date', 'Maintenance Schedule', 'Installation Date',
    'Energy Efficiency', 'Power Consumption', 'Purchase Price', 'Depreciated Value', 'Supplier', 'Date Created'
]);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $row['item_number'],
            $row['campus'],
            $row['brand'],
            $row['model'],
            $row['type'],
            $row['capacity'],
            $row['serial_number'],
            $row['location'],
            $row['status'],
            $fmt_date($row['purchase_date']),
            $fmt_date($row['warranty_expiry']),
            $fmt_date($row['last_service_date']),
            $row['maintenance_schedule'],
            $fmt_date($row['installation_date']),
            $row['energy_efficiency_rating'],
            $row['power_consumption'],
            $fmt_num($row['purchase_price'], 2),
            $fmt_num($row['depreciated_value'], 2),
            $row['supplier_name'],
            $fmt_date($row['date_created']),
        ]);
    }
}

fclose($out);
exit;
