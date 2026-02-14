<?php
$pageTitle = 'User Management';
include '../includes/auth.php';

// Check if admin is verified (password was entered correctly)
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    $_SESSION['error'] = 'Admin access required. Please enter the admin password.';
    header("Location: ../dashboard.php");
    exit;
}

// Check if admin session is still valid (30 minutes timeout)
if (isset($_SESSION['admin_verified_time']) && (time() - $_SESSION['admin_verified_time']) > 1800) {
    unset($_SESSION['admin_verified']);
    unset($_SESSION['admin_verified_time']);
    $_SESSION['error'] = 'Admin session expired. Please re-enter the password.';
    header("Location: ../dashboard.php");
    exit;
}

include '../includes/db.php';

// Handle AJAX requests for pagination/search
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $isAjax = true;
} else {
    include '../includes/header.php';
    $isAjax = false;
}

$user_type = $_SESSION['user_type'] ?? '';
$dashboard_link = ($user_type == 'Admin') ? '../dashboard.php' : '../dashboard.php';

$records_per_page = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%$search%";

// Count different user types for dashboard cards (Keep these as total counts)
// These queries are fast enough to run on every page load for now, or could be optimized
$total_users_count = $conn->query("SELECT COUNT(*) as count FROM user")->fetch_assoc()['count'];
$admin_users = $conn->query("SELECT COUNT(*) as count FROM user WHERE user_type = 'Admin'")->fetch_assoc()['count'];
$regular_users = $conn->query("SELECT COUNT(*) as count FROM user WHERE user_type = 'User'")->fetch_assoc()['count'];
$active_users = $conn->query("SELECT COUNT(*) as count FROM user WHERE user_type IN ('Admin', 'User')")->fetch_assoc()['count'];

// Count for pagination (Filtered)
if (!empty($search)) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM user WHERE first_name LIKE ? OR last_name LIKE ? OR user_type LIKE ?");
    $count_stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $count_stmt->execute();
    $total_filtered = $count_stmt->get_result()->fetch_assoc()['count'];
} else {
    $total_filtered = $total_users_count;
}

$total_pages = max(1, (int) ceil($total_filtered / $records_per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $records_per_page;

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE first_name LIKE ? OR last_name LIKE ? OR user_type LIKE ? ORDER BY last_name, first_name LIMIT ?, ?");
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $offset, $records_per_page);
} else {
    $stmt = $conn->prepare("SELECT * FROM user ORDER BY last_name, first_name LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $records_per_page);
}
$stmt->execute();
$result = $stmt->get_result();

// Prepare table rows and pagination for AJAX or initial load
ob_start();
while ($row = $result->fetch_assoc()):
?>
    <tr>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar me-3">
                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                </div>
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></div>
                    <small class="text-muted">ID: <?= $row['id'] ?></small>
                </div>
            </div>
        </td>
        <td>
            <span class="badge <?= $row['user_type'] === 'Admin' ? 'bg-success' : 'bg-primary' ?>">
                <?= htmlspecialchars(strtoupper($row['user_type'])) ?>
            </span>
        </td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td>
            <button class="btn btn-warning"
                onclick="openEditModal(
                                    <?= $row['id'] ?>,
                                    '<?= addslashes($row['title']) ?>',
                                    '<?= addslashes($row['first_name']) ?>',
                                    '<?= addslashes($row['middle_name']) ?>',
                                    '<?= addslashes($row['last_name']) ?>',
                                    '<?= addslashes($row['suffix']) ?>',
                                    '<?= addslashes($row['academic_title']) ?>',
                                    '<?= addslashes($row['user_type']) ?>',
                                    '<?= addslashes($row['username']) ?>'
                                  )">
                <i class="fas fa-edit me-1"></i>Edit
            </button>

            <button class="btn btn-danger"
                onclick="openDeleteModal(
                                    <?= $row['id'] ?>,
                                    '<?= addslashes($row['title']) ?>',
                                    '<?= addslashes($row['first_name']) ?>',
                                    '<?= addslashes($row['middle_name']) ?>',
                                    '<?= addslashes($row['last_name']) ?>',
                                    '<?= addslashes($row['suffix']) ?>',
                                    '<?= addslashes($row['academic_title']) ?>',
                                    '<?= addslashes($row['user_type']) ?>',
                                    '<?= addslashes($row['username']) ?>'
                                  )">
                <i class="fas fa-trash me-1"></i>Delete
            </button>
        </td>
    </tr>
<?php
endwhile;
$table_rows = ob_get_clean();

// Prepare Pagination HTML
ob_start();
if ($total_pages > 0):
?>
    <ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="#" onclick="loadUsers(<?= max(1, $page - 1) ?>); return false;">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                <a class="page-link" href="#" onclick="loadUsers(<?= $i ?>); return false;"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="#" onclick="loadUsers(<?= min($total_pages, $page + 1) ?>); return false;">Next</a>
        </li>
    </ul>
<?php
endif;
$pagination_html = ob_get_clean();

// Return JSON if AJAX
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'table_rows' => $table_rows,
        'pagination' => $pagination_html
    ]);
    exit;
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
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #dee2e6;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .info-card .icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .info-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .info-card .label {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Card colors matching Inventory.php style */
        .card-total {
            color: var(--primary-green);
        }

        .card-admin {
            color: var(--accent-orange);
        }

        .card-user {
            color: var(--accent-red);
        }

        .card-active {
            color: var(--accent-blue);
        }


        /* Section Header */
        .section-header {
            background: var(--primary-green);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Search Bar Styling */
        .search-container {
            flex-grow: 1;
            max-width: 400px;
            margin: 0 1.5rem;
        }

        .search-container .input-group {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 2px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .search-container .form-control {
            background: transparent;
            border: none;
            color: white;
            padding-left: 1rem;
        }

        .search-container .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-container .form-control:focus {
            box-shadow: none;
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .search-container .btn-search {
            background: transparent;
            border: none;
            color: white;
            padding: 0.5rem 1rem;
        }

        /* Table Styling */
        .table-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #dee2e6;
        }

        .table thead th {
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
        }

        /* Buttons */
        .btn-primary {
            background: var(--accent-orange);
            border-color: var(--accent-orange);
            color: white;
        }

        .btn-primary:hover {
            background: #e8690b;
            border-color: #e8690b;
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

            .info-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-header {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 576px) {
            .info-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="../dashboard.php" style="text-decoration: none; color: inherit;">
                <h3>DARTS</h3>
            </a>
            <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
        </div>

        <nav class="sidebar-nav">
            <?php if (strtolower($user_type) != 'purchasing officer'): ?>
                <ul class="nav-item">
                    <li><a href="<?= $dashboard_link ?>" class="nav-link">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a></li>
                    <li><a href="suppliers.php" class="nav-link">
                            <i class="fas fa-users"></i> Suppliers
                        </a></li>
                    <li><a href=" Inventory.php" class="nav-link">
                            <i class="fas fa-boxes"></i> Inventory
                        </a></li>
                    <li><a href="notifications.php" class="nav-link">
                            <i class="fas fa-bell"></i> Notifications
                        </a></li>
                    <li><a href="../logout.php" class="nav-link logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                </ul>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Toggle Sidebar Button for Mobile -->
        <button class="btn btn-dark d-md-none mb-3" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="fas fa-bars"></i> Menu
        </button>

        <div class="content-header">
            <h1>User Management</h1>
            <p>Manage system users, roles, and permissions</p>
        </div>

        <!-- Information Cards -->
        <div class="info-cards">
            <div class="info-card">
                <div class="icon card-total">
                    <i class="fas fa-users"></i>
                </div>
                <div class="number card-total"><?= $total_users_count ?></div>
                <div class="label">Total Users</div>
            </div>

            <div class="info-card">
                <div class="icon card-admin">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="number card-admin"><?= $admin_users ?></div>
                <div class="label">Administrators</div>
            </div>

            <div class="info-card">
                <div class="icon card-user">
                    <i class="fas fa-user"></i>
                </div>
                <div class="number card-user"><?= $regular_users ?></div>
                <div class="label">Regular Users</div>
            </div>

            <div class="info-card">
                <div class="icon card-active">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="number card-active"><?= $active_users ?></div>
                <div class="label">Active Users</div>
            </div>
        </div>

        <!-- User Records Section -->
        <div class="section-header">
            <h2><i class="fas fa-list me-2"></i>User Records</h2>

            <div class="search-container">
                <div class="input-group">
                    <input type="text" id="search" name="search" class="form-control" placeholder="Search name or position..." value="<?= htmlspecialchars($search) ?>" onkeyup="handleSearch()">
                </div>
            </div>

            <button class="btn btn-primary" onclick="document.getElementById('addUser').style.display='block'">
                <i class="fas fa-plus me-2"></i>New User
            </button>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <?= $table_rows ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="User pagination" class="mt-3" id="users-pagination">
                <?= $pagination_html ?>
            </nav>
        </div>
    </div>

    <!-- Modals -->
    <!-- Add User Modal -->
    <div id="addUser" class="modal-custom">
        <div class="modal-content-custom shadow">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                <button class="btn-close" onclick="document.getElementById('addUser').style.display='none'"></button>
            </div>
            <?php include '../modals/add_user.php'; ?>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUser" class="modal-custom">
        <div class="modal-content-custom shadow">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                <button class="btn-close" onclick="document.getElementById('editUser').style.display='none'"></button>
            </div>
            <?php include '../modals/edit_user.php'; ?>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteUser" class="modal-custom">
        <div class="modal-content-custom shadow">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0"><i class="fas fa-user-times me-2"></i>Delete User</h5>
                <button class="btn-close" onclick="document.getElementById('deleteUser').style.display='none'"></button>
            </div>
            <?php include '../modals/delete_user.php'; ?>
        </div>
    </div>

    <style>
        /* Modal Styling (same as before but ensured) */
        .modal-custom {
            display: none;
            position: fixed;
            z-index: 1050;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content-custom {
            background-color: var(--bg-light);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 12px;
            max-width: 500px;
            color: var(--text-dark);
            border: 1px solid #dee2e6;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Modal functions
        window.onclick = function(event) {
            if (event.target.className === 'modal-custom') {
                event.target.style.display = 'none';
            }
        }

        function openEditModal(id, title, firstName, middleName, lastName, suffix, academicTitle, userType, username) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-firstname').value = firstName;
            document.getElementById('edit-middlename').value = middleName || '';
            document.getElementById('edit-lastname').value = lastName;
            document.getElementById('edit-suffix').value = suffix || '';
            document.getElementById('edit-academictitle').value = academicTitle || '';
            document.getElementById('edit-usertype').value = userType;
            document.getElementById('edit-username').value = username;
            document.getElementById('editUser').style.display = 'block';
        }

        function openDeleteModal(id) {
            document.getElementById('delete-id').value = id;
            document.getElementById('deleteUser').style.display = 'block';
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-custom').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });

        // AJAX Search and Pagination
        let searchTimeout;

        function handleSearch() {
            clearTimeout(searchTimeout);
            const searchValue = document.getElementById('search').value;
            // Debounce search
            searchTimeout = setTimeout(function() {
                loadUsers(1, searchValue);
            }, 300);
        }

        function loadUsers(page = 1, searchTerm = null) {
            if (searchTerm === null) {
                searchTerm = document.getElementById('search').value;
            }

            const tbody = document.getElementById('users-tbody');
            const pagination = document.getElementById('users-pagination');

            // Show loading state
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading users...</p></td></tr>';

            const url = new URL(window.location.href);
            url.searchParams.set('ajax', '1');
            url.searchParams.set('page', page);
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        tbody.innerHTML = data.table_rows;
                        pagination.innerHTML = data.pagination;

                        // Update Browser URL
                        const browserUrl = new URL(window.location.href);
                        browserUrl.searchParams.set('page', page);
                        if (searchTerm) {
                            browserUrl.searchParams.set('search', searchTerm);
                        } else {
                            browserUrl.searchParams.delete('search');
                        }
                        window.history.pushState({}, '', browserUrl);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading users.</td></tr>';
                });
        }
    </script>
    <?php include('../includes/footer.php'); ?>
<?php endif; ?>