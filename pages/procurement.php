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
        at.name as main_category,
        sc.name as subcategory,
        ssc.name as sub_subcategory,
        sssc.name as sub_sub_subcategory
    FROM account_types at
    LEFT JOIN account_subcategories sc ON at.id = sc.parent_id
    LEFT JOIN account_sub_subcategories ssc ON sc.id = ssc.subcategory_id
    LEFT JOIN account_sub_sub_subcategories sssc ON ssc.id = sssc.sub_subcategory_id
    ORDER BY at.name, sc.name, ssc.name, sssc.name
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
        $recv_where
        ORDER BY i.date_created DESC";
$result = $conn->query($sql);


// Get procurement data

// Get suppliers for dropdown
$suppliers_sql = "SELECT supplier_id, supplier_name FROM supplier WHERE status = 'Active' ORDER BY supplier_name";
$suppliers_result = $conn->query($suppliers_sql);

// Display session messages
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['error']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error']);
}
?>

<style>
    :root {
        --primary-green: #073b1d;
        --dark-green: #073b1d;
        --light-green: #2d8aad;
        --accent-orange: #fd7e14;
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

        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        /* spacing between cards */
        justify-content: center;
        /* or space-between / space-around */
        padding: 20px;

    }

    .stat-card {


        flex: 1 1 200px;
        /* allows cards to grow/shrink and wrap */
        max-width: 220px;
        min-width: 180px;
        background-color: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;


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

    /* Modal Styles */
    .modal-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        border-bottom: none;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-body {
        padding: 2rem;
    }

    .form-control-plaintext {
        background-color: var(--bg-light);
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        font-weight: 500;
    }

    .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1rem 2rem;
    }

    /* Action buttons in table */
    .btn-sm {
        margin: 0 2px;
        transition: all 0.3s ease;
    }

    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        .modal-dialog {
            margin: 1rem;
        }

        .modal-body {
            padding: 1rem;
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
            <li><a href="../dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a></li>
            <li><a href="suppliers.php" class="nav-link">
                    <i class="fas fa-users"></i> Supplier List
                </a></li>
            <li><a href="procurement.php" class="nav-link active">
                    <i class="fas fa-shopping-cart"></i> Procurement
                </a></li>
            <li><a href="inventory.php" class="nav-link">
                    <i class="fas fa-boxes"></i> Inventory
                </a></li>
            <li><a href="transaction_list.php" class="nav-link">
                    <i class="fas fa-exchange-alt"></i> Transactions
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

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Pending Receipts</div>
        </div>

        <div class="stat-card received">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Received Items</div>
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
            <h3>Purchase Items</h3>
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
                            <a href="inventory.php?<?= http_build_query(array_diff_key($_GET, ['sy_inv' => true])) ?>" class="btn btn-outline-light">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProcurementModal">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date Received</th>
                        <th>Invoice Number</th>
                        <th>Supplier</th>
                        <th>Sales Type</th>
                        <th>Category</th>
                        <th>Item Description</th>
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
                                <td><?= htmlspecialchars($row['invoice_no']) ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td><?= htmlspecialchars($row['sales_type']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td><?= htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['unit']) ?></td>
                                <td><?= htmlspecialchars($row['unit_price']) ?></td>
                                <td><?= htmlspecialchars($row['total_amount']) ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        Purchased
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-procurement-btn" title="Edit"
                                        data-procurement-id="<?= $row['procurement_id'] ?>"
                                        data-item-name="<?= htmlspecialchars($row['item_name']) ?>"
                                        data-invoice-no="<?= htmlspecialchars($row['invoice_no']) ?>"
                                        data-quantity="<?= $row['quantity'] ?>"
                                        data-unit="<?= htmlspecialchars($row['unit']) ?>"
                                        data-unit-price="<?= $row['unit_price'] ?>"
                                        data-sales-type="<?= htmlspecialchars($row['sales_type']) ?>"
                                        data-category="<?= htmlspecialchars($row['category']) ?>"
                                        data-supplier-id="<?= $row['supplier_id'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-procurement-btn" title="Delete"
                                        data-procurement-id="<?= $row['procurement_id'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
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
                                        <?= $row['approved_by'] ? 'Approved' : 'Pending' ?>
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
                            <td colspan="9" class="text-center py-4">
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
            <form id="approveForm" action="../actions/approve_procurement.php" method="POST">
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
            <div class="modal-header">
                <h5 class="modal-title">Add New Purchase Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/add_procurement.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Row 1: Item Name and Invoice No -->
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice No.</label>
                            <input type="text" name="invoice_no" class="form-control" required>
                        </div>

                        <!-- Row 2: Supplier and Category -->
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
                            <label class="form-label">Category</label>
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
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required id="quantity">
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
                            <label class="form-label">Unit Price</label>
                            <input type="number" name="unit_price" step="0.01" class="form-control" required id="unit_price">
                        </div>

                        <!-- Row 4: Sales Type and Total Amount -->
                        <div class="col-md-6">
                            <label class="form-label">Sales Type</label>
                            <select name="sales_type" class="form-select" required>
                                <option value="">Select Sales Type</option>
                                <option value="Credit">Credit</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Amount</label>
                            <input type="number" name="total_amount" step="0.01" class="form-control" readonly id="total_amount">
                        </div>

                        <!-- Row 5: File Uploads -->
                        <div class="col-md-6">
                            <label class="form-label">Invoice</label>
                            <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Delivery Receipt</label>
                            <input type="file" name="delivery_receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <!-- Row 6: Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Procurement Modal -->
<div class="modal fade" id="editProcurementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Purchase Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/edit_procurement.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="procurement_id" id="edit_procurement_id">
                    <div class="row g-3">
                        <!-- Row 1: Item Name and Invoice No -->
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" id="edit_item_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice No.</label>
                            <input type="text" name="invoice_no" id="edit_invoice_no" class="form-control" required>
                        </div>

                        <!-- Row 2: Supplier and Category -->
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" id="edit_supplier_id" class="form-select" required>
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
                            <label class="form-label">Category</label>
                            <select name="category" id="edit_category" class="form-select" required>
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
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" id="edit_quantity" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit</label>
                            <select name="unit" id="edit_unit" class="form-select" required>
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
                            <label class="form-label">Unit Price</label>
                            <input type="number" name="unit_price" id="edit_unit_price" step="0.01" class="form-control" required>
                        </div>

                        <!-- Row 4: Sales Type and Total Amount -->
                        <div class="col-md-6">
                            <label class="form-label">Sales Type</label>
                            <select name="sales_type" id="edit_sales_type" class="form-select" required>
                                <option value="">Select Sales Type</option>
                                <option value="Credit">Credit</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Amount</label>
                            <input type="number" name="total_amount" id="edit_total_amount" step="0.01" class="form-control" readonly>
                        </div>

                        <!-- Row 5: File Uploads -->
                        <div class="col-md-6">
                            <label class="form-label">Invoice (Optional - leave empty to keep current)</label>
                            <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Delivery Receipt (Optional - leave empty to keep current)</label>
                            <input type="file" name="delivery_receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <!-- Row 6: Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Mobile sidebar toggle
        $('.sidebar-toggle').on('click', function() {
            $('.sidebar').toggleClass('show');
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
            $('#edit_procurement_id').val(button.data('procurement-id'));
            $('#edit_item_name').val(button.data('item-name'));
            $('#edit_invoice_no').val(button.data('invoice-no'));
            $('#edit_quantity').val(button.data('quantity'));
            $('#edit_unit').val(button.data('unit'));
            $('#edit_unit_price').val(button.data('unit-price'));
            $('#edit_sales_type').val(button.data('sales-type'));
            $('#edit_category').val(button.data('category'));
            $('#edit_supplier_id').val(button.data('supplier-id'));
            
            // Calculate and set total amount
            const total = button.data('quantity') * button.data('unit-price');
            $('#edit_total_amount').val(total.toFixed(2));
            
            // Show the modal
            const editModal = new bootstrap.Modal(document.getElementById('editProcurementModal'));
            editModal.show();
            
            console.log('Edit modal populated with:', {
                id: button.data('procurement-id'),
                itemName: button.data('item-name'),
                invoiceNo: button.data('invoice-no'),
                quantity: button.data('quantity'),
                unit: button.data('unit'),
                unitPrice: button.data('unit-price'),   
                salesType: button.data('sales-type')
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
                // Send AJAX request to delete the item
                $.ajax({
                    url: '../actions/delete_procurement.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Item deleted successfully');
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Error deleting item: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error occurred while deleting the item');
                    }
                });
            }
        }
        
        // Delete procurement item functionality
        $('.delete-procurement-btn').on('click', function() {
            const button = $(this);
            deleteProcurementItem(button.data('procurement-id'));
        });
    });
</script>

<?php include '../includes/footer.php'; ?>