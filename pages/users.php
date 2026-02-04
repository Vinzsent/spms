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
include '../includes/header.php';

$records_per_page = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%$search%";

// Count different user types for dashboard cards
$total_users = $conn->query("SELECT COUNT(*) as count FROM user")->fetch_assoc()['count'];
$admin_users = $conn->query("SELECT COUNT(*) as count FROM user WHERE user_type = 'Admin'")->fetch_assoc()['count'];
$regular_users = $conn->query("SELECT COUNT(*) as count FROM user WHERE user_type = 'User'")->fetch_assoc()['count'];
$active_users = $conn->query("SELECT COUNT(*) as count FROM user WHERE user_type IN ('Admin', 'User')")->fetch_assoc()['count'];

// Count filtered users for pagination
$count_query = "SELECT COUNT(*) as count FROM user WHERE (first_name LIKE ? OR last_name LIKE ? OR user_type LIKE ?)";
$stmt_count = $conn->prepare($count_query);
$stmt_count->bind_param("sss", $search_param, $search_param, $search_param);
$stmt_count->execute();
$filtered_users = $stmt_count->get_result()->fetch_assoc()['count'];

$total_pages = max(1, (int) ceil($filtered_users / $records_per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $records_per_page;

$stmt = $conn->prepare("SELECT * FROM user WHERE (first_name LIKE ? OR last_name LIKE ? OR user_type LIKE ?) ORDER BY last_name, first_name LIMIT ?, ?");
$stmt->bind_param("sssii", $search_param, $search_param, $search_param, $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Display session messages
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>

<?php include('../includes/navbar.php'); ?>

<style>
    /* Main color scheme matching the image */
    :root {
        --primary-green: #1a5f3c;
        --secondary-green: #2d7a4d;
        --light-green: #4a9c6b;
        --accent-orange: #fd7e14;
        --accent-red: #dc3545;
        --accent-blue: #0d6efd;
        --text-dark: #212529;
        --text-light: #6c757d;
        --bg-light: #ffffff;
        --border-light: #dee2e6;
    }

    /* Header Banner */
    .header-banner {
        background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .header-banner h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .header-banner p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }

    /* Information Cards */
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
        border: 1px solid var(--border-light);
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
        color: var(--text-light);
        font-weight: 500;
    }

    /* Card colors */
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
    }

    .section-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .search-box {
        position: relative;
        max-width: 300px;
    }

    .search-box input {
        padding-left: 2.5rem;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .search-box input:focus {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: white;
        box-shadow: none;
    }

    .search-box input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .search-box .fa-search {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.7);
    }

    /* Table Styling */
    .table-container {
        background: var(--bg-light);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border-light);
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: var(--primary-green);
        color: white;
        border: none;
        padding: 1rem;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-light);
    }

    .table tbody tr:hover {
        background-color: rgba(26, 95, 60, 0.05);
    }

    /* Buttons */
    .btn-primary {
        background: var(--accent-orange);
        border-color: var(--accent-orange);
        color: white;
        font-weight: 500;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background: #e8690b;
        border-color: #e8690b;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: var(--text-light);
        border-color: var(--text-light);
        color: white;
        font-weight: 500;
        padding: 0.5rem 1.5rem;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-secondary:hover {
        background: #5a6268;
        border-color: #5a6268;
        color: white;
    }

    .btn-warning {
        background: var(--accent-orange);
        border-color: var(--accent-orange);
        color: white;
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        margin-right: 0.5rem;
    }

    .btn-warning:hover {
        background: #e8690b;
        border-color: #e8690b;
        color: white;
    }

    .btn-danger {
        background: var(--accent-red);
        border-color: var(--accent-red);
        color: white;
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
    }

    .btn-danger:hover {
        background: #c82333;
        border-color: #c82333;
        color: white;
    }

    /* Modal Styling */
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
        border: 1px solid var(--border-light);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .info-cards {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .header-banner h1 {
            font-size: 2rem;
        }

        .section-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        .info-cards {
            grid-template-columns: 1fr;
        }

        .header-banner {
            padding: 1.5rem 0;
        }

        .header-banner h1 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="container-fluid" style="margin-top: 100px;">
    <!-- Header Banner -->
    <div class="header-banner">
        <div class="container">
            <h1>User Management</h1>
            <p>Manage system users, roles, and permissions</p>
            <a href="../dashboard.php"><button class="btn btn-primary mt-3"><i class="fas fa-arrow-left me-2"></i> Previous Page</button></a>
        </div>
    </div>

    <div class="container">
        <!-- Information Cards -->
        <div class="info-cards">
            <div class="info-card">
                <div class="icon card-total">
                    <i class="fas fa-users"></i>
                </div>
                <div class="number card-total"><?= $total_users ?></div>
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
            <div class="d-flex align-items-center gap-3">
                <form action="" method="GET" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" name="search" class="form-control" placeholder="Search name or position..." value="<?= htmlspecialchars($search) ?>">
                    <?php if ($search): ?>
                        <a href="users.php" class="btn btn-sm text-white position-absolute end-0 top-50 translate-middle-y me-2">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
                <button class="btn btn-primary" onclick="document.getElementById('addUser').style.display='block'">
                    <i class="fas fa-plus me-2"></i>New User
                </button>
            </div>
        </div>

        <!-- User Table -->
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
                    <tbody id="user-table-body">
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div id="pagination-container">
                <?php if ($total_pages > 0): ?>
                    <nav aria-label="User pagination" class="mt-3">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= max(1, $page - 1) ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= min($total_pages, $page + 1) ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

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

    <script>
        // Close modals when clicking outside
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

            // Show the edit modal
            document.getElementById('editUser').style.display = 'block';
        }

        function openDeleteModal(id) {
            document.getElementById('delete-id').value = id;
            // Show the delete modal
            document.getElementById('deleteUser').style.display = 'block';
        }

        // AJAX Search and Pagination
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const tableBody = document.getElementById('user-table-body');
            const paginationContainer = document.getElementById('pagination-container');
            let debounceTimer;

            function fetchUsers(search = '', page = 1) {
                const url = `../api/users_search.php?search=${encodeURIComponent(search)}&page=${page}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Search error:', data.error);
                            return;
                        }
                        tableBody.innerHTML = data.rows;
                        paginationContainer.innerHTML = data.pagination;

                        // Update URL without reloading
                        const newUrl = window.location.pathname + `?search=${encodeURIComponent(search)}&page=${page}`;
                        window.history.pushState({
                            search,
                            page
                        }, '', newUrl);
                    })
                    .catch(error => console.error('Error fetching users:', error));
            }

            // Debounced search input
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    clearTimeout(debounceTimer);
                    const searchTerm = e.target.value;
                    debounceTimer = setTimeout(() => {
                        fetchUsers(searchTerm, 1);
                    }, 300);
                });

                // Prevent form submission
                const searchForm = searchInput.closest('form');
                if (searchForm) {
                    searchForm.addEventListener('submit', (e) => e.preventDefault());
                }
            }

            // Handle AJAX pagination clicks
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('ajax-pagination')) {
                    e.preventDefault();
                    const page = e.target.getAttribute('data-page');
                    const searchTerm = searchInput ? searchInput.value : '';
                    fetchUsers(searchTerm, page);
                }
            });

            // Handle browser back/forward buttons
            window.addEventListener('popstate', function(event) {
                if (event.state) {
                    if (searchInput) searchInput.value = event.state.search || '';
                    fetchUsers(event.state.search || '', event.state.page || 1);
                }
            });
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-custom').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });

        // Initialize DataTable with dark mode support
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any DataTables if present
            if ($.fn.DataTable.isDataTable('table')) {
                $('table').DataTable({
                    responsive: true,
                    pageLength: 10,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search..."
                    },
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ]
                });
            }
        });
    </script>

    <?php include('../includes/footer.php'); ?>

    </html>