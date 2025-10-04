<?php
$pageTitle = 'Procurement Management';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';


// Get supply requests with status information
// Apply request type filter based on user role
$role = strtolower($user_type);
$whereClause = '';
if ($role === 'supply in-charge') {
    $whereClause = "WHERE LOWER(sr.request_type) = 'consumables'";
} elseif ($role === 'property custodian') {
    $whereClause = "WHERE LOWER(sr.request_type) = 'property'";
}

// Add condition to only show rows with approved_by and approved_date not null or empty
if (!empty($whereClause)) {
    $whereClause .= " AND sr.approved_by IS NOT NULL 
                      AND sr.approved_by <> '' 
                      AND sr.approved_date IS NOT NULL 
                      AND sr.approved_date <> ''";
} else {
    $whereClause = "WHERE sr.approved_by IS NOT NULL 
                    AND sr.approved_by <> '' 
                    AND sr.approved_date IS NOT NULL 
                    AND sr.approved_date <> ''";
}

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

// Generate school year options (current year and previous 5 years)
$current_year = date('Y');
$sy_years = [];
for ($i = 0; $i < 6; $i++) {
    $year = $current_year - $i;
    $sy_years[] = $year . '-' . ($year + 1);
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
        WHERE at.id BETWEEN 14 AND 29
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

// Get approved supply requests
/*$approved_requests_sql = "SELECT 
    sr.id AS request_id,
    sr.item_name,
    sr.quantity,
    sr.date_requested,
    u.first_name,
    u.last_name
FROM supply_request sr
JOIN user u ON sr.user_id = u.id;
";
$approved_requests_result = $conn->query($approved_requests_sql);*/

// Get purchased data
$sql = "SELECT i.*, s.supplier_name 
        FROM supplier_transaction i 
        LEFT JOIN supplier s ON i.supplier_id = s.supplier_id
        $inv_where
        ORDER BY i.date_created DESC";
$result = $conn->query($sql);


// Get procurement data

// Get suppliers for dropdown
$suppliers_sql = "SELECT supplier_id, supplier_name FROM supplier WHERE status = 'Active' ORDER BY supplier_name";
$suppliers_result = $conn->query($suppliers_sql);

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
        --accent-orange: #EACA26;
        ;
        --accent-blue: #4a90e2;
        --accent-green-approved: #28a745;
        --accent-red: #e74c3c;
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
        max-width: 100%;
    }

    .stat-card {
        background-color: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
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

    .stat-icon.users {
        background-color: var(--primary-green);
    }

    .stat-icon.suppliers {
        background-color: var(--accent-orange);
    }

    .stat-card.pending .stat-icon {
        background-color: var(--accent-red);
    }

    .stat-card.received .stat-icon {
        background-color: var(--accent-blue);
    }

    .stat-card.approved .stat-icon {
        background-color: var(--accent-green-approved);
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

    /* Table Styles */
    .table-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
        width: 100%;
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

    /* Optimized Table Layout */
    .table {
        margin-bottom: 0;
        font-size: 0.85rem;
        width: 100%;
        table-layout: fixed;
    }

    .table thead th {
        padding: 12px 8px;
        font-weight: 600;
        font-size: 0.8rem;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .table tbody td {
        padding: 12px 8px;
        vertical-align: middle;
        line-height: 1.3;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    /* Optimized Column Widths for Monitor Fit */
    .table td:nth-child(1), /* Date */
    .table th:nth-child(1) {
        width: 8%;
        min-width: 90px;
    }

    .table td:nth-child(2), /* Invoice */
    .table th:nth-child(2) {
        width: 8%;
        min-width: 80px;
    }

    .table td:nth-child(3), /* Supplier */
    .table th:nth-child(3) {
        width: 12%;
        min-width: 100px;
    }

    .table td:nth-child(4), /* Sales Type */
    .table th:nth-child(4) {
        width: 7%;
        min-width: 70px;
    }

    .table td:nth-child(5), /* Category */
    .table th:nth-child(5) {
        width: 15%;
        min-width: 120px;
        white-space: normal;
        word-wrap: break-word;
    }

    .table td:nth-child(6), /* Item Name */
    .table th:nth-child(6) {
        width: 18%;
        min-width: 140px;
        white-space: normal;
        word-wrap: break-word;
    }

    .table td:nth-child(7), /* Quantity */
    .table th:nth-child(7) {
        width: 8%;
        min-width: 70px;
        text-align: center;
    }

    .table td:nth-child(8), /* Unit Price */
    .table th:nth-child(8) {
        width: 8%;
        min-width: 80px;
        text-align: right;
    }

    .table td:nth-child(9), /* Amount */
    .table th:nth-child(9) {
        width: 9%;
        min-width: 90px;
        text-align: right;
    }

    .table td:nth-child(10), /* Status */
    .table th:nth-child(10) {
        width: 7%;
        min-width: 70px;
        text-align: center;
    }

    .table td:nth-child(11), /* Action */
    .table th:nth-child(11) {
        width: 10%;
        min-width: 120px;
        text-align: center;
    }

    /* Badge styling - keep default colors */
    .badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
        border-radius: 0.375rem;
    }

    /* Action buttons - keep default styling but optimize spacing */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        margin: 0 1px;
        font-size: 0.75rem;
    }

    /* Responsive adjustments */
    @media (max-width: 1400px) {
        .table {
            font-size: 0.8rem;
        }
        
        .table thead th,
        .table tbody td {
            padding: 10px 6px;
        }
    }

    @media (max-width: 1200px) {
        .table {
            font-size: 0.75rem;
        }
        
        .table thead th,
        .table tbody td {
            padding: 8px 4px;
        }
        
        .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px;
        max-width: 100%;
    }

    .stat-card {
        background-color: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
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

    .stat-icon.users {
        background-color: var(--primary-green);
    }

    .stat-icon.suppliers {
        background-color: var(--accent-orange);
    }

    .stat-card.pending .stat-icon {
        background-color: var(--accent-red);
    }

    .stat-card.received .stat-icon {
        background-color: var(--accent-blue);
    }

    .stat-card.approved .stat-icon {
        background-color: var(--accent-green-approved);
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

    /* Responsive Design */
    @media (max-width: 1200px) {
        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }

        .table-responsive {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 992px) {
        .sidebar {
            width: 250px;
        }

        .main-content {
            margin-left: 250px;
        }

        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }

        .stat-card {
            padding: 12px;
            min-height: 100px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.3rem;
        }

        .stat-number {
            font-size: 1.6rem;
        }

        .content-header {
            padding: 25px;
        }

        .content-header h1 {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            width: 280px;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            padding: 15px;
        }

        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            padding: 15px;
        }

        .stat-card {
            padding: 10px;
            min-height: 90px;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 1.4rem;
        }

        .stat-label {
            font-size: 0.8rem;
        }

        .content-header {
            padding: 20px;
            margin-bottom: 20px;
        }

        .content-header h1 {
            font-size: 1.6rem;
        }

        .content-header p {
            font-size: 0.9rem;
        }

        .table-header {
            padding: 15px;
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .table-header h3 {
            font-size: 1.2rem;
        }

        .btn-add {
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .modal-dialog {
            margin: 0.5rem;
        }

        .modal-body {
            padding: 1rem;
        }

        .table-responsive {
            font-size: 0.8rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .main-content {
            padding: 10px;
        }

        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
            padding: 10px;
        }

        .stat-card {
            padding: 8px;
            min-height: 80px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 1.2rem;
        }

        .stat-label {
            font-size: 0.75rem;
        }

        .content-header {
            padding: 15px;
            margin-bottom: 15px;
        }

        .content-header h1 {
            font-size: 1.4rem;
        }

        .content-header p {
            font-size: 0.8rem;
        }

        .table-header {
            padding: 10px;
        }

        .table-header h3 {
            font-size: 1.1rem;
        }

        .btn-add {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .table-responsive {
            font-size: 0.75rem;
        }

        .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
            margin: 0 1px;
        }

        .modal-dialog {
            margin: 0.25rem;
        }

        .modal-body {
            padding: 0.75rem;
        }
    }

    /* Large screens */
    @media (min-width: 1400px) {
        .main-content {
            padding: 30px;
        }

        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            padding: 25px;
        }

        .stat-card {
            padding: 20px;
            min-height: 140px;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            font-size: 1.8rem;
        }

        .stat-number {
            font-size: 2.5rem;
        }

        .stat-label {
            font-size: 1rem;
        }

        .content-header {
            padding: 40px;
        }

        .content-header h1 {
            font-size: 2.8rem;
        }

        .content-header p {
            font-size: 1.2rem;
        }
    }

    /* Ultra-wide screens */
    @media (min-width: 1920px) {
        .main-content {
            padding: 40px;
        }

        .stats-container {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 30px;
        }

        .stat-card {
            padding: 25px;
            min-height: 160px;
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }

        .stat-number {
            font-size: 3rem;
        }

        .stat-label {
            font-size: 1.1rem;
        }

        .content-header {
            padding: 50px;
        }

        .content-header h1 {
            font-size: 3.2rem;
        }

        .content-header p {
            font-size: 1.4rem;
        }
    }

    .save_purchase:hover {
        background-color: var(--primary-green);
        transform: translateY(-3px);
    }
    .cancel_purchase:hover {
        background-color: var(--primary-green);
        transform: translateY(-3px);
    }
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="">DARTS</h4>
        <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-item">
            <li><a href="../dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a></li>
                <li><a href="suppliers.php" class="nav-link">
                    <i class="fas fa-users"></i> Supplier List
                </a></li>
                <li><a href="procurement.php" class="nav-link active">
                        <i class="fas fa-shopping-cart"></i> Procurement
                    </a></li>
            <li><a href="canvas_form.php" class="nav-link">
                    <i class="fas fa-clipboard-list"></i> Canvass Form
                </a></li>
            <li><a href="canvass_form_list.php" class="nav-link">
                    <i class="fas fa-list"></i> Canvass List
                </a></li>
            <li><a href="purchase_order.php" class="nav-link">
                    <i class="fas fa-shopping-basket"></i> Purchase Order
                </a></li>
            <li><a href="purchase_order_list.php" class="nav-link">
                    <i class="fas fa-file-invoice"></i> Purchase Order List
                </a></li>
            <!--<li><a href="transaction_list.php" class="nav-link">
                    <i class="fas fa-exchange-alt"></i> Transactions
                </a></li>-->
            <li><a href="../logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a></li>
        </ul>
    </nav>
</div>

<!-- Mobile Menu Toggle -->
<button class="btn btn-primary d-md-none mobile-menu-toggle" style="position: fixed; top: 10px; left: 10px; z-index: 1001; display: none;">
    <i class="fas fa-bars"></i>
</button>

<!-- Main Content -->
<div class="main-content">
    <div class="content-header">
        <h1>Procurement Management</h1>
        <p>Manage purchase details, invoices, and delivery status</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon users">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number"><?= $suppliers_result->num_rows ?></div>
            <div class="stat-label">Active Suppliers</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon suppliers">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-number"><?= $result->num_rows ?></div>
            <div class="stat-label">Total Purchases</div>
        </div>

        <!--<div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Pending Receipts</div>
        </div>-->

        <div class="stat-card received">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-number">
                <?php
                $total_count_sql = "SELECT COUNT(*) as count FROM supply_request";
                $total_count_result = $conn->query($total_count_sql);
                $total_count = $total_count_result->fetch_assoc()['count'];
                echo $total_count;
                ?>
            </div>
            <div class="stat-label">Total Requests</div>
        </div>


        <div class="stat-card approved">
            <div class="stat-icon">
                <i class="fas fa-thumbs-up"></i>
            </div>
            <div class="stat-number">
                <?php
                $approved_count_sql = "SELECT COUNT(*) as count FROM supply_request WHERE approved_by IS NOT NULL";
                $approved_count_result = $conn->query($approved_count_sql);
                $approved_count = $approved_count_result->fetch_assoc()['count'];
                echo $approved_count;
                ?>
            </div>
            <div class="stat-label">Approved Request<?php echo $approved_count != 1 ? 's' : ''; ?></div>
        </div>
    </div>

    <!-- Purchase Items Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>Purchase Items List</h3>
            <div class="d-flex align-items-end gap-2">
                <form method="GET" class="d-flex align-items-end gap-2 mb-0">
                    <div>
                        <label for="sy_inv" class="form-label mb-0 text-white">School Year</label>
                        <select id="sy_inv" name="sy_inv" class="form-select" onchange="this.form.submit()">
                            <option value="">All</option>
                            <?php foreach ($sy_years as $sy): ?>
                                <option value="<?= htmlspecialchars($sy) ?>" <?= ($sy_inv_raw === $sy) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sy) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pt-4">
                        <?php if (!empty($sy_inv_raw)): ?>
                            <a href="procurement.php?<?= http_build_query(array_diff_key($_GET, ['sy_inv' => true])) ?>" class="btn btn-outline-light">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProcurementModal">
                    <i class="fas fa-plus"></i> Procurement Items
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th title="Date Purchased">Date Purchased</th>
                        <th title="Invoice Number">Invoice Number</th>
                        <th>Supplier</th>
                        <th>Sales Type</th>
                        <th>Category</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($row['date_created'])) ?></td>
                                <td><strong><?= htmlspecialchars($row['invoice_no']) ?></strong></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td><?= htmlspecialchars($row['sales_type']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                                <td class="text-center"><?= htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['unit']) ?></td>
                                <td class="text-end">₱<?= number_format($row['unit_price'], 2) ?></td>
                                <td class="text-end"><strong>₱<?= number_format($row['total_amount'], 2) ?></strong></td>
                                <td class="text-center">
                                    <?php
                                    $status = $row['status'];
                                    $badgeClass = '';
                                    switch(strtolower($status)) {
                                        case 'pending':
                                            $badgeClass = 'bg-warning';
                                            break;
                                        case 'received':
                                            $badgeClass = 'bg-success';
                                            break;
                                        default:
                                            $badgeClass = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info edit-procurement-btn" title="Edit"
                                            data-procurement-id="<?= $row['procurement_id'] ?>"
                                            data-item-name="<?= htmlspecialchars($row['item_name']) ?>"
                                            data-invoice-no="<?= htmlspecialchars($row['invoice_no']) ?>"
                                            data-quantity="<?= $row['quantity'] ?>"
                                            data-unit="<?= htmlspecialchars($row['unit']) ?>"
                                            data-unit-price="<?= $row['unit_price'] ?>"
                                            data-sales-type="<?= htmlspecialchars($row['sales_type']) ?>"
                                            data-category="<?= htmlspecialchars($row['category']) ?>"
                                            data-brand-model="<?= htmlspecialchars($row['brand_model']) ?>"
                                            data-color="<?= htmlspecialchars($row['color']) ?>"
                                            data-type="<?= htmlspecialchars($row['type']) ?>"
                                            data-receiver="<?= htmlspecialchars($row['receiver']) ?>"
                                            data-supplier-id="<?= $row['supplier_id'] ?>"
                                            data-notes="<?= htmlspecialchars($row['notes'] ?? '') ?>"
                                            data-date-purchase="<?= date('Y-m-d', strtotime($row['date_created'])) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-procurement-btn" title="Delete"
                                            data-procurement-id="<?= $row['procurement_id'] ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No inventory items found</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProcurementModal">
                                    Add First Item
                                </button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Approved Request List -->
    <div class="table-container mt-4">
        <div class="table-header">
            <h3>Approved Request List</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Request ID</th>
                        <th>Date Requested</th>
                        <th>Date Needed</th>
                        <th>Department</th>
                        <th>Name</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $request_sql = "SELECT sr.*, 
                    sr.noted_by, sr.checked_by, sr.verified_by, sr.issued_by, sr.approved_by,
                    sr.noted_date, sr.checked_date, sr.verified_date, sr.issued_date, sr.approved_date,
                    CONCAT_WS(' ', u.first_name, u.last_name) AS requester_name
                FROM supply_request sr 
                LEFT JOIN user u ON u.id = sr.user_id
                $whereClause
                ORDER BY sr.date_requested DESC";
                    $request_result = $conn->query($request_sql);

                    if ($request_result && $request_result->num_rows > 0):
                        while ($row = $request_result->fetch_assoc()):
                    ?>
                            <tr>
                                <td><?= htmlspecialchars($row['request_id']) ?></td>
                                <td><?= date('M d, Y', strtotime($row['date_requested'])) ?></td>
                                <td><?= date('M d, Y', strtotime($row['date_needed'])) ?></td>
                                <td><?= htmlspecialchars($row['department_unit']) ?></td>
                                <td><?= htmlspecialchars($row['requester_name']) ?></td>
                                <td><?= htmlspecialchars($row['purpose']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['approved_by'] ? 'success' : 'warning' ?>">
                                        <?= $row['approved_by'] ? 'Approved' : 'Pending' ?></span>
                                    </span>
                                </td>
                                <td>
                                    <a href="../pages/supply_request.php?id=<?= $row['request_id'] ?>"><button class="btn btn-sm btn-primary">View</button></a>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No supply requests found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!--View Procurement Modal-->
<?php include '../modals/view_procurement.php'; ?>

<!-- Edit Modal -->
<?php include '../modals/edit_procurement.php'; ?>

<!-- Approve/Received Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark as Received</h5>
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
                    <p class="text-center">Are you sure you want to mark this item as received?</p>
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

<!-- Add Procurement Modal -->
<div class="modal fade" id="addProcurementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-green); color: white;">
                <h5 class="modal-title">Add New Purchase Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/add_procurement.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Row 1: Item Name and Invoice No -->

                        <div class="col-md-4">
                            <label class="form-label">Date of Purchase <span class="text-danger">*</span></label>
                            <input type="date" name="date_purchase" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Brand and Model <span class="text-danger">*</span></label>
                            <input type="text" name="brand_model" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"> Item Type (optional)</label>
                            <input type="text" name="type" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Color (optional) <span class="text-danger">*</span></label>
                            <input type="text" name="color" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Invoice No <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" class="form-control" required>
                        </div>

                        <!-- Row 2: Supplier and Category -->
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php
                                if (isset($organized_categories) && !empty($organized_categories)) {
                                    foreach ($organized_categories as $main_category => $subcategories) {
                                        echo '<optgroup label="' . htmlspecialchars($main_category) . '">';
                                        foreach ($subcategories as $subcategory) {
                                            echo '<option value="' . htmlspecialchars($subcategory) . '">' . htmlspecialchars($subcategory) . '</option>';
                                        }
                                        echo '</optgroup>';
                                    }
                                } else {
                                    echo '<option value="Office Supplies">Office Supplies</option>';
                                    echo '<option value="Medical Supplies">Medical Supplies</option>';
                                    echo '<option value="Property and Equipment">Property and Equipment</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Row 3: Quantity, Unit, Unit Price -->
                        <div class="col-md-4">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <option value="pcs">Pieces</option>
                                <option value="boxes">Boxes</option>
                                <option value="kgs">Kilograms</option>
                                <option value="liters">Liters</option>
                                <option value="sets">Sets</option>
                                <option value="packs">Packs</option>
                                <option value="reams">Reams</option>
                            </select>
                        </div>

                        <!-- Row 4: Sales Type and Total Amount -->
                        <div class="col-md-4">
                            <label class="form-label">Purchase Type <span class="text-danger">*</span></label>
                            <select name="sales_type" class="form-select" required>
                                <option value="">Select Purchase Type</option>
                                <option value="Credit">Credit</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" required id="quantity">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" name="unit_price" step="0.01" class="form-control" required id="unit_price">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Receiver <span class="text-danger">*</span></label>
                            
                            <select name="receiver" class="form-select" required>
                                <option value="">Select Receiver</option>
                                <option value="Supply In-charge">Supply In-charge</option>
                                <option value="Property Custodian">Property Custodian</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Total Amount <span class="text-danger">*</span></label>
                            <input type="number" name="total_amount" step="0.01" class="form-control" readonly id="total_amount">
                        </div>


                        <!-- Row 5: File Uploads 
                        <div class="col-md-4">
                            <label class="form-label">Invoice</label>
                            <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Delivery Receipt</label>
                            <input type="file" name="delivery_receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>-->

                        <!-- Row 6: Notes -->
                        <div class="col-6">
                            <label class="form-label">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <p class="text-danger text-center" style="font-weight: bold; margin-right: 300px;">All <span class="text-danger">*</span> fields are required</p>
                    <button type="button" class="btn btn-secondary cancel_purchase" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary save_purchase">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Procurement Modal -->
<div class="modal fade" id="editProcurementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--primary-green); color: white;">
                <h5 class="modal-title">Edit Purchase Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/edit_procurement.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="procurement_id" value="<?= $procurement_id ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Row 1: Item Name and Invoice No -->

                        <div class="col-md-4">
                            <label class="form-label">Date of Purchase <span class="text-danger">*</span></label>
                            <input type="date" name="date_purchase" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Brand and Model <span class="text-danger">*</span></label>
                            <input type="text" name="brand_model" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"> Item Type (optional)</label>
                            <input type="text" name="type" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Color (optional) <span class="text-danger">*</span></label>
                            <input type="text" name="color" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Invoice No <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" class="form-control" required>
                        </div>

                        <!-- Row 2: Supplier and Category -->
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php
                                if (isset($organized_categories) && !empty($organized_categories)) {
                                    foreach ($organized_categories as $main_category => $subcategories) {
                                        echo '<optgroup label="' . htmlspecialchars($main_category) . '">';
                                        foreach ($subcategories as $subcategory) {
                                            echo '<option value="' . htmlspecialchars($subcategory) . '">' . htmlspecialchars($subcategory) . '</option>';
                                        }
                                        echo '</optgroup>';
                                    }
                                } else {
                                    echo '<option value="Office Supplies">Office Supplies</option>';
                                    echo '<option value="Medical Supplies">Medical Supplies</option>';
                                    echo '<option value="Property and Equipment">Property and Equipment</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Row 3: Quantity, Unit, Unit Price -->
                        <div class="col-md-4">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <option value="pcs">Pieces</option>
                                <option value="boxes">Boxes</option>
                                <option value="kgs">Kilograms</option>
                                <option value="liters">Liters</option>
                                <option value="sets">Sets</option>
                                <option value="packs">Packs</option>
                                <option value="reams">Reams</option>
                            </select>
                        </div>

                        <!-- Row 4: Sales Type and Total Amount -->
                        <div class="col-md-4">
                            <label class="form-label">Purchase Type <span class="text-danger">*</span></label>
                            <select name="sales_type" class="form-select" required>
                                <option value="">Select Purchase Type</option>
                                <option value="Credit">Credit</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" required id="quantity">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" name="unit_price" step="0.01" class="form-control" required id="unit_price">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Receiver <span class="text-danger">*</span></label>
                            <select name="receiver" class="form-select" required>
                                <option value="">Select Receiver</option>
                                <option value="Supply In-charge">Supply In-charge</option>
                                <option value="Property Custodian">Property Custodian</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Total Amount <span class="text-danger">*</span></label>
                            <input type="number" name="total_amount" step="0.01" class="form-control" readonly id="total_amount">
                        </div>


                        <!-- Row 5: File Uploads 
                        <div class="col-md-4">
                            <label class="form-label">Invoice</label>
                            <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Delivery Receipt</label>
                            <input type="file" name="delivery_receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>-->

                        <!-- Row 6: Notes -->
                        <div class="col-6">
                            <label class="form-label">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <p class="text-danger text-center" style="font-weight: bold; margin-right: 300px;">All <span class="text-danger">*</span> fields are required</p>
                    <button type="button" class="btn btn-secondary cancel_purchase" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary save_purchase">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Mobile sidebar toggle
        $('.mobile-menu-toggle').on('click', function() {
            $('.sidebar').toggleClass('show');
            $('.mobile-menu-toggle').toggle();
        });

        // Close sidebar when clicking outside on mobile
        $(document).on('click', function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('.sidebar, .mobile-menu-toggle').length) {
                    $('.sidebar').removeClass('show');
                    $('.mobile-menu-toggle').show();
                }
            }
        });

        // Show/hide mobile menu toggle based on screen size
        function toggleMobileMenu() {
            if ($(window).width() <= 768) {
                $('.mobile-menu-toggle').show();
            } else {
                $('.mobile-menu-toggle').hide();
                $('.sidebar').removeClass('show');
            }
        }

        // Initial check
        toggleMobileMenu();

        // Check on window resize
        $(window).on('resize', function() {
            toggleMobileMenu();
        });

        // Auto-calculate total
        $('input[name="quantity"], input[name="unit_price"]').on('input', function() {
            const quantity = parseFloat($('input[name="quantity"]').val()) || 0;
            const unitPrice = parseFloat($('input[name="unit_price"]').val()) || 0;
            const total = quantity * unitPrice;
            // You can add a total display field if needed
        });

        // Auto-calculate total amount
        $('#quantity, #unit_price').on('input', function() {
            const quantity = parseFloat($('#quantity').val()) || 0;
            const unitPrice = parseFloat($('#unit_price').val()) || 0;
            const total = quantity * unitPrice;
            $('#total_amount').val(total.toFixed(2));
        });

        // Auto-calculate total amount for edit modal
        $('#edit_quantity, #edit_unit_price').on('input', function() {
            const quantity = parseFloat($('#edit_quantity').val()) || 0;
            const unitPrice = parseFloat($('#edit_unit_price').val()) || 0;
            const total = quantity * unitPrice;
            $('#edit_total_amount').val(total.toFixed(2));
        });

        // View Modal functionality
        $('.view-btn').on('click', function() {
            console.log('View button clicked!');
            const button = $(this);

            // Store the data in a global variable for the modal shown event
            window.currentViewData = {
                date: button.data('date'),
                item: button.data('item'),
                quantity: button.data('quantity'),
                unit: button.data('unit'),
                supplier: button.data('supplier'),
                price: button.data('price'),
                total: button.data('total'),
                status: button.data('status'),
                notes: button.data('notes')
            };

            // Debug: Log the data attributes
            console.log('View button clicked with data:', window.currentViewData);
        });

        // Modal shown event for debugging and populating data
        $('#viewModal').on('shown.bs.modal', function() {
            console.log('View modal is now visible');
            console.log('Modal elements found:', {
                date: $('#view-date').length,
                item: $('#view-item').length,
                quantity: $('#view-quantity').length,
                supplier: $('#view-supplier').length,
                price: $('#view-price').length,
                total: $('#view-total').length,
                status: $('#view-status').length,
                notes: $('#view-notes').length
            });

            // Populate the modal with stored data
            if (window.currentViewData) {
                console.log('Populating modal with data:', window.currentViewData);

                $('#view-date').text(window.currentViewData.date || 'N/A');
                $('#view-item').text(window.currentViewData.item || 'N/A');
                $('#view-quantity').text((window.currentViewData.quantity || '0') + ' ' + (window.currentViewData.unit || ''));
                $('#view-supplier').text(window.currentViewData.supplier || 'N/A');
                $('#view-price').text('₱' + (parseFloat(window.currentViewData.price || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2
                })));
                $('#view-total').text('₱' + (parseFloat(window.currentViewData.total || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2
                })));

                // Status with badge styling
                const status = window.currentViewData.status || 'Pending';
                const statusBadge = status === 'Received' ?
                    '<span class="badge bg-success">' + status + '</span>' :
                    '<span class="badge bg-warning">' + status + '</span>';
                $('#view-status').html(statusBadge);

                $('#view-notes').text(window.currentViewData.notes || 'No notes available');

                // Debug: Log the populated elements
                console.log('Modal elements populated:', {
                    date: $('#view-date').text(),
                    item: $('#view-item').text(),
                    quantity: $('#view-quantity').text(),
                    supplier: $('#view-supplier').text(),
                    price: $('#view-price').text(),
                    total: $('#view-total').text(),
                    status: $('#view-status').html(),
                    notes: $('#view-notes').text()
                });
            } else {
                console.log('No data available for modal population');
            }
        });

        // Edit Modal functionality
        $('.edit-procurement-btn').on('click', function() {
            const button = $(this);
            const modal = $('#editProcurementModal');
            const form = modal.find('form');
            
            // Populate form fields with data attributes
            form.find('input[name="date_purchase"]').val(button.data('date-purchase') || '');
            form.find('input[name="item_name"]').val(button.data('item-name') || '');
            form.find('input[name="invoice_no"]').val(button.data('invoice-no') || '');
            form.find('input[name="quantity"]').val(button.data('quantity') || '');
            form.find('select[name="unit"]').val(button.data('unit') || '');
            form.find('input[name="unit_price"]').val(button.data('unit-price') || '');
            form.find('select[name="sales_type"]').val(button.data('sales-type') || '');
            form.find('select[name="category"]').val(button.data('category') || '');
            form.find('input[name="brand_model"]').val(button.data('brand-model') || '');
            form.find('input[name="color"]').val(button.data('color') || '');
            form.find('input[name="type"]').val(button.data('type') || '');
            form.find('select[name="receiver"]').val(button.data('receiver') || '');
            form.find('textarea[name="notes"]').val(button.data('notes') || '');
            
            // Set the procurement ID in a hidden field if needed
            form.find('input[name="procurement_id"]').val(button.data('procurement-id') || '');
            
            // Calculate and set total amount
            const quantity = parseFloat(button.data('quantity')) || 0;
            const unitPrice = parseFloat(button.data('unit-price')) || 0;
            const totalAmount = (quantity * unitPrice).toFixed(2);
            form.find('input[name="total_amount"]').val(totalAmount);
            
            // Set the supplier in the dropdown
            form.find('select[name="supplier_id"]').val(button.data('supplier-id') || '');

            // Show the modal
            const editModal = new bootstrap.Modal(modal[0]);
            editModal.show();

            console.log('Edit modal populated with:', {
                id: button.data('procurement-id'),
                itemName: button.data('item-name'),
                invoiceNo: button.data('invoice-no'),
                quantity: button.data('quantity'),
                unit: button.data('unit'),
                unitPrice: button.data('unit-price'),
                salesType: button.data('sales-type'),
                category: button.data('category'),
                brandModel: button.data('brand-model'),
                color: button.data('color'),
                type: button.data('type'),
                receiver: button.data('receiver'),
                supplierId: button.data('supplier-id')
            });
        });

        // Approve Modal functionality
        $('.approve-btn').on('click', function() {
            const button = $(this);
            $('#approve-id').val(button.data('procurement-id'));
            $('#approve-item-name').text(button.data('item-name'));
            $('#approve-quantity').text(button.data('quantity'));
            $('#approve-unit').text(button.data('unit'));
            $('#approve-supplier').text(button.data('supplier'));
            $('#approve-price').text(button.data('price'));
            $('#approve-notes').text(button.data('notes'));
        });

        // Procurement item action functions
        function viewProcurementItem(id) {
            // Open a modal or redirect to view procurement item details
            console.log('View procurement item:', id);
            // You can implement a view modal here or redirect to a details page
            alert('View functionality - ID: ' + id);
        }

        function deleteProcurementItem(id) {
            if (confirm('Are you sure you want to delete this procurement item?')) {
                // Submit the delete form
                document.getElementById('deleteForm').action = '../actions/delete_procurement.php';
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Delete procurement item functionality
        $('.delete-procurement-btn').on('click', function() {
            const button = $(this);
            deleteProcurementItem(button.data('procurement-id'));
        });

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

            // Show the modal
            const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
            approveModal.show();

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
                            $('#approveModal').modal('hide');
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
    });
</script>

<script>
    // Show alert messages if there are session messages
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($session_message)): ?>
            alert('<?= addslashes($session_message) ?>');
        <?php endif; ?>

        <?php if (!empty($session_error)): ?>
            alert('<?= addslashes($session_error) ?>');
        <?php endif; ?>
    });
</script>

<!-- Hidden Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" id="deleteId" name="id" value="">
</form>

<?php include '../includes/footer.php'; ?>