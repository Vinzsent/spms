<?php
$f = 'c:\\xampp\\htdocs\\darts\\pages\\other_property_logs.php';
$content = file_get_contents($f);
$content = str_replace('property_images', 'other_property_images', $content);

// Also need to use property_id instead of id in other_property_images
// "(SELECT image_path FROM other_property_images WHERE id = i.id ORDER BY id ASC LIMIT 1) as first_image" ->
// "(SELECT image_path FROM other_property_images WHERE property_id = i.id ORDER BY id ASC LIMIT 1) as first_image"

$content = str_replace(
    '(SELECT image_path FROM other_property_images WHERE id = i.id',
    '(SELECT image_path FROM other_property_images WHERE property_id = i.id',
    $content
);

file_put_contents($f, $content);
echo "Replaced.";
