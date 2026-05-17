<?php
include '../includes/db.php';

$search = $_GET['search'] ?? '';
$obs = $_GET['obs'] ?? '';
$act = $_GET['act'] ?? '';

$sql = "SELECT * FROM problem_reports WHERE 1=1";
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

if (!empty($obs)) {
    $sql .= " AND JSON_CONTAINS(problem_observed, JSON_QUOTE(?))";
    $types .= "s";
    $params[] = $obs;
}

if (!empty($act)) {
    $sql .= " AND JSON_CONTAINS(initial_action, JSON_QUOTE(?))";
    $types .= "s";
    $params[] = $act;
}

$sql .= " ORDER BY date_reported DESC";

$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $problem_observed_arr = json_decode($row['problem_observed'], true) ?: [];
            $problem_observed_str = htmlspecialchars(implode(', ', $problem_observed_arr));
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['date_reported']) . "</td>";
            echo "<td>" . htmlspecialchars($row['building'] . ' - ' . $row['room_office']) . "</td>";
            echo "<td>" . $problem_observed_str . "</td>";
            echo "<td>" . htmlspecialchars($row['unit_code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['reported_by']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='text-center'>No records found for the selected filters.</td></tr>";
    }
    $stmt->close();
} else {
    echo "<tr><td colspan='5' class='text-center text-danger'>Database query error.</td></tr>";
}
?>
