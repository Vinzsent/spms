<?php
$pageTitle = 'Aircon Condition Management';
include '../includes/auth.php';
include '../includes/db.php';

// Handle AJAX requests for pagination
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    // This is an AJAX request, return only the table content
    $isAjax = true;
} else {
    include '../includes/header.php';
    $isAjax = false;
}

$user_type = $_SESSION['user_type'] ?? '';
$dashboard_link = ($user_type == 'Admin') ? '../dashboard.php' : '../dashboard.php';

// Helpers: parse School Year input into start/end dates (July 1 to June 30)
function parse_school_year_range($raw)
{
    $raw = trim((string)$raw);
    if ($raw === '') return [null, null];
    if (preg_match('/^(20\d{2})\s*-\s*(20\d{2})$/', $raw, $m)) {
        $startY = (int)$m[1];
        $endY = (int)$m[2];
        if ($endY === $startY + 1) {
            return [sprintf('%04d-07-01', $startY), sprintf('%04d-06-30', $endY)];
        }
    } elseif (preg_match('/^(19|20)\d{2}$/', $raw)) {
        $startY = (int)$raw;
        return [sprintf('%04d-07-01', $startY), sprintf('%04d-06-30', $startY + 1)];
    }
    return [null, null];
}

// Per-table School Year search values
$sy_inv_raw  = $_GET['sy_inv']  ?? '';
$sy_recv_raw = $_GET['sy_recv'] ?? '';
$sy_logs_raw = $_GET['sy_logs'] ?? '';

// Campus filter (BED/TED)
$campus_raw = strtoupper(trim($_GET['campus'] ?? ''));

// Search parameter
$search_term = trim($_GET['search'] ?? '');

list($sy_inv_start, $sy_inv_end)   = parse_school_year_range($sy_inv_raw);
list($sy_recv_start, $sy_recv_end) = parse_school_year_range($sy_recv_raw);
list($sy_logs_start, $sy_logs_end) = parse_school_year_range($sy_logs_raw);

// Build optional WHERE conditions per query based on per-table School Year
$inv_where_conditions = [];
$recv_where_conditions = [];
$logs_where_conditions = [];

// Removed receiver filter - not applicable for aircon management
// $inv_where_conditions[] = "i.receiver = 'Property Custodian'";
// $recv_where_conditions[] = "st.receiver = 'Property Custodian'";
// $logs_where_conditions[] = "sl.receiver = 'Property Custodian'";

// Add search filter if search term is provided
if (!empty($search_term)) {
    $search_escaped = $conn->real_escape_string($search_term);
    $inv_where_conditions[] = "(i.brand LIKE '%$search_escaped%' OR i.model LIKE '%$search_escaped%' OR i.type LIKE '%$search_escaped%' OR i.serial_number LIKE '%$search_escaped%' OR i.location LIKE '%$search_escaped%')";
}

// Add school year filters if provided
if ($sy_inv_start && $sy_inv_end) {
    $start_esc = $conn->real_escape_string($sy_inv_start);
    $end_esc   = $conn->real_escape_string($sy_inv_end);
    $inv_where_conditions[] = "i.date_created >= '$start_esc' AND i.date_created <= '$end_esc'";
}
// Add campus filter if provided (expects values BED/TED stored in campus column)
if ($campus_raw === 'BED' || $campus_raw === 'TED') {
    $campus_esc = $conn->real_escape_string($campus_raw);
    $inv_where_conditions[] = "TRIM(UPPER(i.campus)) = '$campus_esc'";
}
if ($sy_recv_start && $sy_recv_end) {
    $start_esc = $conn->real_escape_string($sy_recv_start);
    $end_esc   = $conn->real_escape_string($sy_recv_end);
    $recv_where_conditions[] = "st.date_received >= '$start_esc' AND st.date_received <= '$end_esc'";
}
if ($sy_logs_start && $sy_logs_end) {
    $start_esc = $conn->real_escape_string($sy_logs_start);
    $end_esc   = $conn->real_escape_string($sy_logs_end);
    $logs_where_conditions[] = "sl.date_created >= '$start_esc' AND sl.date_created <= '$end_esc'";
}

// Build final WHERE clauses
$inv_where = !empty($inv_where_conditions) ? ' WHERE ' . implode(' AND ', $inv_where_conditions) : '';
$recv_where = !empty($recv_where_conditions) ? ' WHERE ' . implode(' AND ', $recv_where_conditions) : '';
$logs_where = !empty($logs_where_conditions) ? ' WHERE ' . implode(' AND ', $logs_where_conditions) : '';

// Get purchased data
$sql = "SELECT pi.*, s.supplier_name 
        FROM aircons pi 
        LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id
        ORDER BY pi.date_created DESC";
$result = $conn->query($sql);

// Get inventory data
$sql = "SELECT i.*, s.supplier_name 
        FROM aircons i 
        LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
        $inv_where
        ORDER BY i.date_created DESC";
$result = $conn->query($sql);

$sql1 = "SELECT st.*, s.supplier_name
        FROM supplier_transaction st
        JOIN supplier s ON s.supplier_id = st.supplier_id
        $recv_where
        AND st.status IN ('Pending')
        ORDER BY COALESCE(st.date_received, st.date_created) DESC";
$result1 = $conn->query($sql1);

// Get suppliers for dropdown
$suppliers_sql = "SELECT supplier_id, supplier_name FROM supplier WHERE status = 'Active' ORDER BY supplier_name";
$suppliers_result = $conn->query($suppliers_sql);

// Build School Year options for dropdowns (last 10 years)
$currYear = (int)date('Y');
$minYear = $currYear - 10;
$sy_years = [];
for ($y = $currYear; $y >= $minYear; $y--) {
    $sy_years[] = $y . '-' . ($y + 1);
}

// Calculate statistics - execute the query first to get proper counts
$stats_result = $conn->query($sql);
$total_items = $stats_result ? $stats_result->num_rows : 0;

// Calculate aircon statistics by status
$operational_count = 0;
$needs_repair_count = 0;
$under_maintenance_count = 0;
$decommissioned_count = 0;
$working_count = 0;
$na_count = 0;

$campus_clause = '';
if ($campus_raw === 'BED' || $campus_raw === 'TED') {
    $campus_esc_for_counts = $conn->real_escape_string($campus_raw);
    $campus_clause = " WHERE campus = '$campus_esc_for_counts'";
}

$status_sql = "SELECT status, COUNT(*) as count FROM aircons" . $campus_clause . " GROUP BY status";
$status_result = $conn->query($status_sql);
if ($status_result) {
    while ($row = $status_result->fetch_assoc()) {
        $status = $row['status'];
        $count = (int)$row['count'];

        switch ($status) {
            case 'Operational':
                $operational_count = $count;
                break;
            case 'Working':
                $working_count = $count;
                break;
            case 'Needs Repair':
                $needs_repair_count = $count;
                break;
            case 'Under Maintenance':
                $under_maintenance_count = $count;
                break;
            case 'Decommissioned':
                $decommissioned_count = $count;
                break;
            case 'N/A':
                $na_count = $count;
                break;
        }
    }
}

// Calculate combined counts for display
$good_condition_count = $operational_count + $working_count; // Operational + Working
$attention_needed_count = $needs_repair_count + $under_maintenance_count; // Needs Repair + Under Maintenance

// Fetch aircons by condition for modals
$good_condition_aircons = [];
$needs_attention_aircons = [];
$decommissioned_aircons = [];

// Get good condition aircons (Operational + Working)
$good_sql = "SELECT * FROM aircons WHERE status IN ('Operational', 'Working')"
    . ($campus_clause ? str_replace(' WHERE ', ' AND ', $campus_clause) : '')
    . " ORDER BY location, brand";
$good_result = $conn->query($good_sql);
if ($good_result) {
    while ($row = $good_result->fetch_assoc()) {
        $good_condition_aircons[] = $row;
    }
}

// Get aircons needing attention (Needs Repair + Under Maintenance)
$attention_sql = "SELECT * FROM aircons WHERE status IN ('Needs Repair', 'Under Maintenance')"
    . ($campus_clause ? str_replace(' WHERE ', ' AND ', $campus_clause) : '')
    . " ORDER BY location, brand";
$attention_result = $conn->query($attention_sql);
if ($attention_result) {
    while ($row = $attention_result->fetch_assoc()) {
        $needs_attention_aircons[] = $row;
    }
}

// Get decommissioned aircons
$decom_sql = "SELECT * FROM aircons WHERE status = 'Decommissioned'"
    . ($campus_clause ? str_replace(' WHERE ', ' AND ', $campus_clause) : '')
    . " ORDER BY location, brand";
$decom_result = $conn->query($decom_sql);
if ($decom_result) {
    while ($row = $decom_result->fetch_assoc()) {
        $decommissioned_aircons[] = $row;
    }
}

// Store session messages for modal display
$session_message = '';
$session_error = '';
if (isset($_SESSION['message'])) {
    $session_message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $session_error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch all category data for dropdown
$categories_query = "
    SELECT 
        at.id as category_id,
        at.name as main_category,
        sc.name as subcategory,
        ssc.name as sub_subcategory,
        sssc.name as sub_sub_subcategory
    FROM account_types at
    LEFT JOIN account_subcategories sc ON at.id = sc.parent_id
    LEFT JOIN account_sub_subcategories ssc ON sc.id = ssc.subcategory_id
    LEFT JOIN account_sub_sub_subcategories sssc ON ssc.id = sssc.sub_subcategory_id
    WHERE at.id BETWEEN 14 AND 21
    ORDER BY at.id, sc.name, ssc.name, sssc.name
";
$categories_result = $conn->query($categories_query);

// Organize categories hierarchically
$organized_categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $main = $row['main_category'];
        if (!isset($organized_categories[$main])) {
            $organized_categories[$main] = [];
        }

        if (!empty($row['subcategory'])) {
            $sub = $row['subcategory'];
            if (!in_array($sub, $organized_categories[$main])) {
                $organized_categories[$main][] = $sub;
            }
        }

        if (!empty($row['sub_subcategory'])) {
            $subsub = $row['sub_subcategory'];
            if (!in_array($subsub, $organized_categories[$main])) {
                $organized_categories[$main][] = $subsub;
            }
        }

        if (!empty($row['sub_sub_subcategory'])) {
            $subsubsub = $row['sub_sub_subcategory'];
            if (!in_array($subsubsub, $organized_categories[$main])) {
                $organized_categories[$main][] = $subsubsub;
            }
        }
    }
}

// Fetch all category data for dropdown
$categories_query = "
    SELECT 
        at.id as category_id,
        at.name as main_category,
        sc.name as subcategory,
        ssc.name as sub_subcategory,
        sssc.name as sub_sub_subcategory
    FROM account_types at
    LEFT JOIN account_subcategories sc ON at.id = sc.parent_id
    LEFT JOIN account_sub_subcategories ssc ON sc.id = ssc.subcategory_id
    LEFT JOIN account_sub_sub_subcategories sssc ON ssc.id = sssc.sub_subcategory_id
    WHERE at.id BETWEEN 14 AND 21
    ORDER BY at.id, sc.name, ssc.name, sssc.name
";
$categories_result = $conn->query($categories_query);

// Organize categories hierarchically
$organized_categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $main = $row['main_category'];
        if (!isset($organized_categories[$main])) {
            $organized_categories[$main] = [];
        }

        if (!empty($row['subcategory'])) {
            $sub = $row['subcategory'];
            if (!in_array($sub, $organized_categories[$main])) {
                $organized_categories[$main][] = $sub;
            }
        }

        if (!empty($row['sub_subcategory'])) {
            $subsub = $row['sub_subcategory'];
            if (!in_array($subsub, $organized_categories[$main])) {
                $organized_categories[$main][] = $subsub;
            }
        }

        if (!empty($row['sub_sub_subcategory'])) {
            $subsubsub = $row['sub_sub_subcategory'];
            if (!in_array($subsubsub, $organized_categories[$main])) {
                $organized_categories[$main][] = $subsubsub;
            }
        }
    }
}

?>

<?php if (!$isAjax): ?>
    <style>
        :root {
            --primary-green: #073b1d;
            --dark-green: #073b1d;
            --light-green: #2d8aad;
            --accent-orange: #EACA26;
            --accent-blue: #4a90e2;
            --accent-red: #e74c3c;
            --accent-yellow: #f39c12;
            --text-white: #ffffff;
            --text-dark: #073b1d;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
        }

        .stock-icons-btn {
            background: linear-gradient(to right, #28a745 50%, #ffc107 50%);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .stock-icons-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .stock-icons-btn i {
            font-weight: bold;
            margin: 0 2px;
        }

        .stock-icons-btn i:first-child {
            /* plus icon */
            color: #ffffff;
        }

        .stock-icons-btn i:last-child {
            /* minus icon */
            color: #000000;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--text-white);
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-white);
        }

        .welcome-text {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            padding: 0;
            margin: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: var(--text-white);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text-white);
            border-left-color: var(--accent-orange);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: var(--accent-orange);
            font-weight: 600;
        }

        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .nav-link.logout {
            color: var(--accent-red);
            margin-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            min-height: 100vh;
            background-color: var(--bg-light);
        }

        .content-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--text-white);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .content-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2.2rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--text-white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.clickable {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .stat-card.clickable:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.clickable:active {
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: var(--text-white);
        }

        .stat-icon.items {
            background-color: var(--primary-green);
        }

        .stat-icon.low-stock {
            background-color: var(--accent-yellow);
        }

        .stat-icon.out-of-stock {
            background-color: var(--accent-red);
        }

        .stat-icon.movements {
            background-color: var(--accent-blue);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Alert Styles */
        .alert-card {
            background: linear-gradient(135deg, var(--accent-red) 0%, #c0392b 100%);
            color: var(--text-white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-card.warning {
            background: linear-gradient(135deg, var(--accent-yellow) 0%, #e67e22 100%);
        }

        /* Session Alert Styles */
        .alert {
            margin-bottom: 20px;
            margin-left: 0;
            margin-right: 0;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-left: 4px solid #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-left: 4px solid #721c24;
        }

        .alert .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .alert .btn-close:hover {
            opacity: 1;
        }

        .alert .flex-grow-1 {
            min-width: 0;
            word-break: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            max-width: calc(100% - 60px);
            padding-right: 10px;
        }

        /* Ensure alerts have proper spacing from sidebar on larger screens */
        @media (min-width: 769px) {
            .alert {
                margin-left: 0;
                margin-right: 0;
                padding-left: 20px;
                padding-right: 20px;
            }

            .alert .flex-grow-1 {
                max-width: calc(100% - 80px);
                padding-right: 15px;
            }
        }

        @media (max-width: 768px) {
            .alert {
                font-size: 0.9rem;
                padding: 12px 15px;
            }

            .alert .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .alert .btn-close {
                align-self: flex-end;
                margin-top: -10px;
                margin-right: -10px;
            }
        }

        /* Table Styles */
        .table-container {
            background: var(--text-white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: var(--text-white);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .btn-add {
            background-color: var(--accent-orange);
            border: none;
            color: var(--text-white);
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            background-color: #e55a2b;
            transform: translateY(-2px);
        }

        /* Stock Level Indicators */
        .stock-level {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-level.critical {
            background-color: var(--accent-red);
            color: var(--text-white);
        }

        .stock-level.low {
            background-color: var(--accent-yellow);
            color: var(--text-dark);
        }

        .stock-level.normal {
            background-color: var(--accent-blue);
            color: var(--text-white);
        }

        .stock-level.out {
            background-color: #6c757d;
            color: var(--text-white);
        }

        /* Movement Button Styles */
        .movement-btn {
            transition: all 0.3s ease;
            border-width: 2px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .movement-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .movement-btn.active {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            border-width: 3px;
        }

        .movement-btn.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 0.3;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 0.3;
            }
        }

        .movement-btn.btn-outline-success {
            border-color: #198754;
            color: #198754;
            background-color: rgba(25, 135, 84, 0.1);
        }

        .movement-btn.btn-outline-warning {
            border-color: #ffc107;
            color: #856404;
            background-color: rgba(255, 193, 7, 0.1);
        }

        /* Search Input Styles */
        .search-input {
            min-width: 200px;
        }

        .search-input input {
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .search-input input:focus {
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }

        /* Loading indicator for search input */
        .search-input input.loading {
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            animation: spin 1s linear infinite;
            padding-right: 35px;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .btn-search {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .btn-search:hover {
            background-color: #357abd;
            border-color: #357abd;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .alert {
                margin-left: 10px;
                margin-right: 10px;
                font-size: 0.9rem;
                padding: 12px 15px;
            }

            .alert .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .alert .btn-close {
                align-self: flex-end;
                margin-top: -10px;
                margin-right: -10px;
            }

            .alert .flex-grow-1 {
                max-width: 100%;
                margin-right: 30px;
            }
        }
    </style>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>DARTS</h3>
            <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav-item">
                <li><a href="<?= $dashboard_link ?>" class="nav-link">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a></li>
                <li><a href="office_inventory.php" class="nav-link">
                        <i class="fas fa-building"></i> Office Inventory
                    </a></li>
                <li><a href="property_inventory.php" class="nav-link">
                        <i class="fas fa-boxes"></i> Property Inventory
                    </a></li>
                <li><a href="rooms_inventory.php" class="nav-link">
                        <i class="fas fa-door-open"></i> Rooms Inventory
                    </a></li>
                <li><a href="property_issuance.php" class="nav-link">
                        <i class="fas fa-hand-holding"></i> Property Issuance
                    </a></li>
                <li><a href="equipment_transfer_request.php" class="nav-link">
                        <i class="fas fa-exchange-alt"></i> Transfer Request
                    </a></li>
                <li><a href="borrowers_forms.php" class="nav-link">
                        <i class="fas fa-hand-holding"></i> Borrower Forms
                    </a></li>
                <li><a href="aircon_list.php" class="nav-link">
                        <i class="fas fa-snowflake"></i> Aircons
                    </a></li>
                <li><a href="property_release_logs.php" class="nav-link active">
                        <i class="fas fa-file"></i> Release Logs
                    </a></li>
                <li><a href="../logout.php" class="nav-link logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
                </a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h1>Release Logs</h1>
            <p>Track release property logs, pass property movement, and manage property details</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon items">
                    <i class="fas fa-snowflake"></i>
                </div>
                <div class="stat-number"><?= $total_items ?></div>
                <div class="stat-label">Total Aircon Units</div>
            </div>

            <div class="stat-card clickable" style="border-left: 4px solid #28a745; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#goodConditionModal" title="Click to view good condition aircons">
                <div class="stat-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number" style="color: #28a745;"><?= $good_condition_count ?></div>
                <div class="stat-label">Good Condition</div>
                <small class="text-muted mt-1"><i class="fas fa-eye"></i> Click to view details</small>
            </div>

            <div class="stat-card clickable" style="border-left: 4px solid #ffc107; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#needsAttentionModal" title="Click to view aircons needing attention">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number" style="color: #ffc107;"><?= $attention_needed_count ?></div>
                <div class="stat-label">Needs Attention</div>
                <small class="text-muted mt-1"><i class="fas fa-eye"></i> Click to view details</small>
            </div>

            <div class="stat-card clickable" style="border-left: 4px solid #dc3545; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#decommissionedModal" title="Click to view decommissioned aircons">
                <div class="stat-icon" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number" style="color: #dc3545;"><?= $decommissioned_count ?></div>
                <div class="stat-label">Decommissioned</div>
                <small class="text-muted mt-1"><i class="fas fa-eye"></i> Click to view details</small>
            </div>
        </div>


        <!-- Low Stock Items Modal -->
        <div class="modal fade" id="lowStockModal" tabindex="-1" aria-labelledby="lowStockModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white;">
                        <h5 class="modal-title" id="lowStockModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Items
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($low_stock_items)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong><?= count($low_stock_items) ?></strong> item(s) are running low on stock. Consider reordering soon.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-warning">
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Unit</th>
                                            <th>Supplier</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($low_stock_items as $item): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                                                <td><?= htmlspecialchars($item['category']) ?></td>
                                                <td>
                                                    <span class="badge bg-warning text-dark">
                                                        <?= $item['current_stock'] ?>
                                                    </span>
                                                </td>
                                                <td><?= $item['reorder_level'] ?></td>
                                                <td><?= htmlspecialchars($item['unit']) ?></td>
                                                <td><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></td>
                                                <td><?= date('M d, Y', strtotime($item['date_updated'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-success" onclick="stockIn(<?= $item['inventory_id'] ?>); $('#lowStockModal').modal('hide');" title="Add Stock">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-info" onclick="editInventoryItem(<?= (int)$item['inventory_id'] ?>, 'lowStockModal');" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h4>All Good!</h4>
                                <p class="text-muted">No items are running low on stock.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Out of Stock Items Modal -->
        <div class="modal fade" id="outOfStockModal" tabindex="-1" aria-labelledby="outOfStockModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white;">
                        <h5 class="modal-title" id="outOfStockModalLabel">
                            <i class="fas fa-times-circle me-2"></i>Out of Stock Items
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($out_of_stock_items)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong><?= count($out_of_stock_items) ?></strong> item(s) are completely out of stock. Immediate action required!
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Unit</th>
                                            <th>Supplier</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($out_of_stock_items as $item): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                                                <td><?= htmlspecialchars($item['category']) ?></td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        <?= $item['current_stock'] ?>
                                                    </span>
                                                </td>
                                                <td><?= $item['reorder_level'] ?></td>
                                                <td><?= htmlspecialchars($item['unit']) ?></td>
                                                <td><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></td>
                                                <td><?= date('M d, Y', strtotime($item['date_updated'])) ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-success" onclick="stockIn(<?= $item['inventory_id'] ?>); $('#outOfStockModal').modal('hide');" title="Add Stock">
                                                        <i class="fas fa-plus"></i> Restock
                                                    </button>
                                                    <button class="btn btn-sm btn-info" onclick="editInventoryItem(<?= (int)$item['inventory_id'] ?>, 'outOfStockModal');" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h4>Excellent!</h4>
                                <p class="text-muted">No items are out of stock.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Good Condition Aircons Modal -->
        <div class="modal fade" id="goodConditionModal" tabindex="-1" aria-labelledby="goodConditionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                        <h5 class="modal-title" id="goodConditionModalLabel">
                            <i class="fas fa-check-circle me-2"></i>Good Condition Aircons
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($good_condition_aircons)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong><?= count($good_condition_aircons) ?></strong> aircon unit(s) are in good working condition.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Brand</th>
                                            <th>Model</th>
                                            <th>Type</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Serial No.</th>
                                            <th>Last Service</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($good_condition_aircons as $aircon): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($aircon['brand'] ?? 'N/A') ?></strong></td>
                                                <td><?= htmlspecialchars($aircon['model'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($aircon['type'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($aircon['location'] ?? 'N/A') ?></td>
                                                <td><span class="badge bg-success"><?= htmlspecialchars($aircon['status']) ?></span></td>
                                                <td><?= htmlspecialchars($aircon['serial_number'] ?? 'N/A') ?></td>
                                                <td><?= !empty($aircon['last_service_date']) ? date('M d, Y', strtotime($aircon['last_service_date'])) : 'N/A' ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary view-aircon-details-btn"
                                                        title="View Details"
                                                        data-aircon-id="<?= (int)$aircon['aircon_id'] ?>"
                                                        data-item-number="<?= htmlspecialchars($aircon['item_number'] ?? '', ENT_QUOTES) ?>"
                                                        data-brand="<?= htmlspecialchars($aircon['brand'] ?? '', ENT_QUOTES) ?>"
                                                        data-model="<?= htmlspecialchars($aircon['model'] ?? '', ENT_QUOTES) ?>"
                                                        data-type="<?= htmlspecialchars($aircon['type'] ?? '', ENT_QUOTES) ?>"
                                                        data-capacity="<?= htmlspecialchars($aircon['capacity'] ?? '', ENT_QUOTES) ?>"
                                                        data-serial-number="<?= htmlspecialchars($aircon['serial_number'] ?? '', ENT_QUOTES) ?>"
                                                        data-location="<?= htmlspecialchars($aircon['location'] ?? '', ENT_QUOTES) ?>"
                                                        data-status="<?= htmlspecialchars($aircon['status'] ?? '', ENT_QUOTES) ?>"
                                                        data-purchase-date="<?= htmlspecialchars($aircon['purchase_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-warranty-expiry="<?= htmlspecialchars($aircon['warranty_expiry'] ?? '', ENT_QUOTES) ?>"
                                                        data-last-service-date="<?= htmlspecialchars($aircon['last_service_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-maintenance-schedule="<?= htmlspecialchars($aircon['maintenance_schedule'] ?? '', ENT_QUOTES) ?>"
                                                        data-supplier-info="<?= htmlspecialchars($aircon['supplier_name'] ?? '', ENT_QUOTES) ?>"
                                                        data-installation-date="<?= htmlspecialchars($aircon['installation_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-energy-efficiency="<?= htmlspecialchars($aircon['energy_efficiency_rating'] ?? '', ENT_QUOTES) ?>"
                                                        data-power-consumption="<?= htmlspecialchars($aircon['power_consumption'] ?? '', ENT_QUOTES) ?>"
                                                        data-notes="<?= htmlspecialchars($aircon['notes'] ?? '', ENT_QUOTES) ?>"
                                                        data-purchase-price="<?= htmlspecialchars($aircon['purchase_price'] ?? '0', ENT_QUOTES) ?>"
                                                        data-depreciated-value="<?= htmlspecialchars($aircon['depreciated_value'] ?? '0', ENT_QUOTES) ?>"
                                                        data-receiver="<?= htmlspecialchars($aircon['receiver'] ?? '', ENT_QUOTES) ?>"
                                                        data-created-by="<?= htmlspecialchars($aircon['created_by'] ?? '', ENT_QUOTES) ?>"
                                                        data-date-created="<?= htmlspecialchars($aircon['date_created'] ?? '', ENT_QUOTES) ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-snowflake fa-4x text-muted mb-3"></i>
                                <h4>No Aircons</h4>
                                <p class="text-muted">No aircons in good condition found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Needs Attention Aircons Modal -->
        <div class="modal fade" id="needsAttentionModal" tabindex="-1" aria-labelledby="needsAttentionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white;">
                        <h5 class="modal-title" id="needsAttentionModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Aircons Needing Attention
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($needs_attention_aircons)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong><?= count($needs_attention_aircons) ?></strong> aircon unit(s) need repair or maintenance attention.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-warning">
                                        <tr>
                                            <th>Brand</th>
                                            <th>Model</th>
                                            <th>Type</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Serial No.</th>
                                            <th>Last Service</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($needs_attention_aircons as $aircon): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($aircon['brand'] ?? 'N/A') ?></strong></td>
                                                <td><?= htmlspecialchars($aircon['model'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($aircon['type'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($aircon['location'] ?? 'N/A') ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $aircon['status'] == 'Needs Repair' ? 'warning' : 'info' ?>">
                                                        <?= htmlspecialchars($aircon['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($aircon['serial_number'] ?? 'N/A') ?></td>
                                                <td><?= !empty($aircon['last_service_date']) ? date('M d, Y', strtotime($aircon['last_service_date'])) : 'N/A' ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary view-aircon-details-btn"
                                                        title="View Details"
                                                        data-aircon-id="<?= (int)$aircon['aircon_id'] ?>"
                                                        data-item-number="<?= htmlspecialchars($aircon['item_number'] ?? '', ENT_QUOTES) ?>"
                                                        data-brand="<?= htmlspecialchars($aircon['brand'] ?? '', ENT_QUOTES) ?>"
                                                        data-model="<?= htmlspecialchars($aircon['model'] ?? '', ENT_QUOTES) ?>"
                                                        data-type="<?= htmlspecialchars($aircon['type'] ?? '', ENT_QUOTES) ?>"
                                                        data-capacity="<?= htmlspecialchars($aircon['capacity'] ?? '', ENT_QUOTES) ?>"
                                                        data-serial-number="<?= htmlspecialchars($aircon['serial_number'] ?? '', ENT_QUOTES) ?>"
                                                        data-location="<?= htmlspecialchars($aircon['location'] ?? '', ENT_QUOTES) ?>"
                                                        data-status="<?= htmlspecialchars($aircon['status'] ?? '', ENT_QUOTES) ?>"
                                                        data-purchase-date="<?= htmlspecialchars($aircon['purchase_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-warranty-expiry="<?= htmlspecialchars($aircon['warranty_expiry'] ?? '', ENT_QUOTES) ?>"
                                                        data-last-service-date="<?= htmlspecialchars($aircon['last_service_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-maintenance-schedule="<?= htmlspecialchars($aircon['maintenance_schedule'] ?? '', ENT_QUOTES) ?>"
                                                        data-supplier-info="<?= htmlspecialchars($aircon['supplier_name'] ?? '', ENT_QUOTES) ?>"
                                                        data-installation-date="<?= htmlspecialchars($aircon['installation_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-energy-efficiency="<?= htmlspecialchars($aircon['energy_efficiency_rating'] ?? '', ENT_QUOTES) ?>"
                                                        data-power-consumption="<?= htmlspecialchars($aircon['power_consumption'] ?? '', ENT_QUOTES) ?>"
                                                        data-notes="<?= htmlspecialchars($aircon['notes'] ?? '', ENT_QUOTES) ?>"
                                                        data-purchase-price="<?= htmlspecialchars($aircon['purchase_price'] ?? '0', ENT_QUOTES) ?>"
                                                        data-depreciated-value="<?= htmlspecialchars($aircon['depreciated_value'] ?? '0', ENT_QUOTES) ?>"
                                                        data-receiver="<?= htmlspecialchars($aircon['receiver'] ?? '', ENT_QUOTES) ?>"
                                                        data-created-by="<?= htmlspecialchars($aircon['created_by'] ?? '', ENT_QUOTES) ?>"
                                                        data-date-created="<?= htmlspecialchars($aircon['date_created'] ?? '', ENT_QUOTES) ?>"
                                                        data-modal-id="needsAttentionModal">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h4>All Good!</h4>
                                <p class="text-muted">No aircons need repair or maintenance attention.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decommissioned Aircons Modal -->
        <div class="modal fade" id="decommissionedModal" tabindex="-1" aria-labelledby="decommissionedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white;">
                        <h5 class="modal-title" id="decommissionedModalLabel">
                            <i class="fas fa-times-circle me-2"></i>Decommissioned Aircons
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($decommissioned_aircons)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong><?= count($decommissioned_aircons) ?></strong> aircon unit(s) have been decommissioned and are out of service.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-danger">
                                        <tr>
                                            <th>Brand</th>
                                            <th>Model</th>
                                            <th>Type</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Serial No.</th>
                                            <th>Last Service</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($decommissioned_aircons as $aircon): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($aircon['brand'] ?? 'N/A') ?></strong></td>
                                                <td><?= htmlspecialchars($aircon['model'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($aircon['type'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($aircon['location'] ?? 'N/A') ?></td>
                                                <td><span class="badge bg-danger"><?= htmlspecialchars($aircon['status']) ?></span></td>
                                                <td><?= htmlspecialchars($aircon['serial_number'] ?? 'N/A') ?></td>
                                                <td><?= !empty($aircon['last_service_date']) ? date('M d, Y', strtotime($aircon['last_service_date'])) : 'N/A' ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary view-aircon-details-btn"
                                                        title="View Details"
                                                        data-aircon-id="<?= (int)$aircon['aircon_id'] ?>"
                                                        data-item-number="<?= htmlspecialchars($aircon['item_number'] ?? '', ENT_QUOTES) ?>"
                                                        data-brand="<?= htmlspecialchars($aircon['brand'] ?? '', ENT_QUOTES) ?>"
                                                        data-model="<?= htmlspecialchars($aircon['model'] ?? '', ENT_QUOTES) ?>"
                                                        data-type="<?= htmlspecialchars($aircon['type'] ?? '', ENT_QUOTES) ?>"
                                                        data-capacity="<?= htmlspecialchars($aircon['capacity'] ?? '', ENT_QUOTES) ?>"
                                                        data-serial-number="<?= htmlspecialchars($aircon['serial_number'] ?? '', ENT_QUOTES) ?>"
                                                        data-location="<?= htmlspecialchars($aircon['location'] ?? '', ENT_QUOTES) ?>"
                                                        data-status="<?= htmlspecialchars($aircon['status'] ?? '', ENT_QUOTES) ?>"
                                                        data-purchase-date="<?= htmlspecialchars($aircon['purchase_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-warranty-expiry="<?= htmlspecialchars($aircon['warranty_expiry'] ?? '', ENT_QUOTES) ?>"
                                                        data-last-service-date="<?= htmlspecialchars($aircon['last_service_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-maintenance-schedule="<?= htmlspecialchars($aircon['maintenance_schedule'] ?? '', ENT_QUOTES) ?>"
                                                        data-supplier-info="<?= htmlspecialchars($aircon['supplier_name'] ?? '', ENT_QUOTES) ?>"
                                                        data-installation-date="<?= htmlspecialchars($aircon['installation_date'] ?? '', ENT_QUOTES) ?>"
                                                        data-energy-efficiency="<?= htmlspecialchars($aircon['energy_efficiency_rating'] ?? '', ENT_QUOTES) ?>"
                                                        data-power-consumption="<?= htmlspecialchars($aircon['power_consumption'] ?? '', ENT_QUOTES) ?>"
                                                        data-notes="<?= htmlspecialchars($aircon['notes'] ?? '', ENT_QUOTES) ?>"
                                                        data-purchase-price="<?= htmlspecialchars($aircon['purchase_price'] ?? '0', ENT_QUOTES) ?>"
                                                        data-depreciated-value="<?= htmlspecialchars($aircon['depreciated_value'] ?? '0', ENT_QUOTES) ?>"
                                                        data-receiver="<?= htmlspecialchars($aircon['receiver'] ?? '', ENT_QUOTES) ?>"
                                                        data-created-by="<?= htmlspecialchars($aircon['created_by'] ?? '', ENT_QUOTES) ?>"
                                                        data-date-created="<?= htmlspecialchars($aircon['date_created'] ?? '', ENT_QUOTES) ?>"
                                                        data-modal-id="decommissionedModal">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h4>Excellent!</h4>
                                <p class="text-muted">No aircons have been decommissioned.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <style>
            /* Responsive table styles */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Reduce table header font size */
            .table-dark th {
                font-size: 0.85rem;
            }

            /* Action buttons styling */
            .table .actions {
                display: flex;
                gap: 0.5rem;
                justify-content: center;
                align-items: center;
            }

            .table .actions .btn {
                margin: 0;
            }

            @media (max-width: 991.98px) {

                .table th,
                .table td {
                    white-space: nowrap;
                    min-width: 120px;
                }

                .table thead {
                    display: none;
                }

                .table,
                .table tbody,
                .table tr,
                .table td {
                    display: block;
                    width: 100%;
                }

                .table tr {
                    margin-bottom: 1rem;
                    border: 1px solid #dee2e6;
                    border-radius: 0.25rem;
                    position: relative;
                    padding-top: 2.5rem;
                }

                .table td {
                    text-align: right;
                    padding-left: 50%;
                    position: relative;
                    border-bottom: 1px solid #dee2e6;
                }

                .table td::before {
                    content: attr(data-label);
                    position: absolute;
                    left: 1rem;
                    width: 45%;
                    padding-right: 1rem;
                    text-align: left;
                    font-weight: bold;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .table .actions {
                    display: flex;
                    justify-content: flex-end;
                    padding: 0.5rem;
                    border-bottom: none;
                }

                .table .actions::before {
                    display: none;
                }

                .table .btn-group {
                    flex-wrap: nowrap;
                }

                .table .btn {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.8rem;
                }
            }

            /* Reduce table text size for compactness */
            .table {
                font-size: 0.875rem;
                /* 14px */
            }

            .table thead th {
                font-size: 0.875rem;
                /* 14px */
                padding: 0.5rem;
            }

            .table tbody td {
                font-size: 0.813rem;
                /* 13px */
                padding: 0.5rem;
            }

            .table .badge {
                font-size: 0.75rem;
                /* 12px */
                padding: 0.25rem 0.5rem;
            }

            .table .btn-sm {
                font-size: 0.75rem;
                /* 12px */
                padding: 0.25rem 0.4rem;
            }
        </style>

        <!-- Aircon Table -->
        <div class="table-container">
            <div class="table-header">
                <h3>Release Logs</h3>
                <div class="d-flex align-items-end gap-2">
                    <form method="GET" class="d-flex align-items-end gap-2 mb-0">
                        <div class="search-input">
                            <label for="search" class="form-label mb-0 text-white">Search Aircon</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Search by brand, model, type, serial no., location..." value="<?= htmlspecialchars($search_term) ?>">
                        </div>
                        <div>
                            <label for="sy_inv" class="form-label mb-0 text-white">School Year</label>
                            <select id="sy_inv" name="sy_inv" class="form-select">
                                <option value="">All</option>
                                <?php foreach ($sy_years as $sy): ?>
                                    <option value="<?= htmlspecialchars($sy) ?>" <?= ($sy_inv_raw === $sy) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sy) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="campus" class="form-label mb-0 text-white">Campus</label>
                            <select id="campus" name="campus" class="form-select">
                                <option value="">All</option>
                                <option value="BED" <?= ($campus_raw === 'BED') ? 'selected' : '' ?>>BED</option>
                                <option value="TED" <?= ($campus_raw === 'TED') ? 'selected' : '' ?>>TED</option>
                            </select>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="btn btn-search">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if (!empty($search_term) || !empty($sy_inv_raw) || !empty($campus_raw)): ?>
                                <a href="aircon_list.php" class="btn btn-outline-light ms-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <a class="btn btn-success" href="../actions/export_aircons.php?search=<?= urlencode($search_term) ?>&sy_inv=<?= urlencode($sy_inv_raw) ?>&campus=<?= urlencode($campus_raw) ?>">
                        <i class="fas fa-file-export"></i> Export
                    </a>
                    <button class="btn btn-add text-dark" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="fas fa-plus"></i> Add Aircon
                    </button>
                </div>
            </div>


            <?php
            // Pagination settings
            $records_per_page = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $records_per_page;

            // Get total number of records
            $count_sql = "SELECT COUNT(*) as total FROM aircons i $inv_where";
            $count_result = $conn->query($count_sql);
            $total_records = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_records / $records_per_page);

            // Get inventory data with pagination (respect filters and join supplier)
            $sql = "SELECT i.*, s.supplier_name 
                    FROM aircons i 
                    LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
                    $inv_where
                    ORDER BY i.date_created DESC
                    LIMIT $records_per_page OFFSET $offset";
            $result = $conn->query($sql);
            ?>


            <!-- Aircon Table List -->
            <div class="table-responsive">
                <div id="inventoryTable">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Item Description</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php
                                $item_counter = 1;
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td data-label="Date"><?= htmlspecialchars($row['date'] ?? 'N/A') ?></td>
                                        <td data-label="Name"><?= htmlspecialchars($row['name'] ?? 'N/A') ?></td>
                                        <td data-label="Item Description"><?= htmlspecialchars($row['item_description'] ?? 'N/A') ?></td>
                                        <td data-label="Quantity"><?= htmlspecialchars($row['quantity'] ?? 'N/A') ?></td>
                                        <td data-label="Unit"><?= htmlspecialchars($row['unit'] ?? 'N/A') ?></td>
                                        <td data-label="Notes"><?= htmlspecialchars($row['notes'] ?? 'N/A') ?></td>
                                        <td data-label="Actions" class="actions">
                                            <button class="btn btn-sm btn-outline-info view-maintenance-btn"
                                                data-aircon-id="<?= (int)$row['aircon_id'] ?>"
                                                data-brand="<?= htmlspecialchars($row['brand'] ?? '', ENT_QUOTES) ?>"
                                                data-model="<?= htmlspecialchars($row['model'] ?? '', ENT_QUOTES) ?>"
                                                data-serial="<?= htmlspecialchars($row['serial_number'] ?? '', ENT_QUOTES) ?>"
                                                title="View Maintenance Records">
                                                <i class="fas fa-calendar-alt"></i> View Records
                                            </button>
                                            <button class="btn btn-sm btn-primary view-aircon-details-btn"
                                                title="View Details"
                                                data-aircon-id="<?= (int)$row['aircon_id'] ?>"
                                                data-item-number="<?= htmlspecialchars($row['item_number'] ?? '', ENT_QUOTES) ?>"
                                                data-brand="<?= htmlspecialchars($row['brand'] ?? '', ENT_QUOTES) ?>"
                                                data-model="<?= htmlspecialchars($row['model'] ?? '', ENT_QUOTES) ?>"
                                                data-type="<?= htmlspecialchars($row['type'] ?? '', ENT_QUOTES) ?>"
                                                data-capacity="<?= htmlspecialchars($row['capacity'] ?? '', ENT_QUOTES) ?>"
                                                data-serial-number="<?= htmlspecialchars($row['serial_number'] ?? '', ENT_QUOTES) ?>"
                                                data-location="<?= htmlspecialchars($row['location'] ?? '', ENT_QUOTES) ?>"
                                                data-status="<?= htmlspecialchars($row['status'] ?? '', ENT_QUOTES) ?>"
                                                data-purchase-date="<?= htmlspecialchars($row['purchase_date'] ?? '', ENT_QUOTES) ?>"
                                                data-warranty-expiry="<?= htmlspecialchars($row['warranty_expiry'] ?? '', ENT_QUOTES) ?>"
                                                data-last-service-date="<?= htmlspecialchars($row['last_service_date'] ?? '', ENT_QUOTES) ?>"
                                                data-maintenance-schedule="<?= htmlspecialchars($row['maintenance_schedule'] ?? '', ENT_QUOTES) ?>"
                                                data-supplier-info="<?= htmlspecialchars($row['supplier_name'] ?? '', ENT_QUOTES) ?>"
                                                data-installation-date="<?= htmlspecialchars($row['installation_date'] ?? '', ENT_QUOTES) ?>"
                                                data-energy-efficiency="<?= htmlspecialchars($row['energy_efficiency_rating'] ?? '', ENT_QUOTES) ?>"
                                                data-power-consumption="<?= htmlspecialchars($row['power_consumption'] ?? '', ENT_QUOTES) ?>"
                                                data-notes="<?= htmlspecialchars($row['notes'] ?? '', ENT_QUOTES) ?>"
                                                data-purchase-price="<?= htmlspecialchars($row['purchase_price'] ?? '0', ENT_QUOTES) ?>"
                                                data-depreciated-value="<?= htmlspecialchars($row['depreciated_value'] ?? '0', ENT_QUOTES) ?>"
                                                data-receiver="<?= htmlspecialchars($row['receiver'] ?? '', ENT_QUOTES) ?>"
                                                data-created-by="<?= htmlspecialchars($row['created_by'] ?? '', ENT_QUOTES) ?>"
                                                data-date-created="<?= htmlspecialchars($row['date_created'] ?? '', ENT_QUOTES) ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" title="Edit"
                                                onclick="openEditAirconModal(
                                                <?= (int)$row['aircon_id'] ?>,
                                                <?= json_encode($row['item_number'] ?? '') ?>,
                                                <?= json_encode($row['category'] ?? '') ?>,
                                                <?= json_encode($row['brand']) ?>,
                                                <?= json_encode($row['model']) ?>,
                                                <?= json_encode($row['type']) ?>,
                                                <?= json_encode($row['capacity'] ?? '') ?>,
                                                <?= json_encode($row['serial_number']) ?>,
                                                <?= json_encode($row['location']) ?>,
                                                <?= json_encode($row['status']) ?>,
                                                <?= json_encode($row['purchase_date']) ?>,
                                                <?= json_encode($row['warranty_expiry']) ?>,
                                                <?= json_encode($row['last_service_date']) ?>,
                                                <?= json_encode($row['maintenance_schedule']) ?>,
                                                <?= (int)($row['supplier_id'] ?? 0) ?>,
                                                <?= json_encode($row['installation_date']) ?>,
                                                <?= json_encode($row['energy_efficiency_rating'] ?? '') ?>,
                                                <?= json_encode($row['power_consumption'] ?? '') ?>,
                                                <?= json_encode($row['notes']) ?>,
                                                <?= json_encode($row['purchase_price'] ?? '0') ?>,
                                                <?= json_encode($row['depreciated_value'] ?? '0') ?>
                                                )">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-snowflake fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No aircon units found</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                            Add First Aircon
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center mt-3" id="paginationContainer">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="#" onclick="loadInventory(<?= $page - 1 ?>); return false;">&laquo;</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="#" onclick="loadInventory(<?= $i ?>); return false;"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="#" onclick="loadInventory(<?= $page + 1 ?>); return false;">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <!-- Approve/Received Modal -->
            <div class="modal fade" id="receivedModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Mark as Received Items</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="approveForm" action="../actions/mark_property_transaction.php" method="POST">
                            <input type="hidden" id="approve-id" name="procurement_id" style="display: none;">
                            <input type="hidden" id="approve-item-name" name="item_name" style="display: none;">
                            <input type="hidden" id="approve-quantity" name="quantity" style="display: none;">
                            <input type="hidden" id="approve-unit" name="unit" style="display: none;">
                            <input type="hidden" id="approve-supplier" name="supplier" style="display: none;">
                            <input type="hidden" id="approve-price" name="price" style="display: none;">
                            <input type="hidden" id="approve-notes" name="notes" style="display: none;">
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                </div>
                                <p class="text-center">Are you sure you want to mark this item as received and add the item to the inventory?</p>
                                <div class="text-center mb-3">
                                    <strong id="display-item-name"></strong>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Received Date</label>
                                        <input type="date" name="received_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Received Notes (Optional)</label>
                                        <textarea name="received_notes" class="form-control" rows="3" placeholder="Any additional notes about the received items..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Mark as Received</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add New Aircon Modal -->
            <div class="modal fade" id="addInventoryModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: var(--primary-green);">
                            <h5 class="modal-title text-white">Add New Aircon</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="../actions/add_aircon.php" method="POST">
                            <input type="hidden" name="receiver" value="Property Custodian">
                            <input type="hidden" name="status" value="Active">
                            <div class="modal-body">
                                <!-- Basic Information -->
                                <div class="mb-3 pb-2 border-bottom">
                                    <h6 class="mb-3 text-uppercase text-muted">Basic Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Item Number <span class="text-danger">*</span></label>
                                            <input type="text" name="item_name" class="form-control" required placeholder="e.g., AC-001">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Category <span class="text-danger">*</span></label>
                                            <select id="accountSelect" name="category" class="form-select">
                                                <option value="">Select Category</option>
                                                <?php
                                                // Use the same organized categories from the main page
                                                if (isset($organized_categories) && !empty($organized_categories)) {
                                                    foreach ($organized_categories as $main_category => $subcategories) {
                                                        echo '<optgroup label="' . htmlspecialchars($main_category) . '">';
                                                        foreach ($subcategories as $subcategory) {
                                                            echo '<option value="' . htmlspecialchars($subcategory) . '">' . htmlspecialchars($subcategory) . '</option>';
                                                        }
                                                        echo '</optgroup>';
                                                    }
                                                } else {
                                                    // Fallback options if no data available - display as bold headers only
                                                    echo '<optgroup label="Property and Equipment"></optgroup>';
                                                    echo '<optgroup label="Intangible Assets"></optgroup>';
                                                    echo '<optgroup label="Office Supplies"></optgroup>';
                                                    echo '<optgroup label="Medical Supplies"></optgroup>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Brand <span class="text-danger">*</span></label>
                                            <input type="text" name="brand" class="form-control" required placeholder="e.g., Carrier, Daikin">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Model <span class="text-danger">*</span></label>
                                            <input type="text" name="model" class="form-control" required placeholder="e.g., 42QHC018">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Type</label>
                                            <select name="type" class="form-select">
                                                <option value="">Select Type</option>
                                                <option value="Split">Split</option>
                                                <option value="Window">Window</option>
                                                <option value="Portable">Portable</option>
                                                <option value="Cassette">Cassette</option>
                                                <option value="Central">Central</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Capacity (BTU/hr)</label>
                                            <input type="text" name="capacity" class="form-control" placeholder="e.g., 18,000">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Serial Number</label>
                                            <input type="text" name="serial_number" class="form-control" placeholder="Manufacturer serial number">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Location</label>
                                            <input type="text" name="location" class="form-control" placeholder="e.g., Room 101, Office">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="Working">Working</option>
                                                <option value="Needs Repair">Needs Repair</option>
                                                <option value="Under Maintenance">Under Maintenance</option>
                                                <option value="Decommissioned">Decommissioned</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Campus</label>
                                            <select name="campus" class="form-select">
                                                <option value="TED">TED</option>
                                                <option value="BED">BED</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Purchase Date</label>
                                            <input type="date" name="purchase_date" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Warranty Expiry</label>
                                            <input type="date" name="warranty_expiry" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Last Service Date</label>
                                            <input type="date" name="last_service" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Maintenance Schedule</label>
                                            <input type="date" name="maintenance_schedule" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Supplier</label>
                                            <select name="supplier_id" class="form-select">
                                                <option value="">Select Supplier</option>
                                                <?php
                                                // Reset the result pointer to reuse the suppliers data
                                                if ($suppliers_result && $suppliers_result->num_rows > 0) {
                                                    $suppliers_result->data_seek(0);
                                                    while ($supplier = $suppliers_result->fetch_assoc()) {
                                                        echo '<option value="' . $supplier['supplier_id'] . '">' . htmlspecialchars($supplier['supplier_name']) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Installation Date</label>
                                            <input type="date" name="installation_date" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Energy Efficiency Rating</label>
                                            <input type="text" name="energy_efficient" class="form-control" placeholder="e.g., 5-star, A++">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Power Consumption (kW)</label>
                                            <input type="number" step="0.1" name="power_consumption" class="form-control" placeholder="e.g., 1.5">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Notes</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Purchase Price (₱)</label>
                                            <input type="number" step="0.01" name="purchase_price" class="form-control" placeholder="0.00">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Depreciated Value (₱)</label>
                                            <input type="number" step="0.01" name="depreciated_value" class="form-control" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Item</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Aircon Details Modal -->
            <div class="modal fade" id="viewAirconModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: var(--primary-green);">
                            <h5 class="modal-title">
                                <i class="fas fa-snowflake me-2"></i>Aircon Unit Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-4">
                                <!-- Basic Information Section -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Aircon ID</label>
                                                    <p class="fw-bold mb-0" id="view_aircon_id">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Item Number</label>
                                                    <p class="fw-bold mb-0" id="view_item_number">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Brand</label>
                                                    <p class="fw-bold mb-0" id="view_brand">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Model</label>
                                                    <p class="fw-bold mb-0" id="view_model">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Type</label>
                                                    <p class="fw-bold mb-0" id="view_type">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Capacity (BTU/hr)</label>
                                                    <p class="fw-bold mb-0" id="view_capacity">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location & Status Section -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location & Status</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="text-muted small">Serial Number</label>
                                                <p class="fw-bold mb-0" id="view_serial_number">-</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="text-muted small">Location</label>
                                                <p class="fw-bold mb-0" id="view_location">-</p>
                                            </div>
                                            <div class="mb-0">
                                                <label class="text-muted small">Status</label>
                                                <p class="fw-bold mb-0" id="view_status">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dates Section -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Important Dates</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="text-muted small">Purchase Date</label>
                                                <p class="fw-bold mb-0" id="view_purchase_date">-</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="text-muted small">Warranty Expiry</label>
                                                <p class="fw-bold mb-0" id="view_warranty_expiry">-</p>
                                            </div>
                                            <div class="mb-0">
                                                <label class="text-muted small">Installation Date</label>
                                                <p class="fw-bold mb-0" id="view_installation_date">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Maintenance Section -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Maintenance Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Last Service Date</label>
                                                    <p class="fw-bold mb-0" id="view_last_service_date">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Maintenance Schedule</label>
                                                    <p class="fw-bold mb-0" id="view_maintenance_schedule">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Supplier Info</label>
                                                    <p class="fw-bold mb-0" id="view_supplier_info">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Technical Specifications Section -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Technical Specifications</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="text-muted small">Energy Efficiency Rating</label>
                                                    <p class="fw-bold mb-0" id="view_energy_efficiency_rating">-</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="text-muted small">Power Consumption (kW)</label>
                                                    <p class="fw-bold mb-0" id="view_power_consumption">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Information Section -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-dark text-white">
                                            <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Financial Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Purchase Price</label>
                                                    <p class="fw-bold mb-0" id="view_purchase_price">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Depreciated Value</label>
                                                    <p class="fw-bold mb-0" id="view_depreciated_value">-</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="text-muted small">Receiver</label>
                                                    <p class="fw-bold mb-0" id="view_receiver">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes Section -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header" style="background-color: #6c757d; color: white;">
                                            <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-0" id="view_notes">-</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Record Information Section -->
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header" style="background-color: #5a6268; color: white;">
                                            <h6 class="mb-0"><i class="fas fa-database me-2"></i>Record Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="text-muted small">Created By</label>
                                                    <p class="fw-bold mb-0" id="view_created_by">-</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="text-muted small">Date Created</label>
                                                    <p class="fw-bold mb-0" id="view_date_created">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="printAirconDetails()">
                                <i class="fas fa-print me-2"></i>Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Movement Modal -->
            <div class="modal fade" id="stockMovementModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Stock Movement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="stockMovementForm" action="../actions/property_stock_movement.php" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="inventory_id" id="movement_inventory_id">
                                <input type="hidden" name="movement_type" id="movement_type">

                                <div class="mb-3">
                                    <label class="form-label">Item</label>
                                    <input type="text" id="movement_item_name" class="form-control" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Movement Type</label>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success flex-fill movement-btn" id="stockInBtn" onclick="setMovementType('IN')">
                                            <i class="fas fa-plus"></i> Stock In
                                        </button>
                                        <button type="button" class="btn btn-warning flex-fill movement-btn" id="stockOutBtn" onclick="setMovementType('OUT')">
                                            <i class="fas fa-minus"></i> Stock Out
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="text" name="quantity" class="form-control" required min="1">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Record Movement</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hidden form for Add to Inventory from Received row -->
            <form id="addInventoryHiddenForm" action="../actions/add_property.php" method="POST" style="display:none;">
                <input type="hidden" name="procurement_id" id="ai_procurement_id">
                <input type="hidden" name="item_name" id="ai_item_name">
                <input type="hidden" name="category" id="ai_category">
                <input type="hidden" name="current_stock" id="ai_current_stock">
                <input type="hidden" name="unit" id="ai_unit">
                <input type="hidden" name="reorder_level" id="ai_reorder_level" value="0">
                <input type="hidden" name="supplier_id" id="ai_supplier_id">
                <input type="hidden" name="unit_cost" id="ai_unit_cost">
                <input type="hidden" name="location" id="ai_location" value="Warehouse">
                <input type="hidden" name="description" id="ai_description">
                <input type="hidden" name="status" id="ai_status">
                <input type="hidden" name="receiver" id="ai_receiver" value="Property Custodian">
            </form>

            <!-- Edit Inventory Modal -->
            <div class="modal fade" id="editInventoryModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Property Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="editInventoryForm" action="../actions/edit_property.php" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="inventory_id" id="ei_inventory_id">

                                <!-- Basic Information -->
                                <div class="mb-3 pb-2 border-bottom">
                                    <h6 class="mb-3 text-uppercase text-muted">Basic Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Item Name</label>
                                            <input type="text" class="form-control" name="item_name" id="ei_item_name" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Category</label>
                                            <select id="ei_category" name="category" class="form-select">
                                                <option value="">Select Category</option>
                                                <?php
                                                // Use the same organized categories from the main page
                                                if (isset($organized_categories) && !empty($organized_categories)) {
                                                    foreach ($organized_categories as $main_category => $subcategories) {
                                                        echo '<optgroup label="' . htmlspecialchars($main_category) . '">';
                                                        foreach ($subcategories as $subcategory) {
                                                            echo '<option value="' . htmlspecialchars($subcategory) . '">' . htmlspecialchars($subcategory) . '</option>';
                                                        }
                                                        echo '</optgroup>';
                                                    }
                                                } else {
                                                    // Fallback options if no data available - display as bold headers only
                                                    echo '<optgroup label="Property and Equipment"></optgroup>';
                                                    echo '<optgroup label="Intangible Assets"></optgroup>';
                                                    echo '<optgroup label="Office Supplies"></optgroup>';
                                                    echo '<optgroup label="Medical Supplies"></optgroup>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2">
                                        <div class="col-3">
                                            <label class="form-label">Brand</label>
                                            <input class="form-control" name="brand" id="ei_brand">
                                        </div>

                                        <div class="col-3">
                                            <label class="form-label">Color</label>
                                            <input class="form-control" name="color" id="ei_color">
                                            <small class="text-muted" style="font-size: 12px;">leave black if not applicable</small>
                                        </div>

                                        <div class="col-3">
                                            <label class="form-label">Size</label>
                                            <input class="form-control" name="size" id="ei_size">
                                            <small class="text-muted" style="font-size: 12px;">leave black if not applicable</small>
                                        </div>

                                        <div class="col-3">
                                            <label class="form-label">Type</label>
                                            <input class="form-control" name="type" id="ei_type">
                                            <small class="text-muted" style="font-size: 12px;">leave black if not applicable</small>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" id="ei_description" rows="2" placeholder="Optional description..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock & Unit -->
                                <div class="mb-3 pb-2 border-bottom">
                                    <h6 class="mb-3 text-uppercase text-muted">Stock & Unit</h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Current Stock</label>
                                            <input type="number" class="form-control" name="current_stock" id="ei_current_stock" min="0" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Reorder Level</label>
                                            <input type="number" class="form-control" name="reorder_level" id="ei_reorder_level" min="0">
                                            <small style="color: gray; font-size: 12px;">Leave blank if not applicable</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Unit</label>
                                            <select name="unit" id="ei_unit" class="form-select" required>
                                                <option value="">--Select Unit--</option>
                                                <option value="pc">Piece</option>
                                                <option value="pcs">Pieces</option>
                                                <option value="box">Box</option>
                                                <option value="boxes">Boxes</option>
                                                <option value="kg">Kilogram</option>
                                                <option value="kgs">Kilograms</option>
                                                <option value="liter">Liter</option>
                                                <option value="liters">Liters</option>
                                                <option value="set">Set</option>
                                                <option value="sets">Sets</option>
                                                <option value="pack">Pack</option>
                                                <option value="packs">Packs</option>
                                                <option value="ream">Ream</option>
                                                <option value="reams">Reams</option>
                                                <option value="gal">Gallon</option>
                                                <option value="gals">Gallons</option>
                                                <option value="bag">Bag</option>
                                                <option value="bags">Bags</option>
                                                <option value="none">Others</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Unit Cost</label>
                                            <input type="number" step="0.01" class="form-control" name="unit_cost" id="ei_unit_cost" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Supplier & Location -->
                                <div class="mb-3 pb-2 border-bottom">
                                    <h6 class="mb-3 text-uppercase text-muted">Supplier & Location</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Supplier</label>
                                            <select class="form-select" name="supplier_id" id="ei_supplier_id" required>
                                                <option value="">Select Supplier</option>
                                                <?php
                                                if ($suppliers_result) {
                                                    $suppliers_result->data_seek(0);
                                                    while ($supplier = $suppliers_result->fetch_assoc()):
                                                ?>
                                                        <option value="<?= $supplier['supplier_id'] ?>"><?= htmlspecialchars($supplier['supplier_name']) ?></option>
                                                <?php endwhile;
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location</label>
                                            <input type="text" class="form-control" name="location" id="ei_location">
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Meta -->
                                <div class="mb-2">
                                    <h6 class="mb-3 text-uppercase text-muted">Status & Meta</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status" id="ei_status">
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                                <option value="Discontinued">Discontinued</option>
                                            </select>
                                        </div>
                                        <input type="hidden" class="form-control" name="quantity" id="ei_quantity" min="0">
                                        <div class="col-md-4">
                                            <label class="form-label">Receiver</label>
                                            <input type="text" class="form-control" name="receiver" id="ei_receiver" placeholder="e.g., Property Custodian">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Received Notes</label>
                                            <textarea type="text" class="form-control" name="received_notes" id="ei_received_notes" placeholder="Optional notes"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Maintenance Records Modal -->
            <div class="modal fade" id="maintenanceRecordsModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
                            <h5 class="modal-title">
                                <i class="fas fa-tools me-2"></i>Maintenance Records
                                <small class="d-block mt-1" style="font-size: 0.85rem;" id="maintenance-aircon-info"></small>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Maintenance History</h6>
                                <div class="d-flex gap-2">
                                    <a id="exportMaintCsv" class="btn btn-outline-primary btn-sm" href="#" target="_blank">
                                        <i class="fas fa-file-csv"></i> Export CSV
                                    </a>
                                    <a id="exportMaintXls" class="btn btn-outline-success btn-sm" href="#" target="_blank">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm" id="addMaintenanceBtn">
                                        <i class="fas fa-plus"></i> Add Maintenance Record
                                    </button>
                                </div>
                            </div>

                            <!-- Maintenance Records Table -->
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="maintenanceTable">
                                    <thead class="table-info">
                                        <tr>
                                            <th>Service Date</th>
                                            <th>Service Type</th>
                                            <th>Technician</th>
                                            <th>Next Scheduled</th>
                                            <th>Remarks</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="maintenanceTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="spinner-border text-info" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div id="noMaintenanceMessage" class="text-center py-4" style="display: none;">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h5>No Maintenance Records</h5>
                                <p class="text-muted">No maintenance records found for this aircon unit.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Maintenance Record Modal -->
            <div class="modal fade" id="maintenanceFormModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: var(--primary-green);">
                            <h5 class="modal-title text-white" id="maintenanceFormTitle">
                                <i class="fas fa-wrench me-2"></i>Add Maintenance Record
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="maintenanceForm">
                            <input type="hidden" id="maint_maintenance_id" name="maintenance_id">
                            <input type="hidden" id="maint_aircon_id" name="aircon_id">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Service Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="maint_service_date" name="service_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Service Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="maint_service_type" name="service_type" required>
                                            <option value="">-- Select Service Type --</option>
                                            <option value="Cleaning">Cleaning</option>
                                            <option value="Repair">Repair</option>
                                            <option value="Preventive Maintenance">Preventive Maintenance</option>
                                            <option value="Filter Replacement">Filter Replacement</option>
                                            <option value="Gas Refill">Gas Refill</option>
                                            <option value="Inspection">Inspection</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Technician</label>
                                        <input type="text" class="form-control" id="maint_technician" name="technician" placeholder="Name or company">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Next Scheduled Date</label>
                                        <input type="date" class="form-control" id="maint_next_scheduled_date" name="next_scheduled_date">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Remarks</label>
                                        <textarea class="form-control" id="maint_remarks" name="remarks" rows="3" placeholder="Notes or issues found..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Record
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Maintenance Detail Modal -->
            <div class="modal fade" id="viewMaintenanceModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white;">
                            <h5 class="modal-title">
                                <i class="fas fa-eye me-2"></i>Maintenance Record Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Service Date</label>
                                    <p class="form-control-plaintext" id="view_service_date"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Service Type</label>
                                    <p class="form-control-plaintext" id="view_service_type"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Technician</label>
                                    <p class="form-control-plaintext" id="view_technician"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Next Scheduled Date</label>
                                    <p class="form-control-plaintext" id="view_next_scheduled_date"></p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Remarks</label>
                                    <p class="form-control-plaintext" id="view_remarks"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Created By</label>
                                    <p class="form-control-plaintext" id="view_created_by"></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Date Created</label>
                                    <p class="form-control-plaintext" id="view_date_created"></p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>


            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

            <script>
                // Session message variables
                var sessionMessage = '<?= addslashes($session_message) ?>';
                var sessionError = '<?= addslashes($session_error) ?>';

                $(document).ready(function() {
                    // Show session message alert if there are messages
                    if (sessionMessage || sessionError) {
                        showSessionMessageAlert();
                    }

                    // Enable Enter key search
                    $('#search').on('keypress', function(e) {
                        if (e.which === 13) { // Enter key
                            $(this).closest('form').submit();
                        }
                    });

                    // Real-time search with debounce - NO PAGE REFRESH
                    let searchTimeout;
                    $('#search').on('input', function() {
                        clearTimeout(searchTimeout);
                        const searchValue = $(this).val();

                        searchTimeout = setTimeout(function() {
                            if (searchValue.length === 0 || searchValue.length >= 2) {
                                // Perform AJAX search instead of form submission
                                performSearch(searchValue);
                            }
                        }, 500); // Wait 500ms after user stops typing
                    });


                    // Mark as Received Modal functionality
                    // Mark as Received Modal functionality
                    $('.mark-received-btn').on('click', function() {
                        const button = $(this);
                        const transactionId = button.data('transaction-id');
                        const itemName = button.data('item-name');
                        const quantity = button.data('quantity');
                        const unit = button.data('unit');
                        const supplier = button.data('supplier');
                        const unitPrice = button.data('unit-price');
                        const notes = button.data('notes');
                        const receiver = button.data('receiver');

                        // Populate hidden fields
                        $('#approve-id').val(transactionId);
                        $('#approve-item-name').val(itemName);
                        $('#approve-quantity').val(quantity);
                        $('#approve-unit').val(unit);
                        $('#approve-supplier').val(supplier);
                        $('#approve-price').val(unitPrice);
                        $('#approve-notes').val(notes);
                        $('#approve-receiver').val(receiver);
                        // Display item name in modal
                        $('#display-item-name').text(itemName);

                        console.log('Mark as Received modal populated with:', {
                            id: transactionId,
                            itemName: itemName,
                            quantity: quantity,
                            unit: unit,
                            supplier: supplier,
                            unitPrice: unitPrice,
                            notes: notes,
                            receiver: receiver
                        });
                    });

                    // View Aircon Details button handler
                    $(document).on('click', '.view-aircon-details-btn', function() {
                        const button = $(this);

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
                        const modalId = button.data('modal-id');

                        // Call the viewAirconDetails function
                        viewAirconDetails(
                            airconId, itemNumber, brand, model, type, capacity, serialNumber,
                            location, status, purchaseDate, warrantyExpiry, lastServiceDate,
                            maintenanceSchedule, supplierInfo, installationDate, energyEfficiency,
                            powerConsumption, notes, purchasePrice, depreciatedValue, receiver,
                            createdBy, dateCreated
                        );

                        // Hide the parent modal if modalId is specified
                        if (modalId) {
                            $('#' + modalId).modal('hide');
                        }
                    });
                });

                // Maintenance Records Modal Handler - Global scope
                let currentAirconId = null;

                $(document).ready(function() {
                    $(document).on('click', '.view-maintenance-btn', function() {
                        const button = $(this);
                        currentAirconId = button.data('aircon-id');
                        const brand = button.data('brand');
                        const model = button.data('model');
                        const serial = button.data('serial');

                        // Set aircon info in modal
                        $('#maintenance-aircon-info').text(`${brand} ${model} - S/N: ${serial}`);

                        // Show modal
                        $('#maintenanceRecordsModal').modal('show');

                        // Load maintenance records
                        loadMaintenanceRecords(currentAirconId);

                        // Wire export links with current aircon id
                        const csvLink = document.getElementById('exportMaintCsv');
                        const xlsLink = document.getElementById('exportMaintXls');
                        if (csvLink) csvLink.href = `../actions/export_aircon_maintenance_csv.php?aircon_id=${encodeURIComponent(currentAirconId)}`;
                        if (xlsLink) xlsLink.href = `../actions/export_aircon_maintenance_excel.php?aircon_id=${encodeURIComponent(currentAirconId)}`;
                    });

                    // Add Maintenance Button
                    $('#addMaintenanceBtn').on('click', function() {
                        $('#maintenanceFormTitle').html('<i class="fas fa-wrench me-2"></i>Add Maintenance Record');
                        $('#maintenanceForm')[0].reset();
                        $('#maint_maintenance_id').val('');
                        $('#maint_aircon_id').val(currentAirconId);
                        $('#maintenanceFormModal').modal('show');
                    });

                    // Submit Maintenance Form
                    $('#maintenanceForm').on('submit', function(e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        const maintenanceId = $('#maint_maintenance_id').val();
                        const url = maintenanceId ? '../actions/edit_aircon_maintenance.php' : '../actions/add_aircon_maintenance.php';

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    alert('✅ ' + response.message);
                                    $('#maintenanceFormModal').modal('hide');
                                    loadMaintenanceRecords(currentAirconId);
                                    // Reload page to update last service date
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    alert('❌ ' + response.message);
                                }
                            },
                            error: function() {
                                alert('❌ Error saving maintenance record');
                            }
                        });
                    });
                }); // End of $(document).ready for maintenance handlers

                // Function to show session message alert
                function showSessionMessageAlert() {
                    if (sessionMessage) {
                        // Success message
                        alert('✅ Success!\n\n' + sessionMessage);
                    } else if (sessionError) {
                        // Error message
                        alert('❌ Error!\n\n' + sessionError);
                    }
                }

                // Load Maintenance Records
                function loadMaintenanceRecords(airconId) {
                    $('#maintenanceTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="spinner-border text-info" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    `);
                    $('#noMaintenanceMessage').hide();

                    $.ajax({
                        url: '../actions/get_aircon_maintenance.php',
                        type: 'GET',
                        data: {
                            aircon_id: airconId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                const records = response.maintenance_records;

                                if (records.length === 0) {
                                    $('#maintenanceTableBody').html('');
                                    $('#noMaintenanceMessage').show();
                                } else {
                                    let html = '';
                                    records.forEach(function(record) {
                                        const serviceDate = record.service_date ? formatDate(record.service_date) : 'N/A';
                                        const nextDate = record.next_scheduled_date ? formatDate(record.next_scheduled_date) : 'N/A';
                                        const technician = record.technician || 'N/A';
                                        const remarks = record.remarks ? (record.remarks.length > 50 ? record.remarks.substring(0, 50) + '...' : record.remarks) : 'N/A';

                                        // Escape data for HTML attributes
                                        const escapedTechnician = (record.technician || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                        const escapedRemarks = (record.remarks || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                                        const escapedServiceType = (record.service_type || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                                        html += `
                                            <tr>
                                                <td>${serviceDate}</td>
                                                <td><span class="badge bg-info">${record.service_type}</span></td>
                                                <td>${technician}</td>
                                                <td>${nextDate}</td>
                                                <td>${remarks}</td>
                                                <td>${record.created_by || 'N/A'}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-secondary view-maint-btn" 
                                                        data-id="${record.maintenance_id}"
                                                        title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning edit-maint-btn" 
                                                        data-id="${record.maintenance_id}"
                                                        data-date="${record.service_date || ''}"
                                                        data-type="${escapedServiceType}"
                                                        data-technician="${escapedTechnician}"
                                                        data-next="${record.next_scheduled_date || ''}"
                                                        data-remarks="${escapedRemarks}"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-maint-btn" 
                                                        data-id="${record.maintenance_id}"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        `;
                                    });
                                    $('#maintenanceTableBody').html(html);
                                    $('#noMaintenanceMessage').hide();
                                }
                            } else {
                                $('#maintenanceTableBody').html(`
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> ${response.message}
                                        </td>
                                    </tr>
                                `);
                            }
                        },
                        error: function() {
                            $('#maintenanceTableBody').html(`
                                <tr>
                                    <td colspan="7" class="text-center text-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Error loading maintenance records
                                    </td>
                                </tr>
                            `);
                        }
                    });
                }

                // View Maintenance Record
                $(document).on('click', '.view-maint-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('View button clicked');

                    const maintenanceId = $(this).data('id');
                    console.log('Maintenance ID:', maintenanceId);

                    $.ajax({
                        url: '../actions/get_aircon_maintenance.php',
                        type: 'GET',
                        data: {
                            aircon_id: currentAirconId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                const record = response.maintenance_records.find(r => r.maintenance_id == maintenanceId);
                                if (record) {
                                    $('#view_service_date').text(record.service_date ? formatDate(record.service_date) : 'N/A');
                                    $('#view_service_type').text(record.service_type || 'N/A');
                                    $('#view_technician').text(record.technician || 'N/A');
                                    $('#view_next_scheduled_date').text(record.next_scheduled_date ? formatDate(record.next_scheduled_date) : 'N/A');
                                    $('#view_remarks').text(record.remarks || 'N/A');
                                    $('#view_created_by').text(record.created_by || 'N/A');
                                    $('#view_date_created').text(record.date_created ? formatDateTime(record.date_created) : 'N/A');

                                    $('#viewMaintenanceModal').modal('show');
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching maintenance details:', error);
                            alert('Error loading maintenance details');
                        }
                    });
                });

                // Edit Maintenance Record
                $(document).on('click', '.edit-maint-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Edit button clicked');

                    const button = $(this);
                    const maintenanceId = button.data('id');
                    console.log('Editing maintenance ID:', maintenanceId);

                    $('#maintenanceFormTitle').html('<i class="fas fa-edit me-2"></i>Edit Maintenance Record');
                    $('#maint_maintenance_id').val(maintenanceId);
                    $('#maint_aircon_id').val(currentAirconId);
                    $('#maint_service_date').val(button.data('date'));
                    $('#maint_service_type').val(button.data('type'));

                    // Decode HTML entities for technician
                    const technicianValue = button.data('technician');
                    const decodedTechnician = $('<textarea/>').html(technicianValue).text();
                    $('#maint_technician').val(decodedTechnician !== 'N/A' && decodedTechnician !== '' ? decodedTechnician : '');

                    $('#maint_next_scheduled_date').val(button.data('next'));

                    // Decode HTML entities for remarks
                    const remarksValue = button.data('remarks');
                    const decodedRemarks = $('<textarea/>').html(remarksValue).text();
                    $('#maint_remarks').val(decodedRemarks);

                    $('#maintenanceFormModal').modal('show');
                });

                // Delete Maintenance Record
                $(document).on('click', '.delete-maint-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Delete button clicked');

                    const maintenanceId = $(this).data('id');
                    console.log('Deleting maintenance ID:', maintenanceId);

                    if (confirm('Are you sure you want to delete this maintenance record?')) {
                        $.ajax({
                            url: '../actions/delete_aircon_maintenance.php',
                            type: 'POST',
                            data: {
                                maintenance_id: maintenanceId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    alert('✅ ' + response.message);
                                    loadMaintenanceRecords(currentAirconId);
                                    // Reload page to update last service date
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    alert('❌ ' + response.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error deleting maintenance record:', error);
                                alert('❌ Error deleting maintenance record');
                            }
                        });
                    }
                });

                // Helper function to format date
                function formatDate(dateString) {
                    if (!dateString || dateString === '0000-00-00') return 'N/A';
                    const date = new Date(dateString);
                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    };
                    return date.toLocaleDateString('en-US', options);
                }

                // Helper function to format datetime
                function formatDateTime(dateString) {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    return date.toLocaleDateString('en-US', options);
                }

                // AJAX search function - no page refresh
                function performSearch(searchTerm) {
                    // Show loading indicator
                    const searchInput = $('#search');
                    const originalValue = searchInput.val();

                    // Add loading class to search input
                    searchInput.addClass('loading');

                    // Get current URL parameters
                    const currentParams = new URLSearchParams(window.location.search);
                    currentParams.set('search', searchTerm);
                    currentParams.set('ajax', '1');
                    currentParams.delete('page'); // Reset to first page for new search

                    // Fetch search results
                    fetch("aircon_list.php?" + currentParams.toString())
                        .then(response => response.text())
                        .then(data => {
                            // Remove loading class
                            searchInput.removeClass('loading');

                            // Extract table content from response
                            const temp = document.createElement('div');
                            temp.innerHTML = data;
                            const newTable = temp.querySelector('#inventoryTable');
                            const newPagination = temp.querySelector('#paginationContainer');

                            if (newTable) {
                                document.getElementById("inventoryTable").innerHTML = newTable.innerHTML;
                            }
                            if (newPagination) {
                                const paginationContainer = document.querySelector("#paginationContainer");
                                if (paginationContainer) {
                                    paginationContainer.innerHTML = newPagination.innerHTML;
                                }
                            }

                            // Update URL without page reload
                            const url = new URL(window.location);
                            url.searchParams.set('search', searchTerm);
                            url.searchParams.delete('page');
                            window.history.pushState({}, '', url);

                            // Smooth scroll to top of table
                            const table = document.querySelector('.table-responsive');
                            if (table) {
                                table.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }
                        })
                        .catch(error => {
                            console.error("Search error:", error);
                            searchInput.removeClass('loading');

                            // Show error message
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'alert alert-danger mt-3';
                            errorDiv.innerHTML = `
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error performing search. Please try again.
                                <button class="btn btn-sm btn-outline-danger ms-2" onclick="performSearch('${searchTerm}')">
                                    <i class="fas fa-sync-alt"></i> Retry
                                </button>
                            `;

                            const tableContainer = document.querySelector('.table-responsive');
                            if (tableContainer) {
                                tableContainer.parentNode.insertBefore(errorDiv, tableContainer.nextSibling);
                            }
                        });
                }

                // Function to change status from Pending to Received
                window.changeStatusToReceived = function() {
                    const procurementId = $('#approve-id').val();
                    const itemName = $('#approve-item-name').val();

                    if (!procurementId) {
                        alert('Error: Procurement ID not found');
                        return;
                    }

                    if (confirm(`Are you sure you want to change the status of "${itemName}" from Pending to Received?`)) {
                        // Send AJAX request to change status
                        $.ajax({
                            url: '../actions/change_procurement_status.php',
                            type: 'POST',
                            data: {
                                procurement_id: procurementId,
                                new_status: 'Received'
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    alert('Status changed successfully from Pending to Received!');
                                    // Close the modal
                                    $('#receivedModal').modal('hide');
                                    // Reload the page to reflect changes
                                    location.reload();
                                } else {
                                    alert('Error changing status: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Error occurred while changing the status');
                            }
                        });
                    }
                };

                function stockIn(inventoryId) {
                    document.getElementById('movement_inventory_id').value = inventoryId;
                    document.getElementById('movement_type').value = 'IN';
                    fetchItemDetails(inventoryId);
                    const smModal = new bootstrap.Modal(document.getElementById('stockMovementModal'));
                    smModal.show();
                    // Set the movement type and highlight the button after modal is shown
                    setTimeout(() => {
                        setMovementType('IN');
                    }, 100);
                }

                function stockOut(inventoryId) {
                    document.getElementById('movement_inventory_id').value = inventoryId;
                    document.getElementById('movement_type').value = 'OUT';
                    fetchItemDetails(inventoryId);
                    const smModal = new bootstrap.Modal(document.getElementById('stockMovementModal'));
                    smModal.show();
                    // Set the movement type and highlight the button after modal is shown
                    setTimeout(() => {
                        setMovementType('OUT');
                    }, 100);
                }

                function fetchItemDetails(inventoryId) {
                    $.ajax({
                        url: '../pages/get_aircon_list.php',
                        type: 'GET',
                        data: {
                            inventory_id: inventoryId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                $('#movement_item_name').val(response.item.item_name);
                            } else {
                                $('#movement_item_name').val('Item not found');
                            }
                        },
                        error: function() {
                            $('#movement_item_name').val('Error loading item');
                        }
                    });
                }

                function setMovementType(type) {
                    $('#movement_type').val(type);

                    // Remove active class from all movement buttons
                    $('.movement-btn').removeClass('active btn-outline-success btn-outline-warning');

                    if (type === 'IN') {
                        $('#stockInBtn').addClass('active');
                        $('#stockInBtn').addClass('btn-outline-success');
                        $('#stockOutBtn').removeClass('btn-outline-warning');
                    } else {
                        $('#stockOutBtn').addClass('active');
                        $('#stockOutBtn').addClass('btn-outline-warning');
                        $('#stockInBtn').removeClass('btn-outline-success');
                    }
                }

                function viewItem(inventoryId) {
                    // Implement view item details functionality
                    console.log('View item:', inventoryId);
                }

                function viewAllMovements() {
                    // Implement view all movements functionality
                    console.log('View all movements');
                }

                // Add to Inventory from Acquired Supplies Items row
                function addToInventoryFromRow(button) {
                    const row = button.closest('tr');
                    if (!row) {
                        alert('Error: Could not find table row');
                        return;
                    }

                    const procurementId = row.getAttribute('data-transaction-id') || '';
                    const itemName = row.getAttribute('data-description') || '';
                    const category = row.getAttribute('data-category') || '';
                    const quantity = row.getAttribute('data-quantity') || '0';
                    const unit = row.getAttribute('data-unit') || '';
                    const supplierId = row.getAttribute('data-supplier-id') || '';
                    const unitPrice = row.getAttribute('data-unit-price') || '0';
                    const status = row.getAttribute('data-status') || '';
                    const receiver = row.getAttribute('data-receiver') || '';

                    // Debug: Log all retrieved values
                    console.log('Retrieved data from row:', {
                        procurementId: procurementId,
                        itemName: itemName,
                        category: category,
                        unit: unit,
                        quantity: quantity,
                        supplierId: supplierId,
                        unitPrice: unitPrice,
                        status: status,
                        receiver: receiver
                    });

                    // More lenient validation - only check for truly essential fields
                    if (!itemName || itemName === 'N/A') {
                        alert('Error: Item name is missing or invalid.');
                        return;
                    }

                    // Set defaults for missing fields
                    const finalCategory = category && category !== 'N/A' ? category : 'General';
                    const finalUnit = unit && unit !== '' ? unit : 'pc';
                    const finalQuantity = quantity && quantity !== '0' ? quantity : '1';

                    // Fill hidden form
                    document.getElementById('ai_item_name').value = itemName;
                    document.getElementById('ai_category').value = finalCategory;
                    document.getElementById('ai_current_stock').value = finalQuantity;
                    document.getElementById('ai_unit').value = finalUnit;
                    document.getElementById('ai_status').value = status;
                    document.getElementById('ai_supplier_id').value = supplierId;
                    document.getElementById('ai_unit_cost').value = unitPrice;
                    document.getElementById('ai_reorder_level').value = Math.max(1, Math.floor(finalQuantity * 0.2));
                    document.getElementById('ai_description').value = `From invoice ${row.getAttribute('data-invoice') || ''}`;
                    document.getElementById('ai_procurement_id').value = procurementId;
                    document.getElementById('ai_receiver').value = 'Property Custodian';
                    // Debug: Log the final values being submitted
                    console.log('Final values being submitted:', {
                        item_name: itemName,
                        category: finalCategory,
                        current_stock: finalQuantity,
                        unit: finalUnit,
                        supplier_id: supplierId,
                        unit_cost: unitPrice,
                        reorder_level: Math.max(1, Math.floor(finalQuantity * 0.2)),
                        status: status,
                        receiver: receiver
                    });

                    // Submit
                    document.getElementById('addInventoryHiddenForm').submit();
                }

                // Open Edit Inventory modal with data
                function openEditInventoryModal(id, name, category, unit, stock, reorder, location, supplierId, unitCost, description, quantity, receiver, status, receivedNotes, type, brand, size, color) {
                    document.getElementById('ei_inventory_id').value = id;
                    document.getElementById('ei_item_name').value = name;
                    // Ensure Category select reflects the value even if it's not preset
                    (function() {
                        const catSelect = document.getElementById('ei_category');
                        if (catSelect) {
                            let found = false;
                            for (let i = 0; i < catSelect.options.length; i++) {
                                if (String(catSelect.options[i].value) === String(category)) {
                                    found = true;
                                    break;
                                }
                            }
                            if (!found && category) {
                                const opt = document.createElement('option');
                                opt.value = category;
                                opt.textContent = category;
                                catSelect.appendChild(opt);
                            }
                            catSelect.value = category || '';
                        }
                    })();
                    // Ensure Unit select reflects the value even if it's not preset
                    (function() {
                        const unitSelect = document.getElementById('ei_unit');
                        if (unitSelect) {
                            let found = false;
                            for (let i = 0; i < unitSelect.options.length; i++) {
                                if (String(unitSelect.options[i].value) === String(unit)) {
                                    found = true;
                                    break;
                                }
                            }
                            if (!found && unit) {
                                const opt = document.createElement('option');
                                opt.value = unit;
                                opt.textContent = unit;
                                unitSelect.appendChild(opt);
                            }
                            unitSelect.value = unit || '';
                        }
                    })();
                    document.getElementById('ei_current_stock').value = stock;
                    document.getElementById('ei_reorder_level').value = reorder;
                    document.getElementById('ei_location').value = location || '';
                    document.getElementById('ei_supplier_id').value = supplierId || '';
                    document.getElementById('ei_unit_cost').value = unitCost || 0;
                    document.getElementById('ei_description').value = description || '';
                    document.getElementById('ei_brand').value = brand || '';
                    document.getElementById('ei_color').value = color || '';
                    document.getElementById('ei_size').value = size || '';
                    document.getElementById('ei_type').value = type || '';
                    document.getElementById('ei_quantity').value = quantity || 0;
                    document.getElementById('ei_receiver').value = receiver || '';
                    document.getElementById('ei_status').value = status || 'Active';
                    document.getElementById('ei_received_notes').value = receivedNotes || '';
                    const modal = new bootstrap.Modal(document.getElementById('editInventoryModal'));
                    modal.show();
                }

                // ANCHOR: Edit inventory item with modal management
                function editInventoryItem(inventoryId, currentModalId) {
                    console.log('editInventoryItem called with ID:', inventoryId, 'Modal:', currentModalId);

                    // Hide the current modal first if specified
                    if (currentModalId) {
                        $('#' + currentModalId).modal('hide');
                    }

                    // Wait for modal to close, then fetch data and open edit modal
                    setTimeout(function() {
                        // Fetch inventory item data via AJAX
                        $.ajax({
                            url: '../actions/get_aircon_list.php',
                            method: 'GET',
                            data: {
                                inventory_id: inventoryId
                            },
                            dataType: 'json',
                            success: function(data) {
                                console.log('AJAX response:', data);
                                if (data.success) {
                                    openEditInventoryModal(
                                        data.item.inventory_id,
                                        data.item.item_name,
                                        data.item.category,
                                        data.item.unit,
                                        data.item.current_stock,
                                        data.item.reorder_level,
                                        data.item.location || '',
                                        data.item.supplier_id,
                                        data.item.unit_cost,
                                        data.item.description || '',
                                        data.item.quantity || 0,
                                        data.item.receiver || '',
                                        data.item.status || 'Active',
                                        data.item.received_notes || '',
                                        data.item.type || '',
                                        data.item.brand || '',
                                        data.item.size || '',
                                        data.item.color || ''
                                    );
                                } else {
                                    alert('Error loading inventory item: ' + (data.message || 'Unknown error'));
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', status, error);
                                alert('Error: Could not load inventory item data. Please check the console for details.');
                            }
                        });
                    }, currentModalId ? 300 : 0);
                }

                function loadInventory(page = 1) {
                    // Update URL without page reload
                    const url = new URL(window.location);
                    url.searchParams.set('page', page);
                    window.history.pushState({}, '', url);

                    // Show loading overlay with animation
                    const tableContainer = document.querySelector('.table-container');
                    const loadingOverlay = document.createElement('div');
                    loadingOverlay.id = 'loadingOverlay';
                    loadingOverlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1050;
            border-radius: 8px;
        `;

                    // Create loading spinner
                    const spinner = document.createElement('div');
                    spinner.className = 'spinner-border text-primary';
                    spinner.style.width = '3rem';
                    spinner.style.height = '3rem';
                    spinner.role = 'status';

                    const spinnerText = document.createElement('span');
                    spinnerText.className = 'visually-hidden';
                    spinnerText.textContent = 'Loading...';

                    spinner.appendChild(spinnerText);
                    loadingOverlay.appendChild(spinner);

                    // Add loading text
                    const loadingText = document.createElement('div');
                    loadingText.className = 'ms-3';
                    loadingText.style.fontWeight = '600';
                    loadingText.style.color = '#0d6efd';
                    loadingText.textContent = 'Loading inventory data...';
                    loadingOverlay.appendChild(loadingText);

                    // Add to container with relative positioning
                    tableContainer.style.position = 'relative';
                    tableContainer.appendChild(loadingOverlay);

                    // Disable pagination buttons during load
                    const paginationLinks = document.querySelectorAll('.page-link');
                    paginationLinks.forEach(link => {
                        link.style.pointerEvents = 'none';
                        link.style.opacity = '0.6';
                    });

                    // Get current URL parameters to maintain search and filters
                    const currentParams = new URLSearchParams(window.location.search);
                    currentParams.set('ajax', '1');
                    currentParams.set('page', page);

                    // Fetch the page content from this Release Logs page (AJAX partial)
                    fetch("property_release_logs.php?" + currentParams.toString())
                        .then(response => response.text())
                        .then(data => {
                            // Remove loading overlay
                            if (loadingOverlay.parentNode) {
                                loadingOverlay.parentNode.removeChild(loadingOverlay);
                            }

                            // Re-enable pagination buttons
                            paginationLinks.forEach(link => {
                                link.style.pointerEvents = '';
                                link.style.opacity = '';
                            });

                            // Extract just the table content from the response
                            const temp = document.createElement('div');
                            temp.innerHTML = data;
                            const newTable = temp.querySelector('#inventoryTable');
                            const newPagination = temp.querySelector('#paginationContainer');

                            if (newTable) {
                                document.getElementById("inventoryTable").innerHTML = newTable.innerHTML;
                            }
                            if (newPagination) {
                                document.querySelector("#paginationContainer").innerHTML = newPagination.innerHTML;
                            }

                            // Update active state
                            document.querySelectorAll('.page-item').forEach(item => {
                                item.classList.remove('active');
                                if (item.querySelector('a')?.textContent == page) {
                                    item.classList.add('active');
                                }
                            });

                            // Smooth scroll to top of table
                            const table = document.querySelector('.table-responsive');
                            if (table) {
                                table.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            // Remove loading overlay on error
                            if (loadingOverlay.parentNode) {
                                loadingOverlay.parentNode.removeChild(loadingOverlay);
                            }

                            // Show error message
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'alert alert-danger mt-3';
                            errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading content. Please try again.
                    <button class="btn btn-sm btn-outline-danger ms-2" onclick="loadInventory(${page})">
                        <i class="fas fa-sync-alt"></i> Retry
                    </button>
                `;

                            const tableContainer = document.querySelector('.table-responsive');
                            if (tableContainer) {
                                tableContainer.parentNode.insertBefore(errorDiv, tableContainer.nextSibling);
                            }
                        });
                }

                // Handle browser back/forward buttons
                window.addEventListener('popstate', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const page = urlParams.get('page') || 1;
                    loadInventory(parseInt(page));
                });

                // Load initial page
                document.addEventListener("DOMContentLoaded", function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const page = urlParams.get('page') || 1;
                    loadInventory(parseInt(page));

                    // Auto-submit filters on change
                    const filterForm = document.querySelector('.table-header form');
                    const campusSel = document.getElementById('campus');
                    const sySel = document.getElementById('sy_inv');
                    if (filterForm && campusSel) {
                        campusSel.addEventListener('change', function() {
                            filterForm.submit();
                        });
                    }
                    if (filterForm && sySel) {
                        sySel.addEventListener('change', function() {
                            filterForm.submit();
                        });
                    }
                });

                // Function to view aircon details
                function viewAirconDetails(airconId, itemNumber, brand, model, type, capacity, serialNumber, location, status,
                    purchaseDate, warrantyExpiry, lastServiceDate, maintenanceSchedule, supplierInfo, installationDate,
                    energyEfficiency, powerConsumption, notes, purchasePrice, depreciatedValue, receiver, createdBy, dateCreated) {

                    // Populate modal fields
                    document.getElementById('view_aircon_id').textContent = airconId || 'N/A';
                    document.getElementById('view_item_number').textContent = itemNumber || 'N/A';
                    document.getElementById('view_brand').textContent = brand || 'N/A';
                    document.getElementById('view_model').textContent = model || 'N/A';
                    document.getElementById('view_type').textContent = type || 'N/A';
                    document.getElementById('view_capacity').textContent = capacity || 'N/A';
                    document.getElementById('view_serial_number').textContent = serialNumber || 'N/A';
                    document.getElementById('view_location').textContent = location || 'N/A';
                    document.getElementById('view_status').textContent = status || 'N/A';

                    // Format dates
                    document.getElementById('view_purchase_date').textContent = purchaseDate ? formatDate(purchaseDate) : 'N/A';
                    document.getElementById('view_warranty_expiry').textContent = warrantyExpiry ? formatDate(warrantyExpiry) : 'N/A';
                    document.getElementById('view_last_service_date').textContent = lastServiceDate ? formatDate(lastServiceDate) : 'N/A';
                    document.getElementById('view_maintenance_schedule').textContent = maintenanceSchedule || 'N/A';
                    document.getElementById('view_supplier_info').textContent = supplierInfo || 'N/A';
                    document.getElementById('view_installation_date').textContent = installationDate ? formatDate(installationDate) : 'N/A';

                    document.getElementById('view_energy_efficiency_rating').textContent = energyEfficiency || 'N/A';
                    document.getElementById('view_power_consumption').textContent = powerConsumption || 'N/A';
                    document.getElementById('view_notes').textContent = notes || 'N/A';

                    // Format currency
                    document.getElementById('view_purchase_price').textContent = purchasePrice ? '₱' + parseFloat(purchasePrice).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) : '₱0.00';
                    document.getElementById('view_depreciated_value').textContent = depreciatedValue ? '₱' + parseFloat(depreciatedValue).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) : '₱0.00';

                    document.getElementById('view_receiver').textContent = receiver || 'N/A';
                    document.getElementById('view_created_by').textContent = createdBy || 'N/A';
                    document.getElementById('view_date_created').textContent = dateCreated ? formatDateTime(dateCreated) : 'N/A';

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('viewAirconModal'));
                    modal.show();
                }

                // Helper function to format date
                function formatDate(dateString) {
                    if (!dateString || dateString === '0000-00-00') return 'N/A';
                    const date = new Date(dateString);
                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    };
                    return date.toLocaleDateString('en-US', options);
                }

                // Helper function to format datetime
                function formatDateTime(dateString) {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    return date.toLocaleDateString('en-US', options);
                }

                // Function to print aircon details
                function printAirconDetails() {
                    const printContent = document.querySelector('#viewAirconModal .modal-body').innerHTML;
                    const printWindow = window.open('', '', 'height=600,width=800');
                    printWindow.document.write('<html><head><title>Aircon Details</title>');
                    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
                    printWindow.document.write('<style>body { padding: 20px; } .card { margin-bottom: 20px; page-break-inside: avoid; }</style>');
                    printWindow.document.write('</head><body>');
                    printWindow.document.write('<h2 class="text-center mb-4">Aircon Unit Details</h2>');
                    printWindow.document.write(printContent);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    printWindow.print();
                }

                // Function to delete aircon
                function deleteAircon(airconId) {
                    if (confirm('Are you sure you want to delete this aircon unit? This action cannot be undone.')) {
                        // Create a form and submit it
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '../actions/delete_aircon.php';

                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'aircon_id';
                        input.value = airconId;

                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            </script>
        <?php endif; ?>

        <!--- Another aircon lost pagination -->
        <?php
        if (!$isAjax) {
            include '../includes/footer.php';
        } else {
            // For AJAX requests, output only the table content
            // Re-execute pagination logic for AJAX
            $records_per_page = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $records_per_page;

            // Re-process search and filters for AJAX
            $search_term_ajax = trim($_GET['search'] ?? '');
            $sy_inv_raw_ajax = $_GET['sy_inv'] ?? '';
            $campus_raw_ajax = strtoupper(trim($_GET['campus'] ?? ''));

            // Rebuild WHERE conditions for AJAX
            $inv_where_conditions_ajax = [];
            // Removed receiver filter - not applicable for aircon management
            // $inv_where_conditions_ajax[] = "pi.receiver = 'Property Custodian'";

            if (!empty($search_term_ajax)) {
                $search_escaped_ajax = $conn->real_escape_string($search_term_ajax);
                $inv_where_conditions_ajax[] = "(pi.brand LIKE '%$search_escaped_ajax%' OR pi.model LIKE '%$search_escaped_ajax%' OR pi.type LIKE '%$search_escaped_ajax%' OR pi.serial_number LIKE '%$search_escaped_ajax%' OR pi.location LIKE '%$search_escaped_ajax%')";
            }

            list($sy_inv_start_ajax, $sy_inv_end_ajax) = parse_school_year_range($sy_inv_raw_ajax);
            if ($sy_inv_start_ajax && $sy_inv_end_ajax) {
                $start_esc_ajax = $conn->real_escape_string($sy_inv_start_ajax);
                $end_esc_ajax = $conn->real_escape_string($sy_inv_end_ajax);
                $inv_where_conditions_ajax[] = "pi.date_created >= '$start_esc_ajax' AND pi.date_created <= '$end_esc_ajax'";
            }

            // Campus filter (BED/TED) for AJAX
            if ($campus_raw_ajax === 'BED' || $campus_raw_ajax === 'TED') {
                $campus_esc_ajax = $conn->real_escape_string($campus_raw_ajax);
                $inv_where_conditions_ajax[] = "TRIM(UPPER(pi.campus)) = '$campus_esc_ajax'";
            }

            $inv_where_ajax = !empty($inv_where_conditions_ajax) ? ' WHERE ' . implode(' AND ', $inv_where_conditions_ajax) : '';

            // Get total number of records
            $count_sql = "SELECT COUNT(*) as total FROM aircons pi $inv_where_ajax";
            $count_result = $conn->query($count_sql);
            $total_records = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_records / $records_per_page);

            // Get inventory data with pagination
            $sql = "SELECT pi.*, s.supplier_name 
                    FROM aircons pi 
                    LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id 
                    $inv_where_ajax
                    ORDER BY pi.date_created DESC
                    LIMIT $records_per_page OFFSET $offset";
            $result = $conn->query($sql);

            // Output only the table and pagination
            echo '<div id="inventoryTable">';
            echo '<table class="table table-hover mb-0">';
            echo '<thead class="table-dark">';
            echo '<tr>';
            echo '<th>Date</th>';
            echo '<th>Name</th>';
            echo '<th>Item Description</th>';
            echo '<th>Quantity</th>';
            echo '<th>Unit</th>';
            echo '<th>Notes</th>';
            echo '<th>Status</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead><tbody>';

            if ($result && $result->num_rows > 0) {
                $item_counter = ($page - 1) * $records_per_page + 1;
                while ($row = $result->fetch_assoc()) {
                    $status_class = 'success';
                    if ($row['status'] == 'Needs Repair') {
                        $status_class = 'warning';
                    } elseif ($row['status'] == 'Under Maintenance') {
                        $status_class = 'info';
                    } elseif ($row['status'] == 'Decommissioned') {
                        $status_class = 'danger';
                    }

                    echo '<tr>';
                    echo '<td data-label="Date">' . htmlspecialchars($row['date'] ?? 'N/A') . '</td>';
                    echo '<td data-label="Name">' . htmlspecialchars($row['name'] ?? 'N/A') . '</td>';
                    echo '<td data-label="Item Description">' . htmlspecialchars($row['item_description'] ?? 'N/A') . '</td>';
                    echo '<td data-label="Quantity">' . htmlspecialchars($row['quantity'] ?? 'N/A') . '</td>';
                    echo '<td data-label="Unit">' . htmlspecialchars($row['unit'] ?? 'N/A') . '</td>';
                    echo '<td data-label="Notes">' . htmlspecialchars($row['notes'] ?? 'N/A') . '</td>';
                    echo '<td data-label="Status"><span class="badge bg-' . $status_class . '">' . htmlspecialchars($row['status'] ?? 'N/A') . '</span></td>';
                    echo '<td data-label="Actions" class="actions">';
                    echo '<button class="btn btn-sm btn-outline-primary view-maintenance-btn" 
                                data-aircon-id="' . (int)$row['aircon_id'] . '"
                                data-brand="' . htmlspecialchars($row['brand'] ?? '', ENT_QUOTES) . '"
                                data-model="' . htmlspecialchars($row['model'] ?? '', ENT_QUOTES) . '"
                                data-serial="' . htmlspecialchars($row['serial_number'] ?? '', ENT_QUOTES) . '"
                                title="View Maintenance Records">
                                <i class="fas fa-calendar-alt"></i> View Records
                            </button> ';
                    echo '<button class="btn btn-sm btn-primary" title="View Details" onclick=\'viewAirconDetails('
                        . (int)$row['aircon_id'] . ', '
                        . json_encode($row['item_number'] ?? '') . ', '
                        . json_encode($row['brand'] ?? '') . ', '
                        . json_encode($row['model'] ?? '') . ', '
                        . json_encode($row['type'] ?? '') . ', '
                        . json_encode($row['capacity'] ?? '') . ', '
                        . json_encode($row['serial_number'] ?? '') . ', '
                        . json_encode($row['location'] ?? '') . ', '
                        . json_encode($row['status'] ?? '') . ', '
                        . json_encode($row['purchase_date'] ?? '') . ', '
                        . json_encode($row['warranty_expiry'] ?? '') . ', '
                        . json_encode($row['last_service_date'] ?? '') . ', '
                        . json_encode($row['maintenance_schedule'] ?? '') . ', '
                        . json_encode($row['supplier_name'] ?? '') . ', '
                        . json_encode($row['installation_date'] ?? '') . ', '
                        . json_encode($row['energy_efficiency_rating'] ?? '') . ', '
                        . json_encode($row['power_consumption'] ?? '') . ', '
                        . json_encode($row['notes'] ?? '') . ', '
                        . json_encode($row['purchase_price'] ?? '0') . ', '
                        . json_encode($row['depreciated_value'] ?? '0') . ', '
                        . json_encode($row['receiver'] ?? '') . ', '
                        . json_encode($row['created_by'] ?? '') . ', '
                        . json_encode($row['date_created'] ?? '')
                        . ')\'><i class="fas fa-eye"></i></button> ';
                    echo '<button class="btn btn-sm btn-info" title="Edit" onclick=\'openEditAirconModal('
                        . (int)$row['aircon_id'] . ', '
                        . json_encode($row['item_number'] ?? '') . ', '
                        . json_encode($row['category'] ?? '') . ', '
                        . json_encode($row['brand']) . ', '
                        . json_encode($row['model']) . ', '
                        . json_encode($row['type']) . ', '
                        . json_encode($row['capacity'] ?? '') . ', '
                        . json_encode($row['serial_number']) . ', '
                        . json_encode($row['location']) . ', '
                        . json_encode($row['status']) . ', '
                        . json_encode($row['purchase_date']) . ', '
                        . json_encode($row['warranty_expiry']) . ', '
                        . json_encode($row['last_service_date']) . ', '
                        . json_encode($row['maintenance_schedule']) . ', '
                        . (int)($row['supplier_id'] ?? 0) . ', '
                        . json_encode($row['installation_date']) . ', '
                        . json_encode($row['energy_efficiency_rating'] ?? '') . ', '
                        . json_encode($row['power_consumption'] ?? '') . ', '
                        . json_encode($row['notes']) . ', '
                        . json_encode($row['purchase_price'] ?? '0') . ', '
                        . json_encode($row['depreciated_value'] ?? '0')
                        . ')\'><i class="fas fa-edit"></i></button> ';

                    echo '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="8" class="text-center py-4"><i class="fas fa-snowflake fa-3x text-muted mb-3"></i><p class="text-muted">No aircon units found</p></td></tr>';
            }

            echo '</tbody></table></div>';

            // Output pagination
            if ($total_pages > 1) {
                echo '<nav><ul class="pagination justify-content-center mt-3" id="paginationContainer">';
                echo '<li class="page-item ' . (($page <= 1) ? 'disabled' : '') . '">';
                echo '<a class="page-link" href="#" onclick="loadInventory(' . ($page - 1) . '); return false;">&laquo;</a></li>';
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo '<li class="page-item ' . (($i == $page) ? 'active' : '') . '">';
                    echo '<a class="page-link" href="#" onclick="loadInventory(' . $i . '); return false;">' . $i . '</a></li>';
                }
                echo '<li class="page-item ' . (($page >= $total_pages) ? 'disabled' : '') . '">';
                echo '<a class="page-link" href="#" onclick="loadInventory(' . ($page + 1) . '); return false;">&raquo;</a></li>';
                echo '</ul></nav>';
            }
            exit;
        }
        ?>
        <script>
            // Add this to your aircon_list.php, preferably in the head section or before the closing body tag
            function openEditAirconModal(
                aircon_id, item_name, category, brand, model, type, capacity, serial_number, location, status,
                purchase_date, warranty_expiry, last_service_date, maintenance_schedule, supplier_id,
                installation_date, energy_efficient, power_consumption, notes, purchase_price, depreciated_value
            ) {
                // Format dates for input fields (YYYY-MM-DD)
                const formatDate = (dateString) => {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    // Check if date is valid
                    if (isNaN(date.getTime())) return '';
                    return date.toISOString().split('T')[0];
                };

                // Set form values
                document.getElementById('edit_aircon_id').value = aircon_id;
                document.getElementById('edit_item_name').value = item_name || '';  
                document.getElementById('edit_brand').value = brand || '';
                document.getElementById('edit_model').value = model || '';
                document.getElementById('edit_type').value = type || '';
                document.getElementById('edit_capacity').value = capacity || '';
                document.getElementById('edit_serial_number').value = serial_number || '';
                document.getElementById('edit_location').value = location || '';
                document.getElementById('edit_status').value = status || 'Working';
                document.getElementById('edit_purchase_date').value = formatDate(purchase_date);
                document.getElementById('edit_warranty_expiry').value = formatDate(warranty_expiry);
                document.getElementById('edit_last_service').value = formatDate(last_service_date);
                document.getElementById('edit_maintenance_schedule').value = formatDate(maintenance_schedule);
                document.getElementById('edit_supplier_id').value = supplier_id || '';
                document.getElementById('edit_installation_date').value = formatDate(installation_date);
                document.getElementById('edit_energy_efficient').value = energy_efficient || '';
                document.getElementById('edit_power_consumption').value = power_consumption || '';
                document.getElementById('edit_notes').value = notes || '';
                document.getElementById('edit_purchase_price').value = purchase_price || '';
                document.getElementById('edit_depreciated_value').value = depreciated_value || '';

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editAirconModal'));
                modal.show();
            }
        </script>

        <!-- Edit Aircon Modal -->
        <div class="modal fade" id="editAirconModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: var(--primary-green);">
                        <h5 class="modal-title text-white">Edit Aircon Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="../actions/edit_aircon.php" method="POST">
                        <input type="hidden" name="aircon_id" id="edit_aircon_id">
                        <div class="modal-body">
                            <!-- Basic Information -->
                            <div class="mb-3 pb-2 border-bottom">
                                <h6 class="mb-3 text-uppercase text-muted">Basic Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Item Number</label>
                                        <input type="text" name="item_name" id="edit_item_name" class="form-control" placeholder="e.g., AC-001">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Category</label>
                                        <select id="edit_category" name="category" class="form-select">
                                            <option value="">Select Category</option>
                                            <?php
                                            // Use the same organized categories from the main page
                                            if (isset($organized_categories) && !empty($organized_categories)) {
                                                foreach ($organized_categories as $main_category => $subcategories) {
                                                    echo '<optgroup label="' . htmlspecialchars($main_category) . '">';
                                                    foreach ($subcategories as $subcategory) {
                                                        echo '<option value="' . htmlspecialchars($subcategory) . '">' . htmlspecialchars($subcategory) . '</option>';
                                                    }
                                                    echo '</optgroup>';
                                                }
                                            } else {
                                                // Fallback options if no data available - display as bold headers only
                                                echo '<optgroup label="Property and Equipment"></optgroup>';
                                                echo '<optgroup label="Intangible Assets"></optgroup>';
                                                echo '<optgroup label="Office Supplies"></optgroup>';
                                                echo '<optgroup label="Medical Supplies"></optgroup>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Brand</label>
                                        <input type="text" name="brand" id="edit_brand" class="form-control" placeholder="e.g., Carrier, Daikin">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Model</label>
                                        <input type="text" name="model" id="edit_model" class="form-control" placeholder="e.g., 42QHC018">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Type</label>
                                        <select name="type" id="edit_type" class="form-select">
                                            <option value="">Select Type</option>
                                            <option value="Split">Split</option>
                                            <option value="Window">Window</option>
                                            <option value="Portable">Portable</option>
                                            <option value="Cassette">Cassette</option>
                                            <option value="Central">Central</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Capacity (BTU/hr)</label>
                                        <input type="text" name="capacity" id="edit_capacity" class="form-control" placeholder="e.g., 18,000">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Serial Number</label>
                                        <input type="text" name="serial_number" id="edit_serial_number" class="form-control" placeholder="Manufacturer serial number">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Location</label>
                                        <input type="text" name="location" id="edit_location" class="form-control" placeholder="e.g., Room 101, Office">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" id="edit_status" class="form-select">
                                            <option value="Working">Working</option>
                                            <option value="Needs Repair">Needs Repair</option>
                                            <option value="Under Maintenance">Under Maintenance</option>
                                            <option value="Decommissioned">Decommissioned</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Purchase Date</label>
                                        <input type="date" name="purchase_date" id="edit_purchase_date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Warranty Expiry</label>
                                        <input type="date" name="warranty_expiry" id="edit_warranty_expiry" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Last Service Date</label>
                                        <input type="date" name="last_service" id="edit_last_service" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Maintenance Schedule</label>
                                        <input type="date" name="maintenance_schedule" id="edit_maintenance_schedule" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Supplier</label>
                                        <select name="supplier_id" id="edit_supplier_id" class="form-select">
                                            <option value="">Select Supplier</option>
                                            <?php
                                            // Reset the result pointer to reuse the suppliers data
                                            if ($suppliers_result && $suppliers_result->num_rows > 0) {
                                                $suppliers_result->data_seek(0);
                                                while ($supplier = $suppliers_result->fetch_assoc()) {
                                                    echo '<option value="' . $supplier['supplier_id'] . '">' . htmlspecialchars($supplier['supplier_name']) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Installation Date</label>
                                        <input type="date" name="installation_date" id="edit_installation_date" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Energy Efficiency Rating</label>
                                        <input type="text" name="energy_efficient" id="edit_energy_efficient" class="form-control" placeholder="e.g., 5-star, A++">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Power Consumption (kW)</label>
                                        <input type="number" step="0.1" name="power_consumption" id="edit_power_consumption" class="form-control" placeholder="e.g., 1.5">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" id="edit_notes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Purchase Price (₱)</label>
                                        <input type="number" step="0.01" name="purchase_price" id="edit_purchase_price" class="form-control" placeholder="0.00">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Depreciated Value (₱)</label>
                                        <input type="number" step="0.01" name="depreciated_value" id="edit_depreciated_value" class="form-control" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>