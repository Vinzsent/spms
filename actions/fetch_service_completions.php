<?php
include '../includes/db.php';

$search = $_GET['search'] ?? '';
$date_start = $_GET['ds'] ?? '';
$date_end = $_GET['de'] ?? '';
$status = $_GET['status'] ?? '';
$work = $_GET['work'] ?? '';

$sql = "SELECT * FROM service_completions WHERE 1=1";
$types = "";
$params = [];

if (!empty($search)) {
    $sql .= " AND (unit_code LIKE ? OR building LIKE ? OR room_office LIKE ?)";
    $search_param = "%$search%";
    $types .= "sss";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($date_start)) {
    $sql .= " AND date_serviced >= ?";
    $types .= "s";
    $params[] = $date_start;
}

if (!empty($date_end)) {
    $sql .= " AND date_serviced <= ?";
    $types .= "s";
    $params[] = $date_end;
}

if (!empty($status)) {
    $sql .= " AND final_status = ?";
    $types .= "s";
    $params[] = $status;
}

if (!empty($work)) {
    // work_done is stored as a JSON array of strings
    $sql .= " AND JSON_CONTAINS(work_done, JSON_QUOTE(?))";
    $types .= "s";
    $params[] = $work;
}

$sql .= " ORDER BY date_serviced DESC";

$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $work_done_arr = json_decode($row['work_done'], true) ?: [];
            $work_done_str = htmlspecialchars(implode(', ', $work_done_arr));
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['date_serviced']) . "</td>";
            echo "<td>" . htmlspecialchars($row['building'] . ' - ' . $row['room_office']) . "</td>";
            echo "<td>" . htmlspecialchars($row['unit_code']) . "</td>";
            echo "<td>" . $work_done_str . "</td>";
            echo "<td>" . htmlspecialchars($row['serviced_by']) . "</td>";
            echo "<td>" . htmlspecialchars($row['final_status']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='text-center'>No records found for the selected filters.</td></tr>";
    }
    $stmt->close();
} else {
    echo "<tr><td colspan='6' class='text-center text-danger'>Database query error.</td></tr>";
}
?>
