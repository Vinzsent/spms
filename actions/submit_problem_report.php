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
$date_reported = $_POST['date_reported'] ?? '';
$reported_by = $_POST['reported_by'] ?? '';
$contact_no = $_POST['contact_no'] ?? '';
$other_problem_details = $_POST['other_problem_details'] ?? '';

$date_checked = $_POST['date_checked'] ?? null;
if (empty($date_checked)) $date_checked = null;

$checked_by = $_POST['checked_by'] ?? '';
$findings = $_POST['findings'] ?? '';
$remarks = $_POST['remarks'] ?? '';
$received_by_gso = $_POST['received_by_gso'] ?? '';

$date_received = $_POST['date_received'] ?? null;
if (empty($date_received)) $date_received = null;

$problem_observed = isset($_POST['problem_observed']) && is_array($_POST['problem_observed']) ? json_encode($_POST['problem_observed']) : json_encode([]);
$initial_action = isset($_POST['initial_action']) && is_array($_POST['initial_action']) ? json_encode($_POST['initial_action']) : json_encode([]);

$sql = "INSERT INTO problem_reports (
    unit_code, building, room_office, date_reported, reported_by, contact_no,
    problem_observed, other_problem_details, date_checked, checked_by, findings,
    initial_action, remarks, received_by_gso, date_received
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("sssssssssssssss", 
        $unit_code, $building, $room_office, $date_reported, $reported_by, $contact_no,
        $problem_observed, $other_problem_details, $date_checked, $checked_by, $findings,
        $initial_action, $remarks, $received_by_gso, $date_received
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Problem report slip submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
}
?>
