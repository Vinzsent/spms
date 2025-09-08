<?php
$pageTitle = 'Inventory Management';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

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

list($sy_inv_start, $sy_inv_end)   = parse_school_year_range($sy_inv_raw);
list($sy_recv_start, $sy_recv_end) = parse_school_year_range($sy_recv_raw);
list($sy_logs_start, $sy_logs_end) = parse_school_year_range($sy_logs_raw);

// Build optional WHERE conditions per query based on per-table School Year
$inv_where = '';
$recv_where = '';
$logs_where = '';
if ($sy_inv_start && $sy_inv_end) {
    $start_esc = $conn->real_escape_string($sy_inv_start);
    $end_esc   = $conn->real_escape_string($sy_inv_end);
    $inv_where = " WHERE i.date_created >= '$start_esc' AND i.date_created <= '$end_esc'";
}
if ($sy_recv_start && $sy_recv_end) {
    $start_esc = $conn->real_escape_string($sy_recv_start);
    $end_esc   = $conn->real_escape_string($sy_recv_end);
    $recv_where = " WHERE st.date_received >= '$start_esc' AND st.date_received <= '$end_esc'";
}
if ($sy_logs_start && $sy_logs_end) {
    $start_esc = $conn->real_escape_string($sy_logs_start);
    $end_esc   = $conn->real_escape_string($sy_logs_end);
    $logs_where = " WHERE sl.date_created >= '$start_esc' AND sl.date_created <= '$end_esc'";
}

// Get search parameters
$search_item = $_GET['search_item'] ?? '';
$search_unit = $_GET['search_unit'] ?? '';
$search_location = $_GET['search_location'] ?? '';
$search_status = $_GET['search_status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = max(5, min(100, (int)($_GET['per_page'] ?? 10)));
$offset = ($page - 1) * $per_page;

// Build search conditions
$search_conditions = [];
if (!empty($search_item)) {
    $search_conditions[] = "i.item_name LIKE '%" . $conn->real_escape_string($search_item) . "%'";
}
if (!empty($search_unit)) {
    $search_conditions[] = "i.unit LIKE '%" . $conn->real_escape_string($search_unit) . "%'";
}
if (!empty($search_location)) {
    $search_conditions[] = "i.location LIKE '%" . $conn->real_escape_string($search_location) . "%'";
}
if (!empty($search_status)) {
    if ($search_status === 'out') {
        $search_conditions[] = "i.current_stock = 0";
    } elseif ($search_status === 'critical') {
        $search_conditions[] = "i.current_stock > 0 AND i.current_stock <= i.reorder_level";
    } elseif ($search_status === 'low') {
        $search_conditions[] = "i.current_stock > i.reorder_level AND i.current_stock <= (i.reorder_level * 1.5)";
    } elseif ($search_status === 'normal') {
        $search_conditions[] = "i.current_stock > (i.reorder_level * 1.5)";
    }
}

// Combine search conditions with existing where clause
$combined_where = $inv_where;
if (!empty($search_conditions)) {
    $search_where = " (" . implode(" AND ", $search_conditions) . ")";
    if (!empty($inv_where)) {
        $combined_where = $inv_where . " AND" . $search_where;
    } else {
        $combined_where = " WHERE" . $search_where;
    }
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total 
             FROM inventory i 
             LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
             $combined_where";
$count_result = $conn->query($count_sql);
$total_records = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_records / $per_page);

// Get inventory data with pagination
$sql = "SELECT i.*, s.supplier_name 
        FROM inventory i 
        LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
        $combined_where
        ORDER BY i.date_created DESC
        LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);

$sql1 = "SELECT st.*, s.supplier_name
        FROM supplier_transaction st
        JOIN supplier s ON s.supplier_id = st.supplier_id
        WHERE st.status IN ('Pending')
        ORDER BY st.date_received DESC";
$result1 = $conn->query($sql1);

// Get stock movement logs
$stock_logs_sql = "SELECT sl.*, i.item_name, s.supplier_name 
                   FROM stock_logs sl 
                   LEFT JOIN inventory i ON sl.inventory_id = i.inventory_id 
                   LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
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

// Calculate statistics
$total_items = $result ? $result->num_rows : 0;
$low_stock_count = 0;
$out_of_stock_count = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['current_stock'] <= $row['reorder_level']) {
            $low_stock_count++;
        }
        if ($row['current_stock'] == 0) {
            $out_of_stock_count++;
        }
    }
    $result->data_seek(0); // Reset pointer
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
?>

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

    /* Search and Pagination Styles */
    .search-controls {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .search-controls .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
    }

    .search-controls .form-control,
    .search-controls .form-select {
        border: 1px solid #ced4da;
        border-radius: 6px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .search-controls .form-control:focus,
    .search-controls .form-select:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.2rem rgba(7, 59, 29, 0.25);
    }

    .pagination {
        margin-bottom: 0;
    }

    .pagination .page-link {
        color: var(--primary-green);
        border-color: #dee2e6;
        padding: 0.5rem 0.75rem;
    }

    .pagination .page-link:hover {
        color: var(--text-white);
        background-color: var(--primary-green);
        border-color: var(--primary-green);
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary-green);
        border-color: var(--primary-green);
        color: var(--text-white);
    }

    .pagination-info {
        font-size: 0.9rem;
        color: #6c757d;
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

        /* Mobile responsive for search controls */
        .search-controls .row {
            margin: 0;
        }

        .search-controls .col-md-3,
        .search-controls .col-md-2,
        .search-controls .col-md-1 {
            margin-bottom: 1rem;
        }

        .pagination {
            justify-content: center;
        }

        .pagination-info {
            text-align: center;
            margin-bottom: 1rem;
        }
    }
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="">ASSET</h4>
        <h4>MANAGEMENT</h4>
        <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-item">
            <li><a href="<?= $dashboard_link ?>" class="nav-link">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a></li>
            <li><a href="suppliers.php" class="nav-link">
                    <i class="fas fa-users"></i> Supplier List
                </a></li>
            <li><a href="supply_request.php" class="nav-link">
                    <i class="fas fa-clipboard-list"></i> Supply Request
                </a></li>
            <li><a href="issuance.php" class="nav-link">
                    <i class="fas fa-hand-holding"></i> Issuance
                </a></li>
            <li><a href="Inventory.php" class="nav-link active">
                    <i class="fas fa-boxes"></i> Inventory
                </a></li>
            <li><a href="notifications.php" class="nav-link">
                    <i class="fas fa-bell"></i> Notifications
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
        <h1>Inventory Management</h1>
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
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                    <!-- Preserve existing parameters -->
                    <?php if (!empty($sy_inv_raw)): ?>
                        <input type="hidden" name="sy_inv" value="<?= htmlspecialchars($sy_inv_raw) ?>">
                    <?php endif; ?>
                    <?php if (!empty($search_unit)): ?>
                        <input type="hidden" name="search_unit" value="<?= htmlspecialchars($search_unit) ?>">
                    <?php endif; ?>
                    <?php if (!empty($search_location)): ?>
                        <input type="hidden" name="search_location" value="<?= htmlspecialchars($search_location) ?>">
                    <?php endif; ?>
                    <?php if (!empty($search_status)): ?>
                        <input type="hidden" name="search_status" value="<?= htmlspecialchars($search_status) ?>">
                    <?php endif; ?>
                    <?php if (!empty($per_page)): ?>
                        <input type="hidden" name="per_page" value="<?= htmlspecialchars($per_page) ?>">
                    <?php endif; ?>

                    <div class="input-group input-group-sm" style="min-width: 260px;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search_item" class="form-control" placeholder="Search by item name..." value="<?= htmlspecialchars($search_item) ?>">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="Inventory.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
                <button class="btn btn-add btn-sm" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
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
                    <form id="approveForm" action="../actions/mark_supplier_transaction_received.php" method="POST">
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


        <!-- Inventory Table -->
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="inventoryTable">
                <thead class="table-dark">
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Current Stock</th>
                        <th>Unit</th>
                        <th>Location</th>
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
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td class="text-center">
                                    <strong><?= $row['current_stock'] ?></strong>
                                </td>
                                <td><?= $row['unit'] ?></td>
                                <td><?= htmlspecialchars($row['location'] ?? 'N/A') ?></td>
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
                                    <button class="btn btn-sm btn-info" title="Edit" onclick='openEditInventoryModal(<?= (int)$row['inventory_id'] ?>, <?= json_encode($row['item_name']) ?>, <?= json_encode($row['category']) ?>, <?= json_encode($row['unit']) ?>, <?= (int)$row['current_stock'] ?>, <?= (int)$row['reorder_level'] ?>, <?= json_encode($row['location'] ?? '') ?>, <?= json_encode((int)$row['supplier_id']) ?>, <?= json_encode((float)$row['unit_cost']) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                <p class="text-muted">
                                    <?php if (!empty($search_item) || !empty($search_unit) || !empty($search_location) || !empty($search_status)): ?>
                                        No inventory items found matching your search criteria.
                                    <?php else: ?>
                                        No inventory items found.
                                    <?php endif; ?>
                                </p>
                                <?php if (empty($search_item) && empty($search_unit) && empty($search_location) && empty($search_status)): ?>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                        Add First Item
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-between align-items-center p-3 bg-light">
                <div class="text-muted">
                    Showing <?= ($offset + 1) ?> to <?= min($offset + $per_page, $total_records) ?> of <?= $total_records ?> entries
                </div>

                <nav aria-label="Inventory pagination">
                    <ul class="pagination mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>


    </div>

    <!-- Recieved Items Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>Received Items</h3>
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
                        <a href="inventory.php?<?= http_build_query(array_diff_key($_GET, ['sy_recv' => true])) ?>" class="btn btn-outline-light">Reset</a>
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
                            // Sales type handling - can be expanded based on business logic
                            $sales_type_display = $row['sales_type'];
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
                                        data-status="<?= htmlspecialchars($row['status']) ?>">
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
                                <p class="text-muted">No received items found</p>
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
                            <a href="inventory.php?<?= http_build_query(array_diff_key($_GET, ['sy_logs' => true])) ?>" class="btn btn-outline-light">Reset</a>
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
                                <td><?= htmlspecialchars($log['notes']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
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
            <form action="../actions/add_inventory.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Office Supplies">Office Supplies</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Furniture">Furniture</option>
                                <option value="Tools">Tools</option>
                                <option value="Medical">Medical</option>
                                <option value="Food">Food</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" name="current_stock" class="form-control" required min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit</label>
                            <select name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <option value="pc">Piece</option>
                                <option value="box">Box</option>
                                <option value="kg">Kilogram</option>
                                <option value="liter">Liter</option>
                                <option value="set">Set</option>
                                <option value="pack">Pack</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" name="reorder_level" class="form-control" required min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
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
                            <label class="form-label">Unit Cost</label>
                            <input type="number" name="unit_cost" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g., Storage Room A">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
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
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
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
<form id="addInventoryHiddenForm" action="../actions/add_inventory.php" method="POST" style="display:none;">
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
    <input type="text" name="status" id="ai_status">
</form>

<!-- Edit Inventory Modal -->
<div class="modal fade" id="editInventoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInventoryForm" action="../actions/edit_inventory.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="inventory_id" id="ei_inventory_id">
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" name="item_name" id="ei_item_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" name="category" id="ei_category" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Current Stock</label>
                            <input type="number" class="form-control" name="current_stock" id="ei_current_stock" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" class="form-control" name="reorder_level" id="ei_reorder_level" min="0" required>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <select name="unit" id="ei_unit" class="form-select" required>
                                <option value="">--Select Unit--</option>
                                <option value="unit">Unit</option>
                                <option value="pack">Pack</option>
                                <option value="box">Box</option>
                                <option value="set">Set</option>
                                <option value="ream">Ream</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" step="0.01" class="form-control" name="unit_cost" id="ei_unit_cost" required>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Session Message Modal -->
<div class="modal fade" id="sessionMessageModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="sessionModalHeader">
                <h5 class="modal-title" id="sessionModalTitle">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="sessionModalIcon" class="mb-3">
                    <!-- Icon will be set by JavaScript -->
                </div>
                <p id="sessionModalMessage" class="mb-0">
                    <!-- Message will be set by JavaScript -->
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
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
        // Show session message modal if there are messages
        if (sessionMessage || sessionError) {
            showSessionMessageModal();
        }

        // Initialize search functionality
        initializeSearchFilters();

        // Auto-submit form when per_page changes
        $('#per_page').on('change', function() {
            $(this).closest('form').submit();
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

            // Populate hidden fields
            $('#approve-id').val(transactionId);
            $('#approve-item-name').val(itemName);
            $('#approve-quantity').val(quantity);
            $('#approve-unit').val(unit);
            $('#approve-supplier').val(supplier);
            $('#approve-price').val(unitPrice);
            $('#approve-notes').val(notes);

            // Display item name in modal
            $('#display-item-name').text(itemName);

            console.log('Mark as Received modal populated with:', {
                id: transactionId,
                itemName: itemName,
                quantity: quantity,
                unit: unit,
                supplier: supplier,
                unitPrice: unitPrice,
                notes: notes
            });
        });
    });

    // Function to show session message modal
    function showSessionMessageModal() {
        var modal = new bootstrap.Modal(document.getElementById('sessionMessageModal'));
        var header = document.getElementById('sessionModalHeader');
        var title = document.getElementById('sessionModalTitle');
        var icon = document.getElementById('sessionModalIcon');
        var message = document.getElementById('sessionModalMessage');

        if (sessionMessage) {
            // Success message
            header.className = 'modal-header bg-success text-white';
            title.textContent = 'Success';
            icon.innerHTML = '<i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>';
            message.textContent = sessionMessage;
        } else if (sessionError) {
            // Error message
            header.className = 'modal-header bg-danger text-white';
            title.textContent = 'Error';
            icon.innerHTML = '<i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>';
            message.textContent = sessionError;
        }

        modal.show();
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
            url: '../actions/get_inventory_item.php',
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

    // Add to Inventory from Received Items row
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

        // Debug: Log all retrieved values
        console.log('Retrieved data from row:', {
            procurementId: procurementId,
            itemName: itemName,
            category: category,
            unit: unit,
            quantity: quantity,
            supplierId: supplierId,
            unitPrice: unitPrice,
            status: status
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

        // Debug: Log the final values being submitted
        console.log('Final values being submitted:', {
            item_name: itemName,
            category: finalCategory,
            current_stock: finalQuantity,
            unit: finalUnit,
            supplier_id: supplierId,
            unit_cost: unitPrice,
            reorder_level: Math.max(1, Math.floor(finalQuantity * 0.2)),
            status: status
        });

        // Submit
        document.getElementById('addInventoryHiddenForm').submit();
    }

    // Initialize search filters functionality
    function initializeSearchFilters() {
        // Server-side search is now handled by the form submission
        // No client-side filtering needed since search works across all items

        // Highlight search terms in results
        highlightSearchTerms();
    }

    // Highlight search terms in table results
    function highlightSearchTerms() {
        const searchItem = '<?= addslashes($search_item) ?>';
        const searchUnit = '<?= addslashes($search_unit) ?>';
        const searchLocation = '<?= addslashes($search_location) ?>';

        if (searchItem) {
            highlightText('tbody td:nth-child(1)', searchItem);
        }
        if (searchUnit) {
            highlightText('tbody td:nth-child(4)', searchUnit);
        }
        if (searchLocation) {
            highlightText('tbody td:nth-child(5)', searchLocation);
        }
    }

    // Helper function to highlight text
    function highlightText(selector, searchTerm) {
        if (!searchTerm) return;

        $(selector).each(function() {
            const text = $(this).text();
            const regex = new RegExp('(' + escapeRegExp(searchTerm) + ')', 'gi');
            const highlightedText = text.replace(regex, '<mark class="bg-warning">$1</mark>');
            if (highlightedText !== text) {
                $(this).html(highlightedText);
            }
        });
    }

    // Escape special regex characters
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Clear all search filters
    function clearAllFilters() {
        window.location.href = 'Inventory.php';
    }

    // Quick filter functions
    function filterByStatus(status) {
        const url = new URL(window.location);
        url.searchParams.set('search_status', status);
        url.searchParams.delete('page'); // Reset to first page
        window.location.href = url.toString();
    }

    // Open Edit Inventory modal with data
    function openEditInventoryModal(id, name, category, unit, stock, reorder, location, supplierId, unitCost) {
        document.getElementById('ei_inventory_id').value = id;
        document.getElementById('ei_item_name').value = name;
        document.getElementById('ei_category').value = category;
        document.getElementById('ei_unit').value = unit;
        document.getElementById('ei_current_stock').value = stock;
        document.getElementById('ei_reorder_level').value = reorder;
        document.getElementById('ei_location').value = location || '';
        document.getElementById('ei_supplier_id').value = supplierId || '';
        document.getElementById('ei_unit_cost').value = unitCost || 0;
        const modal = new bootstrap.Modal(document.getElementById('editInventoryModal'));
        modal.show();
    }
</script>

<?php include '../includes/footer.php'; ?>