<?php
// api/get_ph_locations.php
header('Content-Type: application/json');
include '../includes/db.php';

$response = [];

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

echo json_encode($response, JSON_PRETTY_PRINT);
?>
