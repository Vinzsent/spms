<?php
// actions/migrate_ph_locations.php
include '../includes/db.php';

// Create tables
$sql1 = "CREATE TABLE IF NOT EXISTS provinces (
    province_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
)";
if (!$conn->query($sql1)) {
    die("Error creating provinces table: " . $conn->error);
}

$sql2 = "CREATE TABLE IF NOT EXISTS cities (
    city_id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (province_id) REFERENCES provinces(province_id) ON DELETE CASCADE
)";
if (!$conn->query($sql2)) {
    die("Error creating cities table: " . $conn->error);
}

// Truncate tables safely
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE cities");
$conn->query("TRUNCATE TABLE provinces");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Read JSON
$json_path = __DIR__ . '/../assets/data/ph_province_city_list.json';
if (!file_exists($json_path)) {
    die("JSON file not found.");
}

$json_data = file_get_contents($json_path);
$data = json_decode($json_data, true);

if (isset($data['Philippines'])) {
    $data = $data['Philippines'];
}

$stmt_prov = $conn->prepare("INSERT INTO provinces (name) VALUES (?)");
$stmt_city = $conn->prepare("INSERT INTO cities (province_id, name) VALUES (?, ?)");

$prov_count = 0;
$city_count = 0;

foreach ($data as $province => $cities) {
    if (empty($province)) continue;
    
    $stmt_prov->bind_param("s", $province);
    if ($stmt_prov->execute()) {
        $prov_count++;
        $prov_id = $conn->insert_id;
        foreach ($cities as $city) {
            if (empty($city)) continue;
            
            $stmt_city->bind_param("is", $prov_id, $city);
            if ($stmt_city->execute()) {
                $city_count++;
            }
        }
    } else {
        echo "Error inserting province $province: " . $stmt_prov->error . "\n";
    }
}

echo "Migration complete! Added $prov_count provinces and $city_count cities.";
?>
