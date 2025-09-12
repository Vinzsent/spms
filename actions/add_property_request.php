<?php
include '../includes/db.php';
include '../includes/notification_helper.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = 'Please login first';
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather and sanitize inputs
    $date_requested      = trim($_POST['date_requested'] ?? '');
    $date_return         = trim($_POST['date_return'] ?? ''); // optional
    $temporary_transfer  = isset($_POST['temporary_transfer']) ? 'Temporary Transfer' : '';
    $permanent_transfer  = isset($_POST['permanent_transfer']) ? 'Permanent Transfer' : '';
    $reason_for_transfer = trim($_POST['reason_for_transfer'] ?? '');
    $category            = trim($_POST['category'] ?? '');
    $item_name           = trim($_POST['item_name'] ?? '');
    $request_description = trim($_POST['request_description'] ?? '');
    $brand               = trim($_POST['brand'] ?? '');
    $color               = trim($_POST['color'] ?? '');
    $type                = trim($_POST['type'] ?? '');
    $quantity_requested  = (int)($_POST['quantity_requested'] ?? 0);
    $request_type        = trim($_POST['request_type'] ?? 'property');
    $user_id             = (int)($_POST['user_id'] ?? 0);
    $department_unit     = trim($_POST['department_unit'] ?? ''); // for notification
    $tagging             = trim($_POST['tagging'] ?? 'consumables');

    // Basic validation
    $missing = [];
    foreach (['date_requested','category','item_name','request_description','quantity_requested','request_type','user_id'] as $f) {
        if (!isset($_POST[$f]) || $_POST[$f] === '' ) { $missing[] = $f; }
    }
    if (!empty($missing)) {
        $_SESSION['error'] = 'Missing required fields: ' . implode(', ', $missing);
        header('Location: ../pages/property_request.php');
        exit;
    }

    // Prepare INSERT with matching placeholders (includes 'type')
    $sql = "INSERT INTO property_request (
                date_requested, date_return, temporary_transfer, permanent_transfer, reason_for_transfer,
                category, item_name, request_description, brand, color, type, quantity_requested, request_type, user_id, tagging
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if (!$stmt = $conn->prepare($sql)) {
        $_SESSION['error'] = 'Prepare failed: ' . $conn->error;
        header('Location: ../pages/property_request.php');
        exit;
    }

    $stmt->bind_param(
        'sssssssssssisis',
        $date_requested,
        $date_return,
        $temporary_transfer,
        $permanent_transfer,
        $reason_for_transfer,
        $category,
        $item_name,
        $request_description,
        $brand,
        $color,
        $type,
        $quantity_requested,
        $request_type,
        $user_id,
        $tagging
    );

    if (!$stmt->execute()) {
        $_SESSION['error'] = 'Execute failed: ' . $stmt->error;
        header('Location: ../pages/property_request.php');
        exit;
    }

    $request_id = $conn->insert_id;

    // Optional notification
    if (function_exists('notifySupplyRequestSubmitted')) {
        notifySupplyRequestSubmitted($request_id, $department_unit, $request_description, $tagging, $conn);
    }

    $_SESSION['request_success'] = true;
    $_SESSION['message'] = 'Property supply request added successfully';

    // AJAX response support
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['status' => 'success', 'id' => $request_id]);
        exit;
    }

    header('Location: ../pages/property_request.php');
    exit;
}

// Fallback for non-POST
$_SESSION['error'] = 'Invalid request method.';
header('Location: ../pages/property_request.php');
exit;
