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
$campus_raw  = strtoupper(trim($_GET['campus'] ?? ''));

list($sy_inv_start, $sy_inv_end) = parse_school_year_range($sy_inv_raw);

$inv_where_conditions = [];

// Match search behavior of property_release_logs.php
if (!empty($search_term)) {
    $search_escaped = $conn->real_escape_string($search_term);
    $inv_where_conditions[] = "(i.facility_name LIKE '%$search_escaped%' 
        OR i.item_description LIKE '%$search_escaped%' 
        OR i.campus LIKE '%$search_escaped%' 
        OR i.notes LIKE '%$search_escaped%')";
}

// School year filter on date_created (same logic as page)
if ($sy_inv_start && $sy_inv_end) {
    $start_esc = $conn->real_escape_string($sy_inv_start);
    $end_esc   = $conn->real_escape_string($sy_inv_end);
    $inv_where_conditions[] = "i.date_created >= '$start_esc' AND i.date_created <= '$end_esc'";
}

// Campus filter (BED/TED)
if ($campus_raw === 'BED' || $campus_raw === 'TED') {
    $campus_esc = $conn->real_escape_string($campus_raw);
    $inv_where_conditions[] = "TRIM(UPPER(i.campus)) = '$campus_esc'";
}

$inv_where = !empty($inv_where_conditions) ? ' WHERE ' . implode(' AND ', $inv_where_conditions) : '';

// Export from release_logs, same data as Release Logs table
$sql = "SELECT 
            i.date,
            i.facility_name,
            i.item_description,
            i.quantity,
            i.unit,
            i.campus,
            i.notes
        FROM release_logs i
        $inv_where
        ORDER BY i.date_created DESC";
$result = $conn->query($sql);

while (ob_get_level() > 0) { ob_end_clean(); }

$filename = 'release_logs_export_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');
// Headers match visible columns in Release Logs table
fputcsv($out, [
    'Date',
    'Name',
    'Item Description',
    'Quantity',
    'Unit',
    'Campus',
    'Notes',
]);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $fmt_date($row['date']),
            $row['facility_name'],
            $row['item_description'],
            $fmt_num($row['quantity'], 0),
            $row['unit'],
            $row['campus'],
            $row['notes'],
        ]);
    }
}

fclose($out);
exit;
