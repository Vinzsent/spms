<?php
/**
 * Dynamic Sidebar Loader
 * This file detects the user role and includes the appropriate sidebar from the sidebar/ directory.
 */

// Normalize user type
$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_role_norm = str_replace([' ', '-'], '', strtolower($raw_user_type));

// Map role to sidebar file
$sidebar_map = [
    'supplyincharge'           => 'supply_incharge.php',
    'propertycustodian'        => 'custodian.php',
    'purchasingofficer'        => 'purchasing.php',
    'purchasingstaff'          => 'purchasing.php',
    'purcashingstaff'          => 'purchasing.php', // Typos
    'purchsingstaff'           => 'purchasing.php', // Typos
    'generalsecurityoffice'    => 'gso.php',
    'gso'                      => 'gso.php'
];

$sidebar_file = $sidebar_map[$user_role_norm] ?? null;

// Default to a basic sidebar or admin sidebar if role not found (optional)
if (!$sidebar_file && in_array($user_role_norm, ['admin', 'superadmin'])) {
    // You might want an admin-specific sidebar here, 
    // for now let's use purchasing as it has most links or create one.
    $sidebar_file = 'purchasing.php'; 
}

if ($sidebar_file) {
    // Determine path based on current directory
    $sidebar_path = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../sidebar/' : 'sidebar/';
    include_once($sidebar_path . $sidebar_file);
}
?>
