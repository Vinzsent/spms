<?php
include '../includes/db.php';

$search = $_GET['search'] ?? '';
$priority = $_GET['priority'] ?? '';
$admin_action = $_GET['admin'] ?? '';

$sql = "SELECT * FROM unresolved_units WHERE 1=1";
$types = "";
$params = [];

if (!empty($search)) {
    $sql .= " AND (unit_code LIKE ? OR report_slip_no LIKE ? OR reported_issue LIKE ?)";
    $search_param = "%$search%";
    $types .= "sss";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($priority)) {
    $sql .= " AND urgency_level = ?";
    $types .= "s";
    $params[] = strtoupper($priority);
}

if (!empty($admin_action)) {
    $sql .= " AND JSON_CONTAINS(admin_action, JSON_QUOTE(?))";
    $types .= "s";
    $params[] = $admin_action;
}

$sql .= " ORDER BY date_first_reported DESC";

$stmt = $conn->prepare($sql);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $actions_taken_arr = json_decode($row['actions_taken'], true) ?: [];
            $actions_taken_str = htmlspecialchars(implode(', ', $actions_taken_arr));
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['report_slip_no'] ?: 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['date_first_reported']) . "</td>";
            echo "<td>" . htmlspecialchars($row['reported_issue']) . "</td>";
            echo "<td>" . $actions_taken_str . "</td>";
            echo "<td>" . htmlspecialchars($row['urgency_level']) . "</td>";
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
