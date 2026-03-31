<?php
// api/get_ph_locations.php
header('Content-Type: application/json');

// Suppress any PHP warnings/notices from appearing in output
error_reporting(0);
ini_set('display_errors', 0);

$response = [];

try {
    include '../includes/db.php';

    if (!$conn || $conn->connect_error) {
        echo json_encode($response);
        exit;
    }

    // Check if tables exist first
    $tableCheck = $conn->query("SHOW TABLES LIKE 'provinces'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT p.name as province_name, c.name as city_name 
            FROM provinces p 
            LEFT JOIN cities c ON p.province_id = c.province_id
            ORDER BY p.name ASC, c.name ASC";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $prov = $row['province_name'];
            $city = $row['city_name'];

            if (!isset($response[$prov])) {
                $response[$prov] = [];
            }

            if ($city && !empty($city)) {
                $response[$prov][] = $city;
            }
        }
    }
} catch (Exception $e) {
    // Silently fail — return empty JSON
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
