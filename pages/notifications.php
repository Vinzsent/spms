<?php
$pageTitle = 'My Notifications';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Get user information
$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$user_name = $_SESSION['user']['first_name'] ?? $_SESSION['name'] ?? 'User';

$dashboard_link = ($_SESSION['user_type'] == 'Admin') ? '../admin_dashboard.php' : '../dashboard.php';

// Handle mark as read actions
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']);
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $notification_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

if (isset($_POST['mark_all_read'])) {
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Get notifications with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filter options
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_read = isset($_GET['read']) ? $_GET['read'] : '';

// Build query
$where_conditions = ["user_id = ?"];
$params = [$user_id];
$param_types = "i";

if ($filter_type) {
    $where_conditions[] = "type = ?";
    $params[] = $filter_type;
    $param_types .= "s";
}

if ($filter_read !== '') {
    $where_conditions[] = "is_read = ?";
    $params[] = $filter_read;
    $param_types .= "i";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM notifications WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($param_types, ...$params);
$count_stmt->execute();
$total_notifications = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_notifications / $per_page);

// Get notifications
$sql = "SELECT id, type, title, message, related_id, related_type, is_read, created_at 
        FROM notifications 
        WHERE $where_clause 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$param_types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get unread count
$unread_sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
$unread_stmt = $conn->prepare($unread_sql);
$unread_stmt->bind_param("i", $user_id);
$unread_stmt->execute();
$unread_count = $unread_stmt->get_result()->fetch_assoc()['unread'];
?>

<?php include('../includes/navbar.php'); ?>

<div class="container" style="margin-top: 120px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-center mb-1">
                <i class="fas fa-bell me-2"></i>My Notifications
            </h3>
            <p class="text-muted mb-0">Manage and view all your notifications</p>
        </div>
        <div>
            <a href="<?php echo $dashboard_link; ?>"><button class="btn btn-secondary me-2" style="background-color: #fd7e14; color: white;">
                <i class="fas fa-arrow-left"></i> Back
            </button></a> 
            <form method="POST" style="display: inline;">
                <button type="submit" name="mark_all_read" class="btn btn-success" onclick="return confirm('Mark all notifications as read?')">
                    <i class="fas fa-check-double me-1"></i>Mark All Read
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="card-title"><?= $total_notifications ?></h4>
                    <p class="card-text">Total Notifications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h4 class="card-title"><?= $unread_count ?></h4>
                    <p class="card-text">Unread</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="card-title"><?= $total_notifications - $unread_count ?></h4>
                    <p class="card-text">Read</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4 class="card-title"><?= $total_pages ?></h4>
                    <p class="card-text">Pages</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Filter by Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        <option value="request" <?= $filter_type === 'request' ? 'selected' : '' ?>>Request</option>
                        <option value="approved" <?= $filter_type === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="issued" <?= $filter_type === 'issued' ? 'selected' : '' ?>>Issued</option>
                        <option value="rejected" <?= $filter_type === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="read" class="form-label">Filter by Status</label>
                    <select class="form-select" id="read" name="read">
                        <option value="">All</option>
                        <option value="0" <?= $filter_read === '0' ? 'selected' : '' ?>>Unread</option>
                        <option value="1" <?= $filter_read === '1' ? 'selected' : '' ?>>Read</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="notifications.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Notifications
                <?php if ($filter_type || $filter_read !== ''): ?>
                    <span class="badge bg-info ms-2">Filtered</span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($notification = $result->fetch_assoc()): ?>
                                <tr class="<?= $notification['is_read'] ? '' : 'table-warning' ?>">
                                    <td>
                                        <span class="badge bg-<?= getNotificationBadgeColor($notification['type']) ?>">
                                            <?= ucfirst($notification['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($notification['title']) ?></strong>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($notification['message']) ?>">
                                            <?= htmlspecialchars($notification['message']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M d, Y g:i A', strtotime($notification['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($notification['is_read']): ?>
                                            <span class="badge bg-success">Read</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Unread</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$notification['is_read']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                <button type="submit" name="mark_read" class="btn btn-sm btn-outline-success" title="Mark as read">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewNotification(<?= $notification['id'] ?>)" title="View details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Notifications pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&type=<?= $filter_type ?>&read=<?= $filter_read ?>">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&type=<?= $filter_type ?>&read=<?= $filter_read ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&type=<?= $filter_type ?>&read=<?= $filter_read ?>">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No notifications found</h5>
                    <p class="text-muted">
                        <?php if ($filter_type || $filter_read !== ''): ?>
                            Try adjusting your filters or 
                            <a href="notifications.php">view all notifications</a>
                        <?php else: ?>
                            You don't have any notifications yet.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">
                    <i class="fas fa-bell me-2"></i>Notification Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, #1a5f3c, #0d4a2a);
    color: white;
    border-radius: 10px 10px 0 0 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.pagination .page-link {
    color: #1a5f3c;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #1a5f3c;
    border-color: #1a5f3c;
}

.pagination .page-link:hover {
    color: #0d4a2a;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.table-warning:hover {
    background-color: rgba(255, 193, 7, 0.2) !important;
}
</style>

<script>
function viewNotification(notificationId) {
    // For now, just show a simple alert with the notification details
    // In a real implementation, you might want to load more details via AJAX
    alert('Notification details would be shown here for ID: ' + notificationId);
    
    // You can implement AJAX loading here:
    /*
    $.ajax({
        url: '../actions/get_notification_details.php',
        type: 'GET',
        data: { notification_id: notificationId },
        success: function(response) {
            if (response.success) {
                $('#notificationModalBody').html(response.html);
                $('#notificationModal').modal('show');
            }
        }
    });
    */
}

// Auto-refresh notifications every 30 seconds
setInterval(function() {
    // Only refresh if no filters are applied
    if (!window.location.search) {
        location.reload();
    }
}, 30000);
</script>

<?php
// Helper function to get badge color based on notification type
function getNotificationBadgeColor($type) {
    switch ($type) {
        case 'approved':
            return 'success';
        case 'issued':
            return 'info';
        case 'rejected':
            return 'danger';
        case 'request':
        default:
            return 'primary';
    }
}
?>

<?php include '../includes/footer.php'; ?>
