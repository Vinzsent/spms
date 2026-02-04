<?php
include '../includes/auth.php';
include '../includes/db.php';

// Check admin access (same as users.php)
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$records_per_page = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%$search%";

// Count filtered users for pagination
$count_query = "SELECT COUNT(*) as count FROM user WHERE (first_name LIKE ? OR last_name LIKE ? OR user_type LIKE ?)";
$stmt_count = $conn->prepare($count_query);
$stmt_count->bind_param("sss", $search_param, $search_param, $search_param);
$stmt_count->execute();
$filtered_users = $stmt_count->get_result()->fetch_assoc()['count'];

$total_pages = max(1, (int) ceil($filtered_users / $records_per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $records_per_page;

$stmt = $conn->prepare("SELECT * FROM user WHERE (first_name LIKE ? OR last_name LIKE ? OR user_type LIKE ?) ORDER BY last_name, first_name LIMIT ?, ?");
$stmt->bind_param("sssii", $search_param, $search_param, $search_param, $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Buffering HTML output for Rows
ob_start();
if ($result->num_rows > 0):
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
else:
    ?>
    <tr>
        <td colspan="4" class="text-center py-4 text-muted">
            <i class="fas fa-search me-2"></i>No users found matching "<?= htmlspecialchars($search) ?>"
        </td>
    </tr>
<?php
endif;
$rows_html = ob_get_clean();

// Buffering HTML output for Pagination
ob_start();
if ($total_pages > 0):
?>
    <nav aria-label="User pagination" class="mt-3">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link ajax-pagination" href="#" data-page="<?= max(1, $page - 1) ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                    <a class="page-link ajax-pagination" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link ajax-pagination" href="#" data-page="<?= min($total_pages, $page + 1) ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php
endif;
$pagination_html = ob_get_clean();

header('Content-Type: application/json');
echo json_encode([
    'rows' => $rows_html,
    'pagination' => $pagination_html,
    'total' => $filtered_users
]);
exit;
