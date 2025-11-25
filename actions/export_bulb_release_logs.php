<?php
// Export Bulb Release Logs to an Excel-compatible file
// Exports all rows that match the same filters used in bulb_release_logs.php

include '../includes/auth.php';
include '../includes/db.php';

// Helper: parse School Year input into start/end dates (July 1 to June 30)
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

// Read filters (mirror bulb_release_logs.php)
$sy_inv_raw  = $_GET['sy_inv']  ?? '';
$search_term = trim($_GET['search'] ?? '');
// Remarks filter is sent as "campus" query param from the page
$campus_raw = strtoupper(trim($_GET['campus'] ?? $_GET['remarks'] ?? ''));

list($sy_inv_start, $sy_inv_end) = parse_school_year_range($sy_inv_raw);

$inv_where_conditions = [];

if (!empty($search_term)) {
    $search_escaped = $conn->real_escape_string($search_term);
    $inv_where_conditions[] = "(i.area_location LIKE '%$search_escaped%' \
        OR i.quantity LIKE '%$search_escaped%' \
        OR i.remarks LIKE '%$search_escaped%')";
}

if ($sy_inv_start && $sy_inv_end) {
    $start_esc = $conn->real_escape_string($sy_inv_start);
    $end_esc   = $conn->real_escape_string($sy_inv_end);
    $inv_where_conditions[] = "i.date_created >= '$start_esc' AND i.date_created <= '$end_esc'";
}

if ($campus_raw === 'OLD' || $campus_raw === 'NEW') {
    $campus_esc = $conn->real_escape_string($campus_raw);
    $inv_where_conditions[] = "TRIM(UPPER(i.remarks)) = '$campus_esc'";
}

$inv_where = !empty($inv_where_conditions) ? ' WHERE ' . implode(' AND ', $inv_where_conditions) : '';

$sql = "SELECT i.area_location, i.date_installed, i.quantity, i.remarks, i.date_created
        FROM bulb_release_logs i
        $inv_where
        ORDER BY i.date_created DESC";

$result = $conn->query($sql);

// Prepare Excel-compatible response (tab-separated values)
$filename = 'bulb_release_logs_' . date('Ymd_His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

// Column headers
$headers = [
    'Area Location',
    'Date Installed',
    'Quantity',
    'Remarks',
    'Date Created',
];

echo implode("\t", $headers) . "\n";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dateInstalled = $row['date_installed'] ?? '';
        if (!empty($dateInstalled) && $dateInstalled !== '0000-00-00' && $dateInstalled !== '0000-00-00 00:00:00') {
            $dateInstalled = date('Y-m-d', strtotime($dateInstalled));
        } else {
            $dateInstalled = '';
        }

        $dateCreated = $row['date_created'] ?? '';
        if (!empty($dateCreated) && $dateCreated !== '0000-00-00' && $dateCreated !== '0000-00-00 00:00:00') {
            $dateCreated = date('Y-m-d H:i:s', strtotime($dateCreated));
        } else {
            $dateCreated = '';
        }

        $line = [
            $row['area_location'] ?? '',
            $dateInstalled,
            $row['quantity'] ?? '',
            $row['remarks'] ?? '',
            $dateCreated,
        ];

        // Escape tabs and newlines
        $line = array_map(function ($value) {
            $value = (string)$value;
            $value = str_replace(["\t", "\r", "\n"], ' ', $value);
            return $value;
        }, $line);

        echo implode("\t", $line) . "\n";
    }
}

exit;
