<?php
include '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

include '../includes/rate_limit.php';

$unit_code = $_POST['unit_code'] ?? '';
$building = $_POST['building'] ?? '';
$room_office = $_POST['room_office'] ?? '';
$date_serviced = $_POST['date_serviced'] ?? '';
$serviced_by = $_POST['serviced_by'] ?? '';
$contact_no = $_POST['contact_no'] ?? '';
$other_work_details = $_POST['other_work_details'] ?? '';
$details_of_work = $_POST['details_of_work'] ?? '';
$parts_replaced = $_POST['parts_replaced'] ?? '';
$final_status = $_POST['final_status'] ?? '';
$next_service_date = $_POST['next_service_date'] ?? null;
if (empty($next_service_date)) $next_service_date = null;
$verified_by = $_POST['verified_by'] ?? '';
$date_verified = $_POST['date_verified'] ?? '';

$work_done = isset($_POST['work_done']) && is_array($_POST['work_done']) ? json_encode($_POST['work_done']) : json_encode([]);

$sql = "INSERT INTO service_completions (
    unit_code, building, room_office, date_serviced, serviced_by, contact_no, 
    work_done, other_work_details, details_of_work, parts_replaced, final_status, 
    next_service_date, verified_by, date_verified
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ssssssssssssss", 
        $unit_code, $building, $room_office, $date_serviced, $serviced_by, $contact_no,
        $work_done, $other_work_details, $details_of_work, $parts_replaced, $final_status,
        $next_service_date, $verified_by, $date_verified
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Service completion slip submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
}
?>
