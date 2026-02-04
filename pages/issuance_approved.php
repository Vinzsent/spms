<?php

$pageTitle = 'Issuance Management';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Get user information from session with multiple fallbacks
$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['user']['id'] ?? '';

// Get user name with fallbacks
$user_name = '';
if (isset($_SESSION['name'])) {
    $user_name = $_SESSION['name'];
} elseif (isset($_SESSION['user']['name'])) {
    $user_name = $_SESSION['user']['name'];
} elseif (isset($_SESSION['user']['first_name']) && isset($_SESSION['user']['last_name'])) {
    $user_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
} elseif (isset($_SESSION['email'])) {
    $user_name = $_SESSION['email'];
} else {
    $user_name = 'Unknown User';
}

$dashboard_link = ($user_type == 'Admin') ? '../admin_dashboard.php' : '../dashboard.php';

// Get supply requests with status information
$sql = "SELECT sr.*, 
        sr.noted_by, sr.checked_by, sr.verified_by, sr.issued_by, sr.approved_by,
        sr.noted_date, sr.checked_date, sr.verified_date, sr.issued_date, sr.approved_date
        FROM supply_request sr 
        ORDER BY sr.date_requested DESC";
$result = $conn->query($sql);

// Count statistics
$total_requests = $result->num_rows;
$pending_requests = 0;
$approved_requests = 0;
$issued_requests = 0;

// Reset result pointer for counting
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    if (empty($row['approved_by'])) {
        $pending_requests++;
    } else {
        $approved_requests++;
    }
    if (!empty($row['issued_by'])) {
        $issued_requests++;
    }
}

// Reset result pointer for display
$result->data_seek(0);

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
        --primary-color: #073b1d;
        --secondary-color: #ff6b35;
        --accent-color: #28a745;
        --light-bg: #f8f9fa;
        --dark-bg: #343a40;
        --text-light: #ffffff;
        --text-dark: #212529;
        --border-color: #dee2e6;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --border-radius: 10px;
    }

    body {
        background: var(--light-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
    }

    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 280px;
        background: linear-gradient(135deg, var(--primary-color), #0d4a2a);
        color: var(--text-light);
        z-index: 1000;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header {
        padding: 2rem 1.5rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-light);
        text-decoration: none;
        display: block;
        margin-bottom: 0.5rem;
    }

    .sidebar-user {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .sidebar-nav {
        padding: 1rem 0;
    }

    .nav-item {
        margin-bottom: 0.25rem;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        color: var(--text-light);
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 0;
        position: relative;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-light);
        transform: translateX(5px);
    }

    .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        border-left: 4px solid var(--secondary-color);
    }

    .nav-link i {
        width: 20px;
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }

    .nav-link.logout {
        color: var(--secondary-color);
        margin-top: 2rem;
    }

    .nav-link.logout:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-light);
        transform: translateX(5px);
    }

    /* Main Content */
    .main-content {
        margin-left: 280px;
        min-height: 100vh;
        background: var(--light-bg);
    }

    /* Header */
    .page-header {
        background: linear-gradient(135deg, var(--primary-color), #0d4a2a);
        color: var(--text-light);
        padding: 2rem 2rem 1.5rem;
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    /* Stats Cards */
    .stats-container {
        padding: 0 2rem 2rem;
    }

    .stats-card {
        background: var(--text-light);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stats-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* Content Section */
    .content-section {
        padding: 0 2rem 2rem;
    }

    .section-header {
        background: linear-gradient(135deg, var(--primary-color), #0d4a2a);
        color: var(--text-light);
        padding: 1.5rem 2rem;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }

    /* Table Styles */
    .table-container {
        background: var(--text-light);
        border-radius: 0 0 var(--border-radius) var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .table-modern {
        margin: 0;
    }

    .table-modern thead th {
        background: #f8f9fa;
        border: none;
        padding: 1rem;
        font-weight: 600;
        color: var(--text-dark);
        border-bottom: 2px solid var(--border-color);
    }

    .table-modern tbody td {
        padding: 1rem;
        border: none;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .table-modern tbody tr:hover {
        background: rgba(7, 59, 29, 0.05);
    }

    /* Status Badges */
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: linear-gradient(135deg, #ffc107, #ffb300);
        color: #000;
    }

    .status-noted {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
    }

    .status-checked {
        background: linear-gradient(135deg, #6f42c1, #5a32a3);
        color: white;
    }

    .status-verified {
        background: linear-gradient(135deg, #fd7e14, #e55a00);
        color: white;
    }

    .status-approved {
        background: linear-gradient(135deg, #20c997, #1a9f7a);
        color: white;
    }

    .status-issued {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        color: white;
    }

    /* Action Buttons */
    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
        border: none;
        transition: all 0.3s ease;
        margin: 0.25rem;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, var(--secondary-color), #e55a00);
        color: white;
    }

    .btn-success-modern {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        color: white;
    }

    .btn-info-modern {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
    }

    .btn-warning-modern {
        background: linear-gradient(135deg, #ffc107, #ffb300);
        color: #000;
    }

    .btn-danger-modern {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    /* Modal Styles */
    .modal-modern .modal-header {
        background: linear-gradient(135deg, var(--primary-color), #0d4a2a);
        color: var(--text-light);
        border-bottom: none;
    }

    .modal-modern .modal-title {
        font-weight: 600;
    }

    .modal-modern .modal-body {
        padding: 2rem;
    }

    .modal-modern .modal-footer {
        border-top: none;
        padding: 1rem 2rem 2rem;
    }

    /* Form Styles */
    .form-control-modern {
        border: 2px solid var(--border-color);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control-modern:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(7, 59, 29, 0.25);
    }

    .form-label-modern {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    /* Responsive Design */
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

        .page-header {
            padding: 1.5rem 1rem 1rem;
        }

        .content-section {
            padding: 0 1rem 1rem;
        }

        .stats-container {
            padding: 0 1rem 1rem;
        }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h5 {
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .empty-state p {
        margin-bottom: 2rem;
        opacity: 0.7;
    }

    /* Additional styles for timeline and info cards */
    .info-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        border-left: 4px solid #28a745;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .info-title {
        color: #28a745;
        font-weight: 600;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .info-content {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 140px;
    }

    .info-value {
        color: #212529;
        text-align: right;
        flex: 1;
    }

    .status-timeline {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .timeline-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #dee2e6;
    }

    .timeline-item.completed {
        border-left-color: #28a745;
        background: rgba(40, 167, 69, 0.1);
    }

    .timeline-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }

    .timeline-content h6 {
        margin: 0;
        font-weight: 600;
        color: #495057;
    }

    .timeline-content p {
        margin: 0;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .bg-info {
        background: #17a2b8;
    }

    .bg-purple {
        background: #6f42c1;
    }

    .bg-warning {
        background: #ffc107;
        color: #000;
    }

    .bg-success {
        background: #28a745;
    }

    .bg-primary {
        background: #007bff;
    }
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-cubes me-2"></i>
            DARTS
        </div>
        <div class="sidebar-user">
            Welcome, <?= htmlspecialchars($user_name) ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="../dashboard.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="suppliers.php" class="nav-link">
                <i class="fas fa-users"></i>
                Supplier List
            </a>
        </div>
        <div class="nav-item">
            <a href="supply_request.php" class="nav-link">
                <i class="fas fa-clipboard-list"></i>
                Request List
            </a>
        </div>
        <div class="nav-item">
            <a href="procurement.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>
                Procurement
            </a>
        </div>
        <div class="nav-item">
            <a href="inventory.php" class="nav-link">
                <i class="fas fa-box"></i>
                Inventory
            </a>
        </div>
        <div class="nav-item">
            <a href="transaction_list.php" class="nav-link">
                <i class="fas fa-exchange-alt"></i>
                Transactions
            </a>
        </div>
        <div class="nav-item">
            <a href="issuance.php" class="nav-link active">
                <i class="fas fa-hand-holding-usd"></i>
                Issuance
            </a>
        </div>
        <div class="nav-item">
            <a href="../logout.php" class="nav-link logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title">Issuance Management</h1>
                <p class="page-subtitle">Manage supply requests and track approval status</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #28a745, #1e7e34); color: white;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stats-number"><?= $total_requests ?></div>
                    <div class="stats-label">Total Requests</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ffc107, #ffb300); color: #000;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number"><?= $pending_requests ?></div>
                    <div class="stats-label">Pending Approval</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #20c997, #1a9f7a); color: white;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number"><?= $approved_requests ?></div>
                    <div class="stats-label">Approved Requests</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stats-number"><?= $issued_requests ?></div>
                    <div class="stats-label">Issued Items</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supply Requests Section -->
    <div class="content-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-clipboard-list me-2"></i>
                Requests List
            </h2>
        </div>

        <div class="table-container">
            <?php if ($total_requests > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar me-2"></i>Date Requested</th>
                                <th><i class="fas fa-box me-2"></i>Item Description</th>
                                <th><i class="fas fa-hashtag me-2"></i>Quantity Needed</th>
                                <th><i class="fas fa-dollar-sign me-2"></i>Total Cost</th>
                                <th><i class="fas fa-user me-2"></i>Requested By</th>
                                <th><i class="fas fa-tasks me-2"></i>Status</th>
                                <th><i class="fas fa-cogs me-2"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('M d, Y', strtotime($row['date_requested'])) ?></strong>
                                        <br>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($row['request_description']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                Category: <?= htmlspecialchars($row['category']) ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            <?= $row['quantity_requested'] ?> <?= htmlspecialchars($row['unit']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            ₱<?= number_format($row['total_cost'], 2) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($row['department_unit']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($row['purpose']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status = 'Pending';
                                        $statusClass = 'status-pending';

                                        if (!empty($row['approved_by'])) {
                                            $status = 'Approved';
                                            $statusClass = 'status-approved';
                                        } elseif (!empty($row['issued_by'])) {
                                            $status = 'Issued';
                                            $statusClass = 'status-issued';
                                        } elseif (!empty($row['verified_by'])) {
                                            $status = 'Verified';
                                            $statusClass = 'status-verified';
                                        } elseif (!empty($row['checked_by'])) {
                                            $status = 'Checked';
                                            $statusClass = 'status-checked';
                                        } elseif (!empty($row['noted_by'])) {
                                            $status = 'Noted';
                                            $statusClass = 'status-noted';
                                        }
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-info-modern btn-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewRequestModal"
                                                data-request-id="<?= $row['request_id'] ?>"
                                                data-date="<?= htmlspecialchars($row['date_requested']) ?>"
                                                data-description="<?= htmlspecialchars($row['request_description']) ?>"
                                                data-quantity="<?= $row['quantity_requested'] ?>"
                                                data-unit="<?= htmlspecialchars($row['unit']) ?>"
                                                data-cost="<?= $row['total_cost'] ?>"
                                                data-department="<?= htmlspecialchars($row['department_unit']) ?>"
                                                data-purpose="<?= htmlspecialchars($row['purpose']) ?>"
                                                data-category="<?= htmlspecialchars($row['category']) ?>"
                                                data-noted="<?= htmlspecialchars($row['noted_by'] ?? '') ?>"
                                                data-checked="<?= htmlspecialchars($row['checked_by'] ?? '') ?>"
                                                data-verified="<?= htmlspecialchars($row['verified_by'] ?? '') ?>"
                                                data-approved="<?= htmlspecialchars($row['approved_by'] ?? '') ?>"
                                                data-issued="<?= htmlspecialchars($row['issued_by'] ?? '') ?>"
                                                data-noted-date="<?= htmlspecialchars($row['noted_date'] ?? '') ?>"
                                                data-checked-date="<?= htmlspecialchars($row['checked_date'] ?? '') ?>"
                                                data-verified-date="<?= htmlspecialchars($row['verified_date'] ?? '') ?>"
                                                data-issued-date="<?= htmlspecialchars($row['issued_date'] ?? '') ?>"
                                                data-approved-date="<?= htmlspecialchars($row['approved_date'] ?? '') ?>">
                                                <i class="fas fa-eye me-1"></i>View
                                            </button>
                                            <button class="btn btn-warning-modern btn-action"
                                                data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal"
                                                data-request-id="<?= $row['request_id'] ?>"
                                                data-current-status="<?= $status ?>">
                                                <i class="fas fa-edit me-1"></i>Update Status
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h5>No Supply Requests Found</h5>
                    <p>There are currently no supply requests to manage.</p>
                    <a href="supply_request.php" class="btn btn-primary-modern">
                        <i class="fas fa-plus me-2"></i>Create First Request
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade modal-modern" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRequestModalLabel">
                    <i class="fas fa-eye me-2"></i>Request Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card mb-3">
                            <h6 class="info-title"><i class="fas fa-calendar me-2"></i>Request Information</h6>
                            <div class="info-content">
                                <div class="info-item">
                                    <span class="info-label">Date Requested:</span>
                                    <span class="info-value" id="viewDate"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Department:</span>
                                    <span class="info-value" id="viewDepartment"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Purpose:</span>
                                    <span class="info-value" id="viewPurpose"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Category:</span>
                                    <span class="info-value" id="viewCategory"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card mb-3">
                            <h6 class="info-title"><i class="fas fa-box me-2"></i>Item Details</h6>
                            <div class="info-content">
                                <div class="info-item">
                                    <span class="info-label">Description:</span>
                                    <span class="info-value" id="viewDescription"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Quantity:</span>
                                    <span class="info-value badge bg-primary text-white" id="viewQuantity"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total Cost:</span>
                                    <span class="info-value text-success fw-bold" id="viewCost"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="info-card">
                    <h6 class="info-title"><i class="fas fa-tasks me-2"></i>Approval Status</h6>
                    <div class="status-timeline">
                        <div class="timeline-item" id="notedStatus">
                            <div class="timeline-icon bg-info">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Noted By</h6>
                                <p id="notedInfo">Pending</p>
                            </div>
                        </div>
                        <div class="timeline-item" id="checkedStatus">
                            <div class="timeline-icon bg-purple">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Checked By</h6>
                                <p id="checkedInfo">Pending</p>
                            </div>
                        </div>
                        <div class="timeline-item" id="verifiedStatus">
                            <div class="timeline-icon bg-warning">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Verified By</h6>
                                <p id="verifiedInfo">Pending</p>
                            </div>
                        </div>
                        <div class="timeline-item" id="approvedStatus">
                            <div class="timeline-icon bg-primary">
                                <i class="fas fa-thumbs-up"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Approved By</h6>
                                <p id="approvedInfo">Pending</p>
                            </div>
                        </div>
                        <div class="timeline-item" id="issuedStatus">
                            <div class="timeline-icon bg-success">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Issued By</h6>
                                <p id="issuedInfo">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade modal-modern" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">
                    <i class="fas fa-edit me-2"></i>Update Request Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStatusForm" action="../actions/update_issuance_status.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="updateRequestId">

                    <div class="mb-3">
                        <label for="statusAction" class="form-label-modern">Select Action:</label>
                        <select class="form-select form-control-modern" id="statusAction" name="status_action" required>
                            <option value="">-- Select Action --</option>
                            <option value="noted">Mark as Noted</option>
                            <option value="checked">Mark as Checked</option>
                            <option value="verified">Mark as Verified</option>
                            <option value="approved">Mark as Approved</option>
                            <option value="issued">Mark as Issued</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="actionBy" class="form-label-modern">Action By:</label>
                        <input type="text" class="form-control form-control-modern" id="actionBy" name="action_by"
                            value="<?= htmlspecialchars($user_name) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label-modern">Remarks (Optional):</label>
                        <textarea class="form-control form-control-modern" id="remarks" name="remarks" rows="3"
                            placeholder="Add any additional remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-modern" id="updateStatusBtn">
                        <i class="fas fa-save me-1"></i>Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // View Request Modal
        $(document).on('click', '[data-bs-target="#viewRequestModal"]', function() {
            const data = $(this).data();

            // Populate basic info
            $('#viewDate').text(new Date(data.date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));
            $('#viewDepartment').text(data.department);
            $('#viewPurpose').text(data.purpose);
            $('#viewCategory').text(data.category);
            $('#viewDescription').text(data.description);
            $('#viewQuantity').text(data.quantity + ' ' + data.unit);
            $('#viewCost').text('₱' + parseFloat(data.cost).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            // Update timeline status
            updateTimelineStatus('noted', data.noted, data.notedDate);
            updateTimelineStatus('checked', data.checked, data.checkedDate);
            updateTimelineStatus('verified', data.verified, data.verifiedDate);
            updateTimelineStatus('approved', data.approved, data.approvedDate);
            updateTimelineStatus('issued', data.issued, data.issuedDate);
        });

        function updateTimelineStatus(type, user, date) {
            const item = $(`#${type}Status`);
            const info = $(`#${type}Info`);

            if (user && date) {
                item.addClass('completed');
                info.html(`<strong>${user}</strong><br><small>${new Date(date).toLocaleDateString()}</small>`);
            } else {
                item.removeClass('completed');
                info.text('Pending');
            }
        }

        // Update Status Modal
        $(document).on('click', '[data-bs-target="#updateStatusModal"]', function() {
            const requestId = $(this).data('request-id');
            const currentStatus = $(this).data('current-status');

            $('#updateRequestId').val(requestId);

            // Disable options based on current status
            const statusOrder = ['Pending', 'Noted', 'Checked', 'Verified', 'Approved', 'Issued'];
            const currentIndex = statusOrder.indexOf(currentStatus);

            $('#statusAction option').prop('disabled', false);
            for (let i = 0; i <= currentIndex; i++) {
                $(`#statusAction option[value="${statusOrder[i].toLowerCase()}"]`).prop('disabled', true);
            }
        });

        // Form submission
        $('#updateStatusForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $('#updateStatusBtn');
            const originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (response.success) {
                        $('#updateStatusModal').modal('hide');
                        alert('Status updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    submitBtn.prop('disabled', false).html(originalText);
                    console.log('AJAX Error:', xhr.responseText);
                    alert('An error occurred while updating the status. Please try again.');
                }
            });
        });
    });
</script>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-cubes me-2"></i>
            ASSET MANAGEMENT
        </div>
        <div class="sidebar-user">
            Welcome, <?= htmlspecialchars($user_name) ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="../dashboard.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>
        </div>
        <div class="nav-item">
            <a href="suppliers.php" class="nav-link">
                <i class="fas fa-users"></i>
                Supplier List
            </a>
        </div>
        <div class="nav-item">
            <a href="supply_request.php" class="nav-link">
                <i class="fas fa-clipboard-list"></i>
                Supply Request
            </a>
        </div>
        <div class="nav-item">
            <a href="procurement.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>
                Procurement
            </a>
        </div>
        <div class="nav-item">
            <a href="inventory.php" class="nav-link">
                <i class="fas fa-box"></i>
                Inventory
            </a>
        </div>
        <div class="nav-item">
            <a href="transaction_list.php" class="nav-link">
                <i class="fas fa-exchange-alt"></i>
                Transactions
            </a>
        </div>
        <div class="nav-item">
            <a href="issuance.php" class="nav-link active">
                <i class="fas fa-hand-holding-usd"></i>
                Issuance
            </a>
        </div>
        <div class="nav-item">
            <a href="../logout.php" class="nav-link logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>
</div>

<!-- View Request Modal -->
<div class="modal fade modal-modern" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRequestModalLabel">
                    <i class="fas fa-eye me-2"></i>Request Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card mb-3">
                            <h6 class="info-title"><i class="fas fa-calendar me-2"></i>Request Information</h6>
                            <div class="info-content">
                                <div class="info-item">
                                    <span class="info-label">Date Requested:</span>
                                    <span class="info-value" id="viewDate"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Department:</span>
                                    <span class="info-value" id="viewDepartment"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Purpose:</span>
                                    <span class="info-value" id="viewPurpose"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Category:</span>
                                    <span class="info-value" id="viewCategory"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card mb-3">
                            <h6 class="info-title"><i class="fas fa-box me-2"></i>Item Details</h6>
                            <div class="info-content">
                                <div class="info-item">
                                    <span class="info-label">Description:</span>
                                    <span class="info-value" id="viewDescription"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Quantity:</span>
                                    <span class="info-value badge bg-primary" id="viewQuantity"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total Cost:</span>
                                    <span class="info-value text-success fw-bold" id="viewCost"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="info-card">
                    <h6 class="info-title"><i class="fas fa-tasks me-2"></i>Approval Status</h6>
                    <div class="status-timeline">
                        <div class="timeline-item" id="notedStatus">
                            <div class="timeline-icon bg-info">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Noted By</h6>
                                <p id="notedInfo">Pending</p>
                            </div>
                        </div>
                        <div class="timeline-item" id="checkedStatus">
                            <div class="timeline-icon bg-purple">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Checked By</h6>
                                <p id="checkedInfo">Pending</p>
                            </div>
                        </div>
                        <div class="timeline-item" id="verifiedStatus">
                            <div class="timeline-icon bg-warning">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Verified By</h6>
                                <p id="verifiedInfo">Pending</p>
                            </div>
                        </div>
                        <div class="timeline-item" id="approvedStatus">
                            <div class="timeline-icon bg-primary">
                                <i class="fas fa-thumbs-up"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Approved By</h6>
                                <p id="approvedInfo">Pending</p>
                            </div>
                        </div>
                        <div class="timeline-item" id="issuedStatus">
                            <div class="timeline-icon bg-success">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Issued By</h6>
                                <p id="issuedInfo">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade modal-modern" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">
                    <i class="fas fa-edit me-2"></i>Update Request Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStatusForm" action="../actions/update_issuance_status.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="updateRequestId">

                    <div class="mb-3">
                        <label for="statusAction" class="form-label-modern">Select Action:</label>
                        <select class="form-select form-control-modern" id="statusAction" name="status_action" required>
                            <option value="">-- Select Action --</option>
                            <option value="noted">Mark as Noted</option>
                            <option value="checked">Mark as Checked</option>
                            <option value="verified">Mark as Verified</option>
                            <option value="issued">Mark as Issued</option>
                            <option value="approved">Mark as Approved</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="actionBy" class="form-label-modern">Action By:</label>
                        <input type="text" class="form-control form-control-modern" id="actionBy" name="action_by"
                            value="<?= htmlspecialchars($user_name) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label-modern">Remarks (Optional):</label>
                        <textarea class="form-control form-control-modern" id="remarks" name="remarks" rows="3"
                            placeholder="Add any additional remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary-modern" id="updateStatusBtn">
                        <i class="fas fa-save me-1"></i>Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // View Request Modal
        $(document).on('click', '[data-bs-target="#viewRequestModal"]', function() {
            const data = $(this).data();

            // Populate basic info
            $('#viewDate').text(new Date(data.date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));
            $('#viewDepartment').text(data.department);
            $('#viewPurpose').text(data.purpose);
            $('#viewCategory').text(data.category);
            $('#viewDescription').text(data.description);
            $('#viewQuantity').text(data.quantity + ' ' + data.unit);
            $('#viewCost').text('₱' + parseFloat(data.cost).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            // Update timeline status
            updateTimelineStatus('noted', data.noted, data.notedDate);
            updateTimelineStatus('checked', data.checked, data.checkedDate);
            updateTimelineStatus('verified', data.verified, data.verifiedDate);
            updateTimelineStatus('issued', data.issued, data.issuedDate);
            updateTimelineStatus('approved', data.approved, data.approvedDate);
        });

        function updateTimelineStatus(type, user, date) {
            const item = $(`#${type}Status`);
            const info = $(`#${type}Info`);

            if (user && date) {
                item.addClass('completed');
                info.html(`<strong>${user}</strong><br><small>${new Date(date).toLocaleDateString()}</small>`);
            } else {
                item.removeClass('completed');
                info.text('Pending');
            }
        }

        // Update Status Modal
        $(document).on('click', '[data-bs-target="#updateStatusModal"]', function() {
            const requestId = $(this).data('request-id');
            const currentStatus = $(this).data('current-status');

            $('#updateRequestId').val(requestId);

            // Disable options based on current status
            const statusOrder = ['Pending', 'Noted', 'Checked', 'Verified', 'Approved', 'Issued'];
            const currentIndex = statusOrder.indexOf(currentStatus);

            $('#statusAction option').prop('disabled', false);
            for (let i = 0; i <= currentIndex; i++) {
                $(`#statusAction option[value="${statusOrder[i].toLowerCase()}"]`).prop('disabled', true);
            }
        });

        // Form submission
        $('#updateStatusForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $('#updateStatusBtn');
            const originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (response.success) {
                        $('#updateStatusModal').modal('hide');
                        alert('Status updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    submitBtn.prop('disabled', false).html(originalText);
                    console.log('AJAX Error:', xhr.responseText);
                    alert('An error occurred while updating the status. Please try again.');
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>