<?php
include '../includes/db.php';

$office = $_GET['office'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

if (empty($office)) {
    echo json_encode(['next_item' => 1]);
    exit;
}

// Same semester logic as in add_office_supply_action.php
function getCurrentSemester($date) {
    if (!$date) $date = date('Y-m-d');
    $month = (int)date('m', strtotime($date));
    $year = (int)date('Y', strtotime($date));
    if ($month >= 6 && $month <= 10) {
        return ['sem' => '1st Semester', 'sy' => $year . '-' . ($year + 1)];
    } elseif ($month >= 11 || $month <= 3) {
        $sy_start = ($month <= 3) ? $year - 1 : $year;
        return ['sem' => '2nd Semester', 'sy' => $sy_start . '-' . ($sy_start + 1)];
    } else {
        return ['sem' => 'Summer', 'sy' => ($year - 1) . '-' . $year];
    }
}

$semData = getCurrentSemester($date);
$semester = $semData['sem'];
$school_year = $semData['sy'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_rows FROM supply_request WHERE department_unit = ?");
$stmt->bind_param("s", $office);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$next_item = ($res['total_rows'] ?? 0) + 1;

header('Content-Type: application/json');
echo json_encode(['next_item' => $next_item]);
?>
