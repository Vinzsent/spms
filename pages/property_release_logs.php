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

// Add search filter if search term is provided (for release_logs)
if (!empty($search_term)) {
    $search_escaped = $conn->real_escape_string($search_term);
    $inv_where_conditions[] = "(i.facility_name LIKE '%$search_escaped%' 
        OR i.item_description LIKE '%$search_escaped%' 
        OR i.campus LIKE '%$search_escaped%' 
        OR i.notes LIKE '%$search_escaped%')";
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

$currYear = (int)date('Y');
$minYear = $currYear - 10;
$sy_years = [];
for ($y = $currYear; $y >= $minYear; $y--) {
    $sy_years[] = $y . '-' . ($y + 1);
}

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

if (!isset($organized_categories)) {
    $organized_categories = [];
}
if (!isset($suppliers_result)) {
    $suppliers_result = null;
}

// Get release logs records
$sql = "SELECT * FROM release_logs ORDER BY date_created DESC";
$logs_result = $conn->query($sql);
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

            .alert .flex-grow-1 {
                max-width: 100%;
                margin-right: 30px;
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
                <li><a href="property_inventory.php" class="nav-link">
                        <i class="fas fa-boxes"></i> Property Inventory
                    </a></li>
                <li><a href="rooms_inventory.php" class="nav-link">
                        <i class="fas fa-door-open"></i> Rooms Inventory
                    </a></li>
                    <li>
                        <a href="#releaseRecordsSubmenu" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="releaseRecordsSubmenu">
                            <i class="fas fa-file"></i> Release Records <i class="fas fa-chevron-down ms-1"></i>
                        </a>
                        <ul class="collapse list-unstyled ps-4" id="releaseRecordsSubmenu">
                            <li>
                                <a href="property_release_logs.php" class="nav-link active">Property Release Logs</a>
                            </li>
                            <li>
                                <a href="bulb_release_logs.php" class="nav-link">Bulb Release Logs</a>
                            </li>
                        </ul>
                    </li>
                <li><a href="aircon_list.php" class="nav-link">
                        <i class="fas fa-snowflake"></i> Aircons
                    </a></li>
                <li><a href="office_inventory.php" class="nav-link">
                        <i class="fas fa-building"></i> Office Inventory Form
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

        <!-- Release Logs Table -->
        <div class="table-container">
            <div class="table-header">
                <h3>Release Logs</h3>
                <div class="d-flex align-items-end gap-2">
                    <form method="GET" class="d-flex align-items-end gap-2 mb-0">
                        <div class="search-input">
                            <label for="search" class="form-label mb-0 text-white">Search Release Logs</label>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Search by Name..." value="<?= htmlspecialchars($search_term) ?>">
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
                                <option value="">All Campuses</option>
                                <option value="BED" <?= ($campus_raw === 'BED') ? 'selected' : '' ?>>BED - Basic Education Department</option>
                                <option value="TED" <?= ($campus_raw === 'TED') ? 'selected' : '' ?>>TED - Tertiary Education Department</option>
                            </select>
                        </div>
                        <div class="pt-4 d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-search">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if (!empty($search_term) || !empty($sy_inv_raw) || !empty($campus_raw)): ?>
                                <a href="property_release_logs.php" class="btn btn-outline-light">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <a class="btn btn-success" href="../actions/export_release_logs.php?search=<?= urlencode($search_term) ?>&sy_inv=<?= urlencode($sy_inv_raw) ?>&campus=<?= urlencode($campus_raw) ?>">
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
            $count_sql = "SELECT COUNT(*) as total FROM release_logs i $inv_where";
            $count_result = $conn->query($count_sql);
            $total_records = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_records / $records_per_page);

            // Get inventory data with pagination (respect filters and join supplier)
            $sql = "SELECT i.* FROM release_logs i $inv_where ORDER BY i.date_created DESC LIMIT $records_per_page OFFSET $offset";
            $result = $conn->query($sql);
            ?>


            <!-- Release Logs Table -->
            <div class="table-responsive">
                <div id="inventoryTable">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Name & Facility</th>
                                <th>Item Description</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Campus</th>
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
                                        <td data-label="Name"><?= htmlspecialchars($row['facility_name'] ?? 'N/A') ?></td>
                                        <td data-label="Item Description"><?= htmlspecialchars($row['item_description'] ?? 'N/A') ?></td>
                                        <td data-label="Quantity"><?= htmlspecialchars($row['quantity'] ?? 'N/A') ?></td>
                                        <td data-label="Unit"><?= htmlspecialchars($row['unit'] ?? 'N/A') ?></td>
                                        <td data-label="Campus"><?= htmlspecialchars($row['campus'] ?? 'N/A') ?></td>
                                        <td data-label="Notes"><?= htmlspecialchars($row['notes'] ?? 'N/A') ?></td>
                                        <td data-label="Actions" class="actions">
                                            <button type="button"
                                                class="btn btn-sm btn-info edit-release-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editReleaseModal"
                                                title="Edit Release Log"
                                                data-logs_id="<?= (int)($row['logs_id'] ?? 0) ?>"
                                                data-date="<?= htmlspecialchars($row['date'] ?? '', ENT_QUOTES) ?>"
                                                data-facility-name="<?= htmlspecialchars($row['facility_name'] ?? '', ENT_QUOTES) ?>"
                                                data-item-description="<?= htmlspecialchars($row['item_description'] ?? '', ENT_QUOTES) ?>"
                                                data-quantity="<?= htmlspecialchars($row['quantity'] ?? '', ENT_QUOTES) ?>"
                                                data-unit="<?= htmlspecialchars($row['unit'] ?? '', ENT_QUOTES) ?>"
                                                data-notes="<?= htmlspecialchars($row['notes'] ?? '', ENT_QUOTES) ?>"
                                                data-campus="<?= htmlspecialchars($row['campus'] ?? '', ENT_QUOTES) ?>">
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

            <!-- Add New Aircon Modal -->
            <div class="modal fade" id="addInventoryModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: var(--primary-green);">
                            <h5 class="modal-title text-white">Add New Aircon</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="../actions/add_release.php" method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['id'] ?? 1; ?>">
                            <div class="modal-body">
                                <!-- Basic Information -->
                                <div class="mb-3 pb-2 border-bottom">
                                    <h6 class="mb-3 text-uppercase text-muted">Basic Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="date">Date <span class="text-danger">*</span></label>
                                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="facility_name">Facility Name <span class="text-danger">*</span></label>
                                            <input type="text" name="facility_name" class="form-control">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="item_description">Item Description <span class="text-danger">*</span></label>
                                            <input type="text" name="item_description" class="form-control">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" name="quantity" class="form-control">

                                        </div>
                                        <div class="col-md-6">
                                            <label for="unit">Unit <span class="text-danger">*</span></label>
                                            <select name="unit" class="form-select">
                                                <option value=""selected disabled>Choose Unit...</option>
                                                <option value="set">Set</option>
                                                <option value="pair">Pair</option>
                                                <option value="piece">Piece</option>
                                                <option value="box">Box</option>
                                                <option value="roll">Roll</option>
                                                <option value="sheet">Sheet</option>
                                                <option value="bag">Bag</option>
                                                <option value="can">Can</option>
                                                <option value="gallon">Gallon</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Campus <span class="text-danger">*</span></label>
                                            <select name="campus" class="form-select">
                                                <option value="TED">TED</option>
                                                <option value="BED">BED</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Notes</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
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

            <!-- Edit Release Log Modal (inlined from modals/edit_release_log.php) -->
            <div class="modal fade" id="editReleaseModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: var(--primary-green);">
                            <h5 class="modal-title text-white">Edit Release Log</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="../actions/update_release.php" method="POST">
                            <input type="hidden" name="logs_id" id="edit_release_id" class="form-control mb-3" readonly placeholder="Log ID">

                            <div class="modal-body">
                                <!-- Basic Information -->
                                <div class="mb-3 pb-2 border-bottom">
                                    <h6 class="mb-3 text-uppercase text-muted">Basic Information</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="edit_date">Date <span class="text-danger">*</span></label>
                                            <input type="date" name="date" id="edit_date" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_facility_name">Facility Name <span class="text-danger">*</span></label>
                                            <input type="text" name="facility_name" id="edit_facility_name" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_item_description">Item Description <span class="text-danger">*</span></label>
                                            <input type="text" name="item_description" id="edit_item_description" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_quantity">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" name="quantity" id="edit_quantity" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_unit">Unit <span class="text-danger">*</span></label>
                                            <select name="unit" id="edit_unit" class="form-select">
                                                <option value="" selected disabled>Choose Unit...</option>
                                                <option value="Set">Set</option>
                                                <option value="Pair">Pair</option>
                                                <option value="Pieces">Pieces</option>
                                                <option value="Piece">Piece</option>
                                                <option value="Box">Box</option>
                                                <option value="Roll">Roll</option>
                                                <option value="Sheet">Sheet</option>
                                                <option value="Bag">Bag</option>
                                                <option value="Can">Can</option>
                                                <option value="Gallon">Gallon</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_campus" class="form-label">Campus <span class="text-danger">*</span></label>
                                            <select name="campus" id="edit_campus" class="form-select">
                                                <option value="TED">TED</option>
                                                <option value="BED">BED</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="edit_notes" class="form-label">Notes</label>
                                            <textarea name="notes" id="edit_notes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
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

                    // Auto-submit form when Campus or School Year filters change
                    $('#campus, #sy_inv').on('change', function() {
                        $(this).closest('form').submit();
                    });

                    // Edit Release Log button handler
                    $(document).on('click', '.edit-release-btn', function() {
                        var button = $(this);

                        $('#edit_release_id').val(button.data('logs_id') || button.attr('data-logs_id') || '');
                        $('#edit_date').val(button.data('date') || button.attr('data-date') || '');
                        $('#edit_facility_name').val(button.attr('data-facility-name') || '');
                        $('#edit_item_description').val(button.attr('data-item-description') || '');
                        $('#edit_quantity').val(button.data('quantity') || button.attr('data-quantity') || '');
                        $('#edit_unit').val(button.data('unit') || button.attr('data-unit') || '');
                        $('#edit_notes').val(button.attr('data-notes') || '');

                        var campus = (button.data('campus') || button.attr('data-campus') || '').toString().toUpperCase();
                        if (campus === 'BED' || campus === 'TED') {
                            $('#edit_campus').val(campus);
                        } else {
                            $('#edit_campus').val('');
                        }
                    });
                });

                function showSessionMessageAlert() {
                    if (sessionMessage) {
                        alert(' Success!\n\n' + sessionMessage);
                    } else if (sessionError) {
                        alert(' Error!\n\n' + sessionError);
                    }
                }

                function performSearch(searchTerm) {
                    var url = new URL(window.location);
                    if (searchTerm && searchTerm.length > 0) {
                        url.searchParams.set('search', searchTerm);
                    } else {
                        url.searchParams.delete('search');
                    }
                    url.searchParams.delete('page');
                    window.location = url.toString();
                }

                function loadInventory(page) {
                    var targetPage = page || 1;
                    var url = new URL(window.location);
                    url.searchParams.set('page', targetPage);
                    window.location = url.toString();
                }
            </script>
<?php endif; ?>
<?php
if (!$isAjax) {
    include '../includes/footer.php';
}
?>