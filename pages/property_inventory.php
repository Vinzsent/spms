<?php
$pageTitle = 'Property Inventory Management';
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

// Search parameter
$search_term = trim($_GET['search'] ?? '');

list($sy_inv_start, $sy_inv_end)   = parse_school_year_range($sy_inv_raw);
list($sy_recv_start, $sy_recv_end) = parse_school_year_range($sy_recv_raw);
list($sy_logs_start, $sy_logs_end) = parse_school_year_range($sy_logs_raw);

// Build optional WHERE conditions per query based on per-table School Year
$inv_where_conditions = [];
$recv_where_conditions = [];
$logs_where_conditions = [];

// Add receiver filter for Property Custodian (use alias 'i' to match later queries)
$inv_where_conditions[] = "i.receiver = 'Property Custodian'";
$recv_where_conditions[] = "st.receiver = 'Property Custodian'";
$logs_where_conditions[] = "sl.receiver = 'Property Custodian'";

// Add search filter if search term is provided
if (!empty($search_term)) {
    $search_escaped = $conn->real_escape_string($search_term);
    $inv_where_conditions[] = "i.item_name LIKE '%$search_escaped%'";
}

// Add school year filters if provided
if ($sy_inv_start && $sy_inv_end) {
    $start_esc = $conn->real_escape_string($sy_inv_start);
    $end_esc   = $conn->real_escape_string($sy_inv_end);
    $inv_where_conditions[] = "i.date_created >= '$start_esc' AND i.date_created <= '$end_esc'";
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
        FROM property_inventory pi 
        LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id
        ORDER BY pi.date_created DESC";
$result = $conn->query($sql);

// Get inventory data
$sql = "SELECT i.*, s.supplier_name 
        FROM property_inventory i 
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

// Get stock movement logs
$stock_logs_sql = "SELECT sl.*, pi.item_name, s.supplier_name 
                   FROM property_stock_logs sl 
                   LEFT JOIN property_inventory pi ON sl.inventory_id = pi.inventory_id 
                   LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id 
                   $logs_where
                   ORDER BY sl.date_created DESC LIMIT 50";
$stock_logs_result = $conn->query($stock_logs_sql);

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
$low_stock_count = 0;
$out_of_stock_count = 0;

if ($stats_result) {
    while ($row = $stats_result->fetch_assoc()) {
        if ($row['current_stock'] <= $row['reorder_level']) {
            $low_stock_count++;
        }
        if ($row['current_stock'] == 0) {
            $out_of_stock_count++;
        }
    }
    $stats_result->data_seek(0); // Reset pointer
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
            --accent-orange: #ff6b35;
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
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

                <li><a href="property_inventory.php" class="nav-link active">
                        <i class="fas fa-boxes"></i> Property Inventory
                    </a></li>

                <li><a href="borrowers_forms.php" class="nav-link">
                        <i class="fas fa-hand-holding"></i> Borrower Forms
                    </a></li>

                <li><a href="property_issuance.php" class="nav-link">
                        <i class="fas fa-hand-holding"></i> Property Issuance
                    </a></li>
                <li><a href="../logout.php" class="nav-link logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h1>Property Inventory Management</h1>
            <p>Track supplies, monitor stock levels, and manage inventory movements</p>
        </div>

        <!-- Low Stock Alerts -->
        <?php if ($low_stock_count > 0 || $out_of_stock_count > 0): ?>
            <div class="alert-card <?= $out_of_stock_count > 0 ? '' : 'warning' ?>">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-1">Stock Alert</h5>
                        <p class="mb-0">
                            <?php if ($out_of_stock_count > 0): ?>
                                <strong><?= $out_of_stock_count ?></strong> items are out of stock
                            <?php endif; ?>
                            <?php if ($low_stock_count > 0): ?>
                                <?= $out_of_stock_count > 0 ? ' and ' : '' ?>
                                <strong><?= $low_stock_count ?></strong> items are running low
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon items">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-number"><?= $total_items ?></div>
                <div class="stat-label">Total Items</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon low-stock">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number"><?= $low_stock_count ?></div>
                <div class="stat-label">Low Stock Items</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon out-of-stock">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?= $out_of_stock_count ?></div>
                <div class="stat-label">Out of Stock</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon movements">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="stat-number"><?= $stock_logs_result ? $stock_logs_result->num_rows : 0 ?></div>
                <div class="stat-label">Recent Movements</div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="table-container">
            <div class="table-header">
                <h3>Inventory Items</h3>
                <div class="d-flex align-items-end gap-2">
                    <form method="GET" class="d-flex align-items-end gap-2 mb-0">
                        <div class="search-input">
                            <label for="search" class="form-label mb-0 text-white">Search Item</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Search by item name..." value="<?= htmlspecialchars($search_term) ?>">
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
                        <div class="pt-4">
                            <button type="submit" class="btn btn-search">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if (!empty($search_term) || !empty($sy_inv_raw)): ?>
                                <a href="property_inventory.php" class="btn btn-outline-light ms-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
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

            <?php
            // Pagination settings
            $records_per_page = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $records_per_page;

            // Get total number of records
            $count_sql = "SELECT COUNT(*) as total FROM property_inventory i $inv_where";
            $count_result = $conn->query($count_sql);
            $total_records = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_records / $records_per_page);

            // Get inventory data with pagination (respect filters and join supplier)
            $sql = "SELECT i.*, s.supplier_name 
        FROM property_inventory i 
        LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
        $inv_where
        ORDER BY i.date_created DESC
        LIMIT $records_per_page OFFSET $offset";
            $result = $conn->query($sql);
            ?>

            <style>
                /* Responsive table styles */
                .table-responsive {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
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
            </style>

            <div class="table-responsive">
                <div id="inventoryTable">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Item Name</th>
                                <th>Current Stock</th>
                                <th>Unit</th>
                                <th>Brand</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Last Updated</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $stock_level = 'normal';
                                    if ($row['current_stock'] == 0) {
                                        $stock_level = 'out';
                                    } elseif ($row['current_stock'] <= $row['reorder_level']) {
                                        $stock_level = 'critical';
                                    } elseif ($row['current_stock'] <= ($row['reorder_level'] * 1.5)) {
                                        $stock_level = 'low';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                                        <td class="text-center"><strong><?= $row['current_stock'] ?></strong></td>
                                        <td><?= $row['unit'] ?></td>
                                        <td><?= htmlspecialchars($row['brand']) ?></td>
                                        <td><?= htmlspecialchars($row['color']) ?></td>
                                        <td><?= htmlspecialchars($row['size']) ?></td>
                                        <td><?= date('M d, Y', strtotime($row['date_updated'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $stock_level == 'out' ? 'danger' : ($stock_level == 'critical' ? 'warning' : 'success') ?>">
                                                <?= ucfirst($stock_level) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success" title="Stock In" onclick="stockIn(<?= $row['inventory_id'] ?>)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" title="Stock Out" onclick="stockOut(<?= $row['inventory_id'] ?>)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" title="Edit"
                                                onclick="openEditInventoryModal(
                                                <?= (int)$row['inventory_id'] ?>,
                                                <?= json_encode($row['item_name']) ?>,
                                                <?= json_encode($row['category']) ?>,
                                                <?= json_encode($row['unit']) ?>,
                                                <?= (int)$row['current_stock'] ?>,
                                                <?= (int)$row['reorder_level'] ?>,
                                                <?= (int)$row['supplier_id'] ?>,
                                                <?= (float)$row['unit_cost'] ?>,
                                                <?= json_encode($row['description'] ?? '') ?>,
                                                <?= (int)($row['quantity'] ?? 0) ?>,
                                                <?= json_encode($row['receiver'] ?? '') ?>,
                                                <?= json_encode($row['status'] ?? 'Active') ?>,
                                                <?= json_encode($row['received_notes'] ?? '') ?>
                                                <?= json_encode($row['type'] ?? '') ?>
                                                <?= json_encode($row['brand'] ?? '') ?>
                                                <?= json_encode($row['size'] ?? '') ?>
                                                <?= json_encode($row['color'] ?? '') ?>
                                            )">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No inventory items found</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                            Add First Item
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


                <div class="table-container">
                    <div class="table-header">
                        <h3>Acquired Supplies</h3>
                        <form method="GET" class="d-flex align-items-end gap-2">
                            <div>
                                <label for="sy_recv" class="form-label mb-0 text-white">School Year</label>
                                <select id="sy_recv" name="sy_recv" class="form-select" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach ($sy_years as $sy): ?>
                                        <option value="<?= htmlspecialchars($sy) ?>" <?= ($sy_recv_raw === $sy) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sy) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="pt-4">
                                <?php if (!empty($sy_recv_raw)): ?>
                                    <a href="property_inventory.php?<?= http_build_query(array_diff_key($_GET, ['sy_recv' => true])) ?>" class="btn btn-outline-light">Reset</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date Received</th>
                                    <th>Invoice Number</th>
                                    <th>Supplier</th>
                                    <th>Sales Type</th>
                                    <th>Category</th>
                                    <th>Item Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Unit Price</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result1 && $result1->num_rows > 0): ?>
                                    <?php while ($row = $result1->fetch_assoc()): ?>
                                        <?php
                                        if ($row['sales_type'] == 0) {
                                        } elseif ($row['sales_type']) {
                                        } elseif ($row['sales_type']) {
                                        }
                                        ?>
                                        <tr
                                            data-supplier-id="<?= (int)($row['supplier_id'] ?? 0) ?>"
                                            data-category="<?= htmlspecialchars($row['category'] ?? '') ?>"
                                            data-description="<?= htmlspecialchars($row['item_name'] ?? '') ?>"
                                            data-quantity="<?= (int)($row['quantity'] ?? 0) ?>"
                                            data-unit="<?= htmlspecialchars($row['unit'] ?? '') ?>"
                                            data-unit-price="<?= htmlspecialchars($row['unit_price'] ?? '0.00') ?>"
                                            data-invoice="<?= htmlspecialchars($row['invoice_no'] ?? '') ?>">
                                            <td><?= date('M d, Y', strtotime($row['date_created'])) ?></td>
                                            <td><?= htmlspecialchars($row['invoice_no']) ?></td>
                                            <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                            <td><?= htmlspecialchars($row['sales_type']) ?></td>
                                            <td><?= htmlspecialchars($row['category']) ?></td>
                                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                                            <td><?= htmlspecialchars($row['unit']) ?></td>
                                            <td>₱ <?= htmlspecialchars($row['unit_price']) ?></td>
                                            <td>₱ <?= htmlspecialchars($row['total_amount']) ?></td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success mark-received-btn" data-bs-toggle="modal" data-bs-target="#receivedModal" title="Mark as Received"
                                                    data-transaction-id="<?= $row['procurement_id'] ?>"
                                                    data-item-name="<?= htmlspecialchars($row['item_name']) ?>"
                                                    data-category="<?= htmlspecialchars($row['category']) ?>"
                                                    data-quantity="<?= $row['quantity'] ?>"
                                                    data-unit="<?= htmlspecialchars($row['unit']) ?>"
                                                    data-supplier-id="<?= $row['supplier_id'] ?>"
                                                    data-unit-price="<?= $row['unit_price'] ?>"
                                                    data-invoice="<?= htmlspecialchars($row['invoice_number'] ?? '') ?>"
                                                    data-status="<?= htmlspecialchars($row['status']) ?>"
                                                    data-receiver="<?= htmlspecialchars($row['receiver']) ?>">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>


                                                <!-- Add to Inventory button (submits mapped data to existing add endpoint)
                                <button type="button" class="btn btn-sm btn-primary" onclick="addToInventoryFromRow(this)" title="Add to Inventory">
                                    <i class="fas fa-plus-circle"></i>
                                </button> -->
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center py-4">
                                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No recieved items found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- Stock Movement Logs -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Recent Stock Movements</h3>
                        <div class="d-flex align-items-end gap-2">
                            <form method="GET" class="d-flex align-items-end gap-2 mb-0">
                                <div>
                                    <label for="sy_logs" class="form-label mb-0 text-white">School Year</label>
                                    <select id="sy_logs" name="sy_logs" class="form-select" onchange="this.form.submit()">
                                        <option value="">All</option>
                                        <?php foreach ($sy_years as $sy): ?>
                                            <option value="<?= htmlspecialchars($sy) ?>" <?= ($sy_logs_raw === $sy) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sy) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="pt-4">
                                    <?php if (!empty($sy_logs_raw)): ?>
                                        <a href="property_inventory.php?<?= http_build_query(array_diff_key($_GET, ['sy_logs' => true])) ?>" class="btn btn-outline-light">Reset</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <button class="btn btn-add" onclick="viewAllMovements()">
                                <i class="fas fa-list"></i> View All
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Previous Stock</th>
                                    <th>New Stock</th>
                                    <th>Receiver</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($stock_logs_result && $stock_logs_result->num_rows > 0): ?>
                                    <?php while ($log = $stock_logs_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('M d, Y H:i', strtotime($log['date_created'])) ?></td>
                                            <td><?= htmlspecialchars($log['item_name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $log['movement_type'] == 'IN' ? 'success' : 'warning' ?>">
                                                    <?= $log['movement_type'] ?>
                                                </span>
                                            </td>
                                            <td><?= $log['quantity'] ?></td>
                                            <td><?= $log['previous_stock'] ?></td>
                                            <td><?= $log['new_stock'] ?></td>
                                            <td><?= htmlspecialchars($log['receiver'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($log['notes']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No stock movements recorded</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Inventory Modal -->
            <div class="modal fade" id="addInventoryModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Inventory Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="../actions/add_property.php" method="POST">
                            <input type="hidden" name="receiver" value="Property Custodian">
                            <input type="hidden" name="status" value="Active">
                            <div class="modal-body">
                                <!-- Basic Information -->
                                <div class="mb-3 pb-2 border-bottom">
                                    <h6 class="mb-3 text-uppercase text-muted">Basic Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                            <input type="text" name="item_name" class="form-control" required>
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
                                            <input type="text" name="brand" class="form-control" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Size</label>
                                            <input type="size" name="size" class="form-control">
                                             <small class="text-muted" style="font-size: 12px;">Leave blank if not applicable</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Color</label>
                                            <input type="text" name="color" class="form-control">
                                             <small class="text-muted" style="font-size: 12px;">Leave blank if not applicable</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Type</label>
                                            <input type="text" name="type" class="form-control">
                                             <small class="text-muted" style="font-size: 12px;">Leave blank if not applicable</small>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock & Unit -->
                                <div class="mb-3 pb-2 border-bottom">
                                    <h6 class="mb-3 text-uppercase text-muted">Stock & Unit</h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Initial Stock <span class="text-danger">*</span></label>
                                            <input type="number" name="current_stock" class="form-control" required min="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Reorder Level</label>
                                            <input type="number" name="reorder_level" class="form-control" required min="0" value="0">
                                            <small class="text-muted" style="font-size: 12px;">Leave blank if not applicable</small>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                                            <select name="unit" class="form-select" required>
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
                                            <input type="hidden" name="quantity" class="form-control" required min="0">
                                        <div class="col-md-3">
                                            <label class="form-label">Unit Cost <span class="text-danger">*</span></label>
                                            <input type="number" name="unit_cost" step="0.01" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Supplier & Location -->
                                <div class="mb-2">
                                    <h6 class="mb-3 text-uppercase text-muted">Supplier & Location</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                            <select name="supplier_id" class="form-select" required>
                                                <option value="">Select Supplier</option>
                                                <?php
                                                if ($suppliers_result) {
                                                    $suppliers_result->data_seek(0);
                                                    while ($supplier = $suppliers_result->fetch_assoc()):
                                                ?>
                                                        <option value="<?= $supplier['supplier_id'] ?>">
                                                            <?= htmlspecialchars($supplier['supplier_name']) ?>
                                                        </option>
                                                <?php
                                                    endwhile;
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Location <span class="text-danger">*</span></label>
                                            <input type="text" name="location" class="form-control" placeholder="e.g., Storage Room A">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Item</button>
                            </div>
                        </form>
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
                        <form id="stockMovementForm" action="../actions/stock_movement.php" method="POST">
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
                                    <input type="hidden" name="quantity" class="form-control" required min="1">
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
                });

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
                    fetch("property_inventory.php?" + currentParams.toString())
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
                        url: '../pages/get_property_inventory_item.php',
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
                    (function(){
                        const catSelect = document.getElementById('accountSelect');
                        if (catSelect) {
                            let found = false;
                            for (let i = 0; i < catSelect.options.length; i++) {
                                if (String(catSelect.options[i].value) === String(category)) { found = true; break; }
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
                    (function(){
                        const unitSelect = document.getElementById('ei_unit');
                        if (unitSelect) {
                            let found = false;
                            for (let i = 0; i < unitSelect.options.length; i++) {
                                if (String(unitSelect.options[i].value) === String(unit)) { found = true; break; }
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

                    // Fetch the page content
                    fetch("property_inventory.php?" + currentParams.toString())
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
                });
            </script>
        <?php endif; ?>

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

            // Rebuild WHERE conditions for AJAX
            $inv_where_conditions_ajax = [];
            $inv_where_conditions_ajax[] = "pi.receiver = 'Property Custodian'";

            if (!empty($search_term_ajax)) {
                $search_escaped_ajax = $conn->real_escape_string($search_term_ajax);
                $inv_where_conditions_ajax[] = "pi.item_name LIKE '%$search_escaped_ajax%'";
            }

            list($sy_inv_start_ajax, $sy_inv_end_ajax) = parse_school_year_range($sy_inv_raw_ajax);
            if ($sy_inv_start_ajax && $sy_inv_end_ajax) {
                $start_esc_ajax = $conn->real_escape_string($sy_inv_start_ajax);
                $end_esc_ajax = $conn->real_escape_string($sy_inv_end_ajax);
                $inv_where_conditions_ajax[] = "pi.date_created >= '$start_esc_ajax' AND pi.date_created <= '$end_esc_ajax'";
            }

            $inv_where_ajax = !empty($inv_where_conditions_ajax) ? ' WHERE ' . implode(' AND ', $inv_where_conditions_ajax) : '';

            // Get total number of records
            $count_sql = "SELECT COUNT(*) as total FROM property_inventory pi $inv_where_ajax";
            $count_result = $conn->query($count_sql);
            $total_records = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_records / $records_per_page);

            // Get inventory data with pagination
            $sql = "SELECT pi.*, s.supplier_name 
            FROM property_inventory pi 
            LEFT JOIN supplier s ON pi.supplier_id = s.supplier_id 
            $inv_where_ajax
            ORDER BY pi.date_created DESC
            LIMIT $records_per_page OFFSET $offset";
            $result = $conn->query($sql);

            // Output only the table and pagination
            echo '<div id="inventoryTable">';
            echo '<table class="table table-hover mb-0">';
            echo '<thead class="table-dark">';
            echo '<tr><th>Item Name</th><th>Current Stock</th><th>Unit</th><th>Brand</th><th>Color</th><th>Size</th><th>Last Updated</th><th>Status</th><th>Actions</th></tr>';
            echo '</thead><tbody>';

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $stock_level = 'normal';
                    if ($row['current_stock'] == 0) {
                        $stock_level = 'out';
                    } elseif ($row['current_stock'] <= $row['reorder_level']) {
                        $stock_level = 'critical';
                    } elseif ($row['current_stock'] <= ($row['reorder_level'] * 1.5)) {
                        $stock_level = 'low';
                    }

                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['item_name']) . '</td>';
                    echo '<td class="text-center"><strong>' . $row['current_stock'] . '</strong></td>';
                    echo '<td>' . $row['unit'] . '</td>';
                    echo '<td>' . htmlspecialchars($row['brand']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['color']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['size']) . '</td>';
                    echo '<td>' . date('M d, Y', strtotime($row['date_updated'])) . '</td>';
                    echo '<td><span class="badge bg-' . ($stock_level == 'out' ? 'danger' : ($stock_level == 'critical' ? 'warning' : 'success')) . '">' . ucfirst($stock_level) . '</span></td>';
                    echo '<td>';
                    echo '<button class="btn btn-sm btn-success" title="Stock In" onclick="stockIn(' . $row['inventory_id'] . ')"><i class="fas fa-plus"></i></button>';
                    echo '<button class="btn btn-sm btn-warning" title="Stock Out" onclick="stockOut(' . $row['inventory_id'] . ')"><i class="fas fa-minus"></i></button>';
                    echo '<button class="btn btn-sm btn-info" title="Edit" onclick=\'openEditInventoryModal('
                        . (int)$row['inventory_id'] . ', '
                        . json_encode($row['item_name']) . ', '
                        . json_encode($row['category']) . ', '
                        . json_encode($row['unit']) . ', '
                        . (int)$row['current_stock'] . ', '
                        . (int)$row['reorder_level'] . ', '
                        . json_encode($row['location'] ?? '') . ', '
                        . json_encode((int)$row['supplier_id']) . ', '
                        . json_encode((float)$row['unit_cost']) . ', '
                        . json_encode($row['description'] ?? '') . ', '
                        . json_encode((int)($row['quantity'] ?? 0)) . ', '
                        . json_encode($row['receiver'] ?? '') . ', '
                        . json_encode($row['status'] ?? 'Active') . ', '
                        . json_encode($row['received_notes'] ?? '') . ', '
                        . json_encode($row['type'] ?? '') . ', '
                        . json_encode($row['brand'] ?? '') . ', '
                        . json_encode($row['size'] ?? '') . ', '
                        . json_encode($row['color'] ?? '')
                        . ')\'><i class="fas fa-edit"></i></button>';
                    echo '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="9" class="text-center py-4"><i class="fas fa-boxes fa-3x text-muted mb-3"></i><p class="text-muted">No inventory items found</p></td></tr>';
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