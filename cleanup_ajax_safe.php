<?php
$file = 'c:\\xampp\\htdocs\\darts\\pages\\other_property_logs.php';
$lines = file($file);

$output = [];
foreach ($lines as $line) {
    if (strpos($line, "['capacity']") !== false) continue;
    if (strpos($line, "['maintenance_schedule']") !== false) continue;
    if (strpos($line, "['installation_date']") !== false) continue;
    if (strpos($line, "['energy_efficiency_rating']") !== false) continue;
    if (strpos($line, "['power_consumption']") !== false) continue;
    $output[] = $line;
}

file_put_contents($file, implode("", $output));
echo "Cleanup Ajax lines done.";
