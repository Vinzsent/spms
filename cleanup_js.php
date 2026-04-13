<?php
$content = file_get_contents('c:\\xampp\\htdocs\\darts\\pages\\other_property_logs.php');

// Fix openEditAirconModal args
$orig_open_edit = <<<EOD
            function openEditAirconModal(
                id, item_name, category, brand, model, type, capacity, serial_number, location, status,
                purchase_date, warranty_expiry, last_service_date, maintenance_schedule, supplier_id,
                installation_date, energy_efficient, power_consumption, notes, purchase_price, depreciated_value, picture
            )
EOD;

$new_open_edit = <<<EOD
            function openEditAirconModal(
                id, item_name, category, brand, model, type, serial_number, location, status,
                purchase_date, warranty_expiry, last_service_date, supplier_id,
                notes, purchase_price, depreciated_value, picture
            )
EOD;
$content = str_replace($orig_open_edit, $new_open_edit, $content);

// Remove capacity etc inside openEditAirconModal
$content = preg_replace("/document\.getElementById\('edit_capacity'\)\.value = capacity \|\| '';/", "", $content);
$content = preg_replace("/document\.getElementById\('edit_maintenance_schedule'\)\.value = formatDate\(maintenance_schedule\);/", "", $content);
$content = preg_replace("/document\.getElementById\('edit_installation_date'\)\.value = formatDate\(installation_date\);/", "", $content);
$content = preg_replace("/document\.getElementById\('edit_energy_efficient'\)\.value = energy_efficient \|\| '';/", "", $content);
$content = preg_replace("/document\.getElementById\('edit_power_consumption'\)\.value = power_consumption \|\| '';/", "", $content);

// Fix viewAirconDetails args
$orig_view = <<<EOD
                function viewAirconDetails(airconId, itemNumber, brand, model, type, capacity, serialNumber, location, status,
                    purchaseDate, warrantyExpiry, lastServiceDate, maintenanceSchedule, supplierInfo, installationDate,
                    energyEfficiency, powerConsumption, notes, purchasePrice, depreciatedValue, receiver, createdBy, dateCreated, picture) {
EOD;

$new_view = <<<EOD
                function viewAirconDetails(airconId, itemNumber, brand, model, type, serialNumber, location, status,
                    purchaseDate, warrantyExpiry, lastServiceDate, supplierInfo,
                    notes, purchasePrice, depreciatedValue, receiver, createdBy, dateCreated, picture) {
EOD;
$content = str_replace($orig_view, $new_view, $content);

// Remove capacity etc inside viewAirconDetails
$content = preg_replace("/document\.getElementById\('view_capacity'\)\.textContent = capacity \|\| 'N\/A';/", "", $content);
$content = preg_replace("/document\.getElementById\('view_maintenance_schedule'\)\.textContent = maintenanceSchedule \|\| 'N\/A';/", "", $content);
$content = preg_replace("/document\.getElementById\('view_installation_date'\)\.textContent = installationDate \? formatDate\(installationDate\) : 'N\/A';/", "", $content);
$content = preg_replace("/document\.getElementById\('view_energy_efficiency_rating'\)\.textContent = energyEfficiency \|\| 'N\/A';/", "", $content);
$content = preg_replace("/document\.getElementById\('view_power_consumption'\)\.textContent = powerConsumption \|\| 'N\/A';/", "", $content);

// Ensure the onclicks match exactly.
// I will just use regex to fix the echo '<button' blocks for view and edit.

file_put_contents('c:\\xampp\\htdocs\\darts\\pages\\other_property_logs.php', $content);
echo "Cleanup JS done.";
