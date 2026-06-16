<?php
include 'c:/xampp/htdocs/darts/includes/db.php';

echo "Starting Item Number Backfill...\n";

// Helper function for semester detection (same as in the action script)
function getSemester($date) {
    if (!$date) return ['sem' => 'Unknown', 'sy' => 'Unknown'];
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

// 1. Get all unique departments
$dept_res = $conn->query("SELECT DISTINCT department_unit FROM supply_request");
if (!$dept_res) die("Error fetching departments: " . $conn->error);

while ($dept_row = $dept_res->fetch_assoc()) {
    $dept = $dept_row['department_unit'];
    echo "Processing Department: $dept\n";

    // 2. Get all requests for this department, ordered by date
    $req_res = $conn->query("SELECT request_id, date_requested FROM supply_request WHERE department_unit = '" . $conn->real_escape_string($dept) . "' ORDER BY date_requested ASC, request_id ASC");
    
    $counters = []; // Track counts per Semester+SY

    while ($req_row = $req_res->fetch_assoc()) {
        $req_id = $req_row['request_id'];
        $date = $req_row['date_requested'];
        
        $semData = getSemester($date);
        $semKey = $semData['sem'] . "|" . $semData['sy'];

        if (!isset($counters[$semKey])) {
            $counters[$semKey] = 1;
        } else {
            $counters[$semKey]++;
        }

        $item_num = $counters[$semKey];
        $semester = $semData['sem'];
        $sy = $semData['sy'];

        // 3. Update the record
        $update_sql = "UPDATE supply_request SET item_number = $item_num, semester = '" . $conn->real_escape_string($semester) . "', school_year = '" . $conn->real_escape_string($sy) . "' WHERE request_id = $req_id";
        if (!$conn->query($update_sql)) {
            echo "  Error updating request $req_id: " . $conn->error . "\n";
        }
    }
    echo "  Updated " . array_sum($counters) . " requests for $dept.\n";
}

echo "Backfill Complete.\n";
?>
