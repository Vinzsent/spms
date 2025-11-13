<?php
// Clean CSV export for maintenance records by aircon_id
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
include '../includes/auth.php';
include '../includes/db.php';

$aircon_id = isset($_GET['aircon_id']) ? (int)$_GET['aircon_id'] : 0;
if ($aircon_id <= 0) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(400);
    echo 'Invalid aircon_id';
    exit;
}

// Fetch aircon basic info
$aircon = [
    'brand' => '',
    'model' => '',
    'serial_number' => ''
];
if ($stmt = $conn->prepare('SELECT brand, model, serial_number FROM aircons WHERE aircon_id = ?')) {
    $stmt->bind_param('i', $aircon_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows) {
        $aircon = $res->fetch_assoc();
    }
    $stmt->close();
}

$sql = "SELECT maintenance_id, service_date, service_type, technician, next_scheduled_date, remarks, created_by, date_created
        FROM aircon_maintenance WHERE aircon_id = ? ORDER BY service_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $aircon_id);
$stmt->execute();
$result = $stmt->get_result();

while (ob_get_level() > 0) { ob_end_clean(); }
$filename = 'maintenance_aircon_' . $aircon_id . '_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
echo "\xEF\xBB\xBF"; // UTF-8 BOM

$out = fopen('php://output', 'w');
// Preface rows
fputcsv($out, ['Aircon Maintenance Export']);
fputcsv($out, ['Brand', $aircon['brand'], 'Model', $aircon['model'], 'Serial', $aircon['serial_number']]);
fputcsv($out, []);
// Header
fputcsv($out, ['Service Date', 'Service Type', 'Technician', 'Next Scheduled', 'Remarks', 'Created By', 'Date Created']);

$fmt = function($d) { if (!$d || $d === '0000-00-00' || $d === '0000-00-00 00:00:00') return ''; $t = strtotime($d); return $t ? date('Y-m-d', $t) : $d; };

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
            $fmt($row['service_date']),
            $row['service_type'],
            $row['technician'],
            $fmt($row['next_scheduled_date']),
            $row['remarks'],
            $row['created_by'],
            $fmt($row['date_created'])
        ]);
    }
}

fclose($out);
exit;
