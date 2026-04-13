<?php
$file = 'c:\\xampp\\htdocs\\darts\\pages\\other_property_logs.php';
$content = file_get_contents($file);

// Replace lines that encode 'capacity' in the viewAirconDetails onclick during ajax
$patterns = [
    '/\. htmlspecialchars\(json_encode\(\$row\[\'capacity\'\] \?\? \'\'\), ENT_QUOTES, \'UTF-8\'\) \. \', \'(\s*)/',
    '/\. htmlspecialchars\(json_encode\(\$row\[\'maintenance_schedule\'\] \?\? \'\'\), ENT_QUOTES, \'UTF-8\'\) \. \', \'(\s*)/',
    '/\. htmlspecialchars\(json_encode\(\$row\[\'installation_date\'\] \?\? \'\'\), ENT_QUOTES, \'UTF-8\'\) \. \', \'(\s*)/',
    '/\. htmlspecialchars\(json_encode\(\$row\[\'energy_efficiency_rating\'\] \?\? \'\'\), ENT_QUOTES, \'UTF-8\'\) \. \', \'(\s*)/',
    '/\. htmlspecialchars\(json_encode\(\$row\[\'power_consumption\'\] \?\? \'\'\), ENT_QUOTES, \'UTF-8\'\) \. \', \'(\s*)/',
];

$content = preg_replace($patterns, "", $content);

file_put_contents($file, $content);
echo "Cleanup Ajax done 2.";
