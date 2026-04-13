<?php
$content = file_get_contents('c:\\xampp\\htdocs\\darts\\pages\\other_property_logs.php');

$removals = [
    '<th data-label="Capacity">Capacity (BTU/hr)</th>',
    '<th data-label="Energy Efficiency Rating">Energy Efficiency Rating</th>',
    '<th data-label="Power Consumption">Power Consumption (kW)</th>',
    '<th data-label="Installation Date">Installation Date</th>',
    '<th data-label="Maintenance Schedule">Maintenance Schedule</th>',
    '<th>Capacity (BTU/hr)</th>',
    '<th>Energy Efficiency Rating</th>',
    '<th>Power Consumption (kW)</th>',
    '<th>Installation Date</th>',
    '<th>Maintenance Schedule</th>',
];
$content = str_replace($removals, '', $content);

$content = preg_replace('/<td data-label="Capacity">.*?<\/td>/s', '', $content);
$content = preg_replace('/<td data-label="Energy Efficiency Rating">.*?<\/td>/s', '', $content);
$content = preg_replace('/<td data-label="Power Consumption">.*?<\/td>/s', '', $content);
$content = preg_replace('/<td data-label="Installation Date">.*?<\/td>/s', '', $content);
$content = preg_replace('/<td data-label="Maintenance Schedule">.*?<\/td>/s', '', $content);

$content = preg_replace('/<div class="col-md-3">\s*<label class="form-label">Capacity.*?<\/div>/s', '', $content);
$content = preg_replace('/<div class="col-md-3">\s*<label class="form-label">Installation Date.*?<\/div>/s', '', $content);
$content = preg_replace('/<div class="col-md-3">\s*<label class="form-label">Energy Efficiency Rating.*?<\/div>/s', '', $content);
$content = preg_replace('/<div class="col-md-3">\s*<label class="form-label">Power Consumption.*?<\/div>/s', '', $content);
$content = preg_replace('/<div class="col-md-3">\s*<label class="form-label">Maintenance Schedule.*?<\/div>/s', '', $content);
$content = preg_replace('/<div class="col-md-4">\s*<label class="text-muted small">Capacity.*?<\/div>/s', '', $content);
$content = preg_replace('/<div class="mb-0">\s*<label class="text-muted small">Installation Date.*?<\/div>/s', '', $content);
$content = preg_replace('/<div class="col-md-4">\s*<label class="text-muted small">Maintenance Schedule.*?<\/div>/s', '', $content);
$content = preg_replace('/<!-- Technical Specifications Section -->.*?<!-- Financial Information Section -->/s', '<!-- Financial Information Section -->', $content);
$content = preg_replace('/<!-- Maintenance Section -->.*?<!-- Technical Specifications Section -->/s', '<!-- Maintenance Section Removed -->', $content);

$content = str_replace(
    [
        'data-capacity="<?= htmlspecialchars($aircon[\'capacity\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-capacity="<?= htmlspecialchars($row[\'capacity\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-maintenance-schedule="<?= htmlspecialchars($aircon[\'maintenance_schedule\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-installation-date="<?= htmlspecialchars($aircon[\'installation_date\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-energy-efficiency="<?= htmlspecialchars($aircon[\'energy_efficiency_rating\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-power-consumption="<?= htmlspecialchars($aircon[\'power_consumption\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-maintenance-schedule="<?= htmlspecialchars($row[\'maintenance_schedule\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-installation-date="<?= htmlspecialchars($row[\'installation_date\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-energy-efficiency="<?= htmlspecialchars($row[\'energy_efficiency_rating\'] ?? \'\', ENT_QUOTES) ?>"',
        'data-power-consumption="<?= htmlspecialchars($row[\'power_consumption\'] ?? \'\', ENT_QUOTES) ?>"',
        '<?= htmlspecialchars(json_encode($row[\'capacity\'] ?? \'\'), ENT_QUOTES, \'UTF-8\') ?>,',
        '<?= htmlspecialchars(json_encode($row[\'maintenance_schedule\'] ?? \'\'), ENT_QUOTES, \'UTF-8\') ?>,',
        '<?= htmlspecialchars(json_encode($row[\'installation_date\'] ?? \'\'), ENT_QUOTES, \'UTF-8\') ?>,',
        '<?= htmlspecialchars(json_encode($row[\'energy_efficiency_rating\'] ?? \'\'), ENT_QUOTES, \'UTF-8\') ?>,',
        '<?= htmlspecialchars(json_encode($row[\'power_consumption\'] ?? \'\'), ENT_QUOTES, \'UTF-8\') ?>,',
    ],
    '',
    $content
);

file_put_contents('c:\\xampp\\htdocs\\darts\\pages\\other_property_logs.php', $content);
echo "Cleanup done.";
