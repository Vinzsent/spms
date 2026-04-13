<?php
$file = 'c:\\xampp\\htdocs\\darts\\pages\\other_property_logs.php';
$content = file_get_contents($file);

$orig_handler = <<<EOD
                        // Get all data attributes
                        const airconId = button.data('aircon-id');
                        const itemNumber = button.data('item-number') || '';
                        const brand = button.data('brand') || '';
                        const model = button.data('model') || '';
                        const type = button.data('type') || '';
                        const capacity = button.data('capacity') || '';
                        const serialNumber = button.data('serial-number') || '';
                        const location = button.data('location') || '';
                        const status = button.data('status') || '';
                        const purchaseDate = button.data('purchase-date') || '';
                        const warrantyExpiry = button.data('warranty-expiry') || '';
                        const lastServiceDate = button.data('last-service-date') || '';
                        const maintenanceSchedule = button.data('maintenance-schedule') || '';
                        const supplierInfo = button.data('supplier-info') || '';
                        const installationDate = button.data('installation-date') || '';
                        const energyEfficiency = button.data('energy-efficiency') || '';
                        const powerConsumption = button.data('power-consumption') || '';
                        const notes = button.data('notes') || '';
                        const purchasePrice = button.data('purchase-price') || '0';
                        const depreciatedValue = button.data('depreciated-value') || '0';
                        const receiver = button.data('receiver') || '';
                        const createdBy = button.data('created-by') || '';
                        const dateCreated = button.data('date-created') || '';
                        const picture = button.data('picture') || '';
                        const modalId = button.data('modal-id');

                        // Call the viewAirconDetails function
                        viewAirconDetails(
                            airconId, itemNumber, brand, model, type, capacity, serialNumber,
                            location, status, purchaseDate, warrantyExpiry, lastServiceDate,
                            maintenanceSchedule, supplierInfo, installationDate, energyEfficiency,
                            powerConsumption, notes, purchasePrice, depreciatedValue, receiver,
                            createdBy, dateCreated, picture
                        );
EOD;

$new_handler = <<<EOD
                        // Get all data attributes
                        const airconId = button.data('aircon-id');
                        const itemNumber = button.data('item-number') || '';
                        const brand = button.data('brand') || '';
                        const model = button.data('model') || '';
                        const type = button.data('type') || '';
                        const serialNumber = button.data('serial-number') || '';
                        const location = button.data('location') || '';
                        const status = button.data('status') || '';
                        const purchaseDate = button.data('purchase-date') || '';
                        const warrantyExpiry = button.data('warranty-expiry') || '';
                        const lastServiceDate = button.data('last-service-date') || '';
                        const supplierInfo = button.data('supplier-info') || '';
                        const notes = button.data('notes') || '';
                        const purchasePrice = button.data('purchase-price') || '0';
                        const depreciatedValue = button.data('depreciated-value') || '0';
                        const receiver = button.data('receiver') || '';
                        const createdBy = button.data('created-by') || '';
                        const dateCreated = button.data('date-created') || '';
                        const picture = button.data('picture') || '';
                        const modalId = button.data('modal-id');

                        // Call the viewAirconDetails function
                        viewAirconDetails(
                            airconId, itemNumber, brand, model, type, serialNumber,
                            location, status, purchaseDate, warrantyExpiry, lastServiceDate,
                            supplierInfo,
                            notes, purchasePrice, depreciatedValue, receiver,
                            createdBy, dateCreated, picture
                        );
EOD;

$content = str_replace($orig_handler, $new_handler, $content);

file_put_contents($file, $content);
echo "View details click handler mapping fixed.";
