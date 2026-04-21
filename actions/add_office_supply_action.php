<?php
include '../includes/db.php';
include '../includes/notification_helper.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login first";
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Form submitted with data: ' . print_r($_POST, true));

    // Debug: Check if all required fields are present
    $required_fields = ['date_requested', 'date_needed', 'department_unit', 'purpose', 'category', 'item_name', 'request_description', 'total_cost', 'quantity_requested', 'unit', 'amount', 'request_type', 'user_id'];
    foreach ($required_fields as $field) {
        $val = $_POST[$field] ?? '';
        if (is_array($val) || strlen(trim((string)$val)) === 0) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {

        throw new Exception("Please fill in all required fields: " . implode(', ', array_map('ucwords', array_map(function($f){ return str_replace('_', ' ', $f); }, $missing_fields))));
    }

    try {

        $date_requested           = trim($_POST['date_requested'] ?? '');
        $date_needed              = trim($_POST['date_needed'] ?? '');
        $department_unit          = trim($_POST['department_unit'] ?? '');
        $purpose                  = trim($_POST['purpose'] ?? '');
        $sales_type               = trim($_POST['sales_type'] ?? '');
        $category                 = trim($_POST['category'] ?? '');
        $item_name                = trim($_POST['item_name'] ?? '');
        $request_description      = trim($_POST['request_description'] ?? '');

        // Fallback for item_name if it was missed by the JS mapping
        if (empty($item_name) && !empty($request_description)) {
            $item_name = substr($request_description, 0, 50);
        }

        $brand                    = trim($_POST['brand'] ?? '');
        $color                    = trim($_POST['color'] ?? '');
        $unit_cost                = trim($_POST['unit_cost'] ?? '');
        $total_cost               = trim($_POST['total_cost'] ?? '');
        $quantity_requested       = trim($_POST['quantity_requested'] ?? '');
        $unit                     = trim($_POST['unit'] ?? '');
        $quality_issued           = trim($_POST['quality_issued'] ?? '');
        $amount                   = trim($_POST['amount'] ?? '');
        $request_type             = trim($_POST['request_type'] ?? '');
        $user_id                  = trim($_POST['user_id'] ?? '');
        $status                   = 'Pending';

        // ─── SEMESTER & ITEM NUMBER LOGIC ───
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

        $semData = getCurrentSemester($date_requested);
        $semester = $semData['sem'];
        $school_year = $semData['sy'];

        // Get item number from POST or calculate if not provided/zero
        $item_number = (int)($_POST['item_number'] ?? 0);
        if ($item_number <= 0) {
            // Calculate next item number based on total rows for this office
            $item_stmt = $conn->prepare("SELECT COUNT(*) as total_rows FROM supply_request WHERE department_unit = ?");
            $item_stmt->bind_param("s", $department_unit);
            $item_stmt->execute();
            $item_res = $item_stmt->get_result()->fetch_assoc();
            $item_number = ($item_res['total_rows'] ?? 0) + 1;
        }

        // Prepare and execute SQL statement for supply_request table
        $stmt = $conn->prepare("
            INSERT INTO supply_request (
                date_requested, date_needed, department_unit, purpose, sales_type, category, item_name, request_description, brand, color, unit_cost, total_cost, quantity_requested, unit, quality_issued, amount, request_type, user_id, status, item_number, semester, school_year
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssssssssssiisss",
            $date_requested,
            $date_needed,
            $department_unit,
            $purpose,
            $sales_type,
            $category,
            $item_name,
            $request_description,
            $brand,
            $color,
            $unit_cost,
            $total_cost,
            $quantity_requested,
            $unit,
            $quality_issued,
            $amount,
            $request_type,
            $user_id,
            $status,
            $item_number,
            $semester,
            $school_year
        );


        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Get the inserted request ID
        $request_id = $conn->insert_id;

        // Create notifications for relevant users
        if ($request_id) {
            notifySupplyRequestSubmitted($request_id, $department_unit, $request_description, $request_type, $conn);
        }

        $_SESSION['request_success'] = true;
        $_SESSION['message'] = "Supply request added successfully";

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            echo json_encode(['status' => 'success']);
            exit;
        }

        $department_unit = trim($_POST['department_unit'] ?? '');
        $redirect_url = "../pages/office_supply_requests.php?office=" . urlencode($department_unit);

        echo "<script>alert('Request Successful!'); window.location.href='$redirect_url';</script>";
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        $department_unit = trim($_POST['department_unit'] ?? '');
        $redirect_url = "../pages/office_supply_requests.php" . ($department_unit ? "?office=" . urlencode($department_unit) : "");

        // If AJAX request
        if (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        ) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }

        header("Location: $redirect_url");
        exit;
    }
}

