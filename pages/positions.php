<?php
$pageTitle = 'Position Management';
include '../includes/auth.php';

// Only admins can access
$user_type = $_SESSION['user_type'] ?? '';
if (strtolower($user_type) !== 'admin') {
    $_SESSION['error'] = 'Access denied. Only administrators can access this page.';
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

$dashboard_link = '../dashboard.php';

$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%$search%";

// Count total records
$total_pos_count = $conn->query("SELECT COUNT(*) as count FROM positions")->fetch_assoc()['count'];

// Count for pagination (Filtered)
if (!empty($search)) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM positions WHERE position_name LIKE ?");
    $count_stmt->bind_param("s", $search_param);
    $count_stmt->execute();
    $total_filtered = $count_stmt->get_result()->fetch_assoc()['count'];
} else {
    $total_filtered = $total_pos_count;
}

$total_pages = max(1, (int) ceil($total_filtered / $records_per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $records_per_page;

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM positions WHERE position_name LIKE ? ORDER BY position_name ASC LIMIT ?, ?");
    $stmt->bind_param("sii", $search_param, $offset, $records_per_page);
} else {
    $stmt = $conn->prepare("SELECT * FROM positions ORDER BY position_name ASC LIMIT ?, ?");
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
            <div class="fw-bold text-dark d-flex align-items-center">
                <i class="fas fa-briefcase text-secondary me-2"></i>
                <?= htmlspecialchars($row['position_name']) ?>
            </div>
            <small class="text-muted ms-4">ID: <?= $row['position_id'] ?></small>
        </td>
        <td>
            <div class="d-flex justify-content-center align-items-center gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(
                                    <?= $row['position_id'] ?>,
                                    '<?= addslashes($row['position_name']) ?>'
                                  ); return false;">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="openDeleteModal(
                                    <?= $row['position_id'] ?>
                                  ); return false;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </td>
    </tr>
<?php
endwhile;

if ($result->num_rows === 0):
?>
    <tr>
        <td colspan="2" class="text-center py-4 text-muted">
            <i class="fas fa-briefcase fa-2x mb-2 d-block opacity-50"></i>
            No positions found.
        </td>
    </tr>
<?php
endif;

$table_rows = ob_get_clean();

// Prepare Pagination HTML
ob_start();
if ($total_pages > 0):
?>
    <ul class="pagination justify-content-center mb-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="#" onclick="loadPositions(<?= max(1, $page - 1) ?>); return false;">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                <a class="page-link" href="#" onclick="loadPositions(<?= $i ?>); return false;"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="#" onclick="loadPositions(<?= min($total_pages, $page + 1) ?>); return false;">Next</a>
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
            --accent-orange: #EACA26;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-light);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 240px;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: white;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-header h3 { margin: 0; font-weight: 700; font-size: 1.5rem; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
        .nav-link {
            display: flex; align-items: center; padding: 10px 20px;
            color: white; text-decoration: none; font-size: 0.9rem;
            border-left: 4px solid transparent; transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--accent-orange); color: white;
        }
        .nav-link i { width: 25px; }

        /* Main Content */
        .main-content {
            margin-left: 280px; padding: 20px; min-height: 100vh;
        }
        .content-header {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px;
        }
        .content-header h1 { margin: 0; font-weight: 700; font-size: 2rem; }
        
        .section-header {
            background: var(--primary-green); color: white;
            padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;
        }
        .section-header h2 { margin: 0; font-size: 1.25rem; font-weight: 600; }
        
        .search-container { flex-grow: 1; max-width: 400px; margin: 0 1.5rem; }
        .search-container input {
            background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);
            color: white; border-radius: 8px; padding: 8px 15px; width: 100%;
        }
        .search-container input::placeholder { color: rgba(255,255,255,0.7); }
        .search-container input:focus { background: rgba(255,255,255,0.2); outline: none; color: white; }

        .table-container {
            background: white; border-radius: 12px; padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .table thead th { background: var(--primary-green); color: white; border: none; padding: 1rem; }
        .btn-primary { background: var(--accent-orange); border: none; color: white; }
        .btn-primary:hover { background: #d4b521; }

        /* Modals */
        .modal-custom {
            display: none; position: fixed; z-index: 1050; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); overflow: auto;
        }
        .modal-content-custom {
            background: white; margin: 5% auto; padding: 2rem; border-radius: 12px;
            max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <button class="btn btn-dark d-md-none mb-3" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="fas fa-bars"></i> Menu
        </button>

        <div class="content-header">
            <h1>Position Management</h1>
            <p class="mb-0">Manage job positions used in user profiles and authorizations</p>
        </div>

        <div class="section-header">
            <h2><i class="fas fa-briefcase me-2"></i>Positions (<?= $total_pos_count ?>)</h2>
            <div class="search-container">
                <input type="text" id="search" placeholder="Search position..." value="<?= htmlspecialchars($search) ?>" onkeyup="handleSearch()">
            </div>
            <button class="btn btn-primary" onclick="document.getElementById('addPosModal').style.display='block'">
                <i class="fas fa-plus me-2"></i>New Position
            </button>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Position Title</th>
                            <th class="text-center" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pos-tbody">
                        <?= $table_rows ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Pagination" class="mt-4" id="pos-pagination">
                <?= $pagination_html ?>
            </nav>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addPosModal" class="modal-custom">
        <div class="modal-content-custom">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>Add Position</h5>
                <button type="button" class="btn-close" onclick="document.getElementById('addPosModal').style.display='none'"></button>
            </div>
            <?php include '../modals/add_position.php'; ?>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editPosModal" class="modal-custom">
        <div class="modal-content-custom">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Edit Position</h5>
                <button type="button" class="btn-close" onclick="document.getElementById('editPosModal').style.display='none'"></button>
            </div>
            <?php include '../modals/edit_position.php'; ?>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deletePosModal" class="modal-custom">
        <div class="modal-content-custom">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Delete Position</h5>
                <button type="button" class="btn-close" onclick="document.getElementById('deletePosModal').style.display='none'"></button>
            </div>
            <?php include '../modals/delete_position.php'; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Check for session messages
        <?php if(isset($_SESSION['success'])): ?>
            Swal.fire({ icon: 'success', title: 'Success!', text: <?= json_encode($_SESSION['success']) ?>, timer: 3000, showConfirmButton: false });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            Swal.fire({ icon: 'error', title: 'Error!', text: <?= json_encode($_SESSION['error']) ?> });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // Modal triggers
        window.onclick = function(e) {
            if (e.target.className === 'modal-custom') e.target.style.display = 'none';
        }

        function openEditModal(id, name) {
            document.getElementById('edit_pos_id').value = id;
            document.getElementById('edit_pos_name').value = name;
            document.getElementById('editPosModal').style.display = 'block';
        }

        function openDeleteModal(id) {
            document.getElementById('delete_pos_id').value = id;
            document.getElementById('deletePosModal').style.display = 'block';
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-custom').forEach(m => m.style.display = 'none');
            }
        });

        // AJAX Search and Pagination
        let searchTimeout;
        function handleSearch() {
            clearTimeout(searchTimeout);
            const val = document.getElementById('search').value;
            searchTimeout = setTimeout(() => loadPositions(1, val), 300);
        }

        function loadPositions(page = 1, searchTerm = null) {
            if (searchTerm === null) searchTerm = document.getElementById('search').value;
            const tbody = document.getElementById('pos-tbody');
            const pagination = document.getElementById('pos-pagination');

            tbody.innerHTML = '<tr><td colspan="2" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i><br>Loading...</td></tr>';

            const url = new URL(window.location.href);
            url.searchParams.set('ajax', '1');
            url.searchParams.set('page', page);
            if (searchTerm) url.searchParams.set('search', searchTerm);
            else url.searchParams.delete('search');

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        tbody.innerHTML = data.table_rows;
                        pagination.innerHTML = data.pagination;
                        window.history.pushState({}, '', url);
                    }
                })
                .catch(err => {
                    console.error(err);
                    tbody.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Error loading data.</td></tr>';
                });
        }
    </script>
    <?php include('../includes/footer.php'); ?>
<?php endif; ?>
