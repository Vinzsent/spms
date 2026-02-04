<?php
$pageTitle = 'Audit Management';
include '../includes/auth.php';
include '../includes/db.php';

// Check if user has access (Referencing roles from spec: Audit Admin, Internal/External Auditor, Asset Manager, System Admin)
// For now, allow access but we could restrict based on $_SESSION['user_type']

// Handle AJAX requests or form submissions here in the future
$isAjax = false;
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $isAjax = true;
} else {
    include '../includes/header.php';
}

$user_type = $_SESSION['user_type'] ?? '';
$dashboard_link = ($user_type == 'Admin') ? '../dashboard.php' : '../dashboard.php';

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

        /* Sidebar Styles - Consistent with Inventory.php */
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
            list-style: none;
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

        /* Card Styles for Dashboard */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--text-white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .stat-card .icon-bg {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.1;
            color: var(--primary-green);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Tabs Styling */
        .nav-tabs .nav-link {
            color: var(--text-dark);
            font-weight: 500;
            border: none;
            padding: 12px 20px;
            border-radius: 10px 10px 0 0;
        }

        .nav-tabs .nav-link.active {
            background: var(--text-white);
            color: var(--primary-green);
            border-bottom: 3px solid var(--accent-orange);
            font-weight: 700;
        }

        .tab-content {
            background: var(--text-white);
            padding: 20px;
            border-radius: 0 10px 10px 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            min-height: 500px;
        }

        /* Tables */
        .table-responsive {
            margin-top: 15px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-open {
            background-color: #ffeeba;
            color: #856404;
        }

        .status-closed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-critical {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
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
                <li><a href="issuance.php" class="nav-link">
                        <i class="fas fa-hand-holding"></i> Issuance
                    </a></li>
                <li><a href="Inventory.php" class="nav-link">
                        <i class="fas fa-boxes"></i> Inventory
                    </a></li>
                <li><a href="audit.php" class="nav-link active">
                        <i class="fas fa-search"></i> Audit
                    </a></li>
                <li><a href="notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i> Notifications
                    </a></li>
                <li><a href="../logout.php" class="nav-link logout" style="color: var(--accent-red); margin-top: 20px;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <h1>Audit Module</h1>
            <p>Ensure transparency, accountability, and compliance across the Asset Management System</p>
        </div>

        <!-- Audit Tabs -->
        <ul class="nav nav-tabs" id="auditTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="planning-tab" data-bs-toggle="tab" data-bs-target="#planning" type="button" role="tab">Audit Planning</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="execution-tab" data-bs-toggle="tab" data-bs-target="#execution" type="button" role="tab">My Audits</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="findings-tab" data-bs-toggle="tab" data-bs-target="#findings" type="button" role="tab">Findings & Actions</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">Audit Logs</button>
            </li>
        </ul>

        <div class="tab-content" id="auditTabsContent">

            <!-- OVERVIEW DASHBOARD -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Audit Dashboard</h4>
                    <button class="btn btn-primary"><i class="fas fa-file-download me-2"></i>Generate Report</button>
                </div>

                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-number text-success">92%</div>
                        <div class="stat-label">Compliance Rate</div>
                        <i class="fas fa-check-circle icon-bg"></i>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-warning">5</div>
                        <div class="stat-label">Open Findings</div>
                        <i class="fas fa-exclamation-circle icon-bg"></i>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-info">3</div>
                        <div class="stat-label">Active Audits</div>
                        <i class="fas fa-search icon-bg"></i>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number text-danger">2</div>
                        <div class="stat-label">Overdue Actions</div>
                        <i class="fas fa-clock icon-bg"></i>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-bottom-0 pt-3">
                                <h5 class="mb-0">Recent Activities</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item border-0 ps-0"><i class="fas fa-circle text-primary me-2 small"></i> Inventoy Audit 2024 (Q1) Started by John Doe</li>
                                    <li class="list-group-item border-0 ps-0"><i class="fas fa-circle text-success me-2 small"></i> Compliance Audit Completed</li>
                                    <li class="list-group-item border-0 ps-0"><i class="fas fa-circle text-warning me-2 small"></i> New Finding Reported: Missing Assets in IT Lab</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white border-bottom-0 pt-3">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary text-start"><i class="fas fa-plus-circle me-2"></i> Create New Audit Plan</button>
                                    <button class="btn btn-outline-success text-start"><i class="fas fa-clipboard-check me-2"></i> Start Physical Asset Verification</button>
                                    <button class="btn btn-outline-warning text-start"><i class="fas fa-flag me-2"></i> Report a Violation</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AUDIT PLANNING -->
            <div class="tab-pane fade" id="planning" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Audit Plans</h4>
                    <button class="btn btn-success"><i class="fas fa-plus me-2"></i>New Plan</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Plan ID</th>
                                <th>Type</th>
                                <th>Scope</th>
                                <th>Start Date</th>
                                <th>Lead Auditor</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Mock Data -->
                            <tr>
                                <td>#Plan-001</td>
                                <td>Asset Audit</td>
                                <td>Inventory & Warehouse</td>
                                <td>Jan 15, 2026</td>
                                <td>Jane Smith</td>
                                <td><span class="status-badge status-open">In Progress</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#Plan-002</td>
                                <td>Financial Audit</td>
                                <td>Procurement & Costs</td>
                                <td>Feb 01, 2026</td>
                                <td>Alex Johnson</td>
                                <td><span class="badge bg-secondary">Scheduled</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- EXECUTION / MY AUDITS -->
            <div class="tab-pane fade" id="execution" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>My Assigned Audits</h4>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5><i class="fas fa-tasks me-2 text-primary"></i> Inventory Audit 2024 (Q1)</h5>
                        <p class="text-muted mb-2">Scope: Main Warehouse A • Due: Jan 30, 2026</p>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small>45% Completed</small>
                            <button class="btn btn-sm btn-primary">Resume Audit</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FINDINGS & ACTIONS -->
            <div class="tab-pane fade" id="findings" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Findings & Corrective Actions</h4>
                    <div class="d-flex gap-2">
                        <select class="form-select w-auto">
                            <option>All Categories</option>
                            <option>Compliance</option>
                            <option>Financial</option>
                            <option>Operational</option>
                        </select>
                        <select class="form-select w-auto">
                            <option>All Severities</option>
                            <option>Critical</option>
                            <option>High</option>
                            <option>Medium</option>
                            <option>Low</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Finding ID</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Severity</th>
                                <th>Root Cause</th>
                                <th>Action Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#FND-105</td>
                                <td>Discrepancy in Laptop counts (Location B)</td>
                                <td>Operational</td>
                                <td><span class="badge bg-danger">Critical</span></td>
                                <td>System delayed update</td>
                                <td><span class="status-badge status-open">Open</span></td>
                                <td>Jan 28, 2026</td>
                            </tr>
                            <tr>
                                <td>#FND-102</td>
                                <td>Missing maintenance log signature</td>
                                <td>Compliance</td>
                                <td><span class="badge bg-warning text-dark">Medium</span></td>
                                <td>Human Error</td>
                                <td><span class="status-badge status-closed">Closed</span></td>
                                <td>Jan 10, 2026</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- AUDIT LOGS -->
            <div class="tab-pane fade" id="logs" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>System & Audit Logs</h4>
                    <input type="text" class="form-control w-25" placeholder="Search logs...">
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Timestamp</th>
                                <th>Action</th>
                                <th>Performed By</th>
                                <th>Module</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-nowrap">2026-01-24 09:30:15</td>
                                <td>Login Success</td>
                                <td>Admin User</td>
                                <td>Auth</td>
                                <td>192.168.1.100</td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">2026-01-24 09:15:22</td>
                                <td>Updated Inventory Item #55</td>
                                <td>Supply Manager</td>
                                <td>Inventory</td>
                                <td>192.168.1.101</td>
                            </tr>
                            <tr>
                                <td class="text-nowrap">2026-01-24 08:45:10</td>
                                <td>Generated Audit Report</td>
                                <td>Internal Auditor</td>
                                <td>Audit</td>
                                <td>192.168.1.105</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- End Tab Content -->

    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>