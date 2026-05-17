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
$date_first_reported = $_POST['date_first_reported'] ?? '';
$report_slip_no = $_POST['report_slip_no'] ?? '';
$reported_issue = $_POST['reported_issue'] ?? '';
$other_action_details = $_POST['other_action_details'] ?? '';
$actions_details_notes = $_POST['actions_details_notes'] ?? '';
$other_requested_action_details = $_POST['other_requested_action_details'] ?? '';
$request_justification = $_POST['request_justification'] ?? '';
$urgency_level = $_POST['urgency_level'] ?? '';
$requested_by = $_POST['requested_by'] ?? '';
$endorsed_to = $_POST['endorsed_to'] ?? '';
$date_endorsed = $_POST['date_endorsed'] ?? '';
$remarks = $_POST['remarks'] ?? '';

$actions_taken = isset($_POST['actions_taken']) && is_array($_POST['actions_taken']) ? json_encode($_POST['actions_taken']) : json_encode([]);
$requested_action = isset($_POST['requested_action']) && is_array($_POST['requested_action']) ? json_encode($_POST['requested_action']) : json_encode([]);
$admin_action = isset($_POST['admin_action']) && is_array($_POST['admin_action']) ? json_encode($_POST['admin_action']) : json_encode([]);

$sql = "INSERT INTO unresolved_units (
    unit_code, building, room_office, date_first_reported, report_slip_no, reported_issue,
    actions_taken, other_action_details, actions_details_notes, requested_action,
    other_requested_action_details, request_justification, urgency_level, requested_by,
    endorsed_to, date_endorsed, admin_action, remarks
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ssssssssssssssssss", 
        $unit_code, $building, $room_office, $date_first_reported, $report_slip_no, $reported_issue,
        $actions_taken, $other_action_details, $actions_details_notes, $requested_action,
        $other_requested_action_details, $request_justification, $urgency_level, $requested_by,
        $endorsed_to, $date_endorsed, $admin_action, $remarks
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Unresolved unit request submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
}
?>
