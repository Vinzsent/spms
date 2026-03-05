<?php
$pageTitle = 'My Notifications';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Get user information
$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? 0;
$user_name = $_SESSION['user']['first_name'] ?? $_SESSION['name'] ?? 'User';
$user_position = $_SESSION['user']['position'] ?? $_SESSION['position'] ?? $_SESSION['user_type'] ?? '';

// Debug: Log session information to help troubleshoot position detection
error_log('Notifications Debug - Session data: ' . print_r($_SESSION, true));
error_log('Notifications Debug - User position detected: ' . $user_position);
error_log('Notifications Debug - User type: ' . ($_SESSION['user_type'] ?? 'not set'));

// Also check for user_type as position might be stored there
if (empty($user_position) && isset($_SESSION['user_type'])) {
    $user_position = $_SESSION['user_type'];
}

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

<div class="container" style="margin-top: 80px; padding-bottom: 2rem;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1" style="font-size: 1.5rem; font-weight: 700;">
                <i class="fas fa-bell me-2 text-success"></i>My Notifications
            </h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Manage and view all your notifications</p>
        </div>
        <div>
            <a href="../dashboard.php"><button class="btn btn-sm btn-secondary me-1" style="background-color: #fd7e14; color: white; border: none;">
                <i class="fas fa-arrow-left"></i> Back
            </button></a> 
            <form method="POST" style="display: inline;">
                <button type="submit" name="mark_all_read" class="btn btn-sm btn-success" onclick="return confirm('Mark all notifications as read?')">
                    <i class="fas fa-check-double me-1"></i>Mark All Read
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center p-2">
                    <h5 class="card-title mb-0" style="font-size: 1.25rem;"><?= $total_notifications ?></h5>
                    <p class="card-text small mb-0">Total Notifications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center p-2">
                    <h5 class="card-title mb-0" style="font-size: 1.25rem;"><?= $unread_count ?></h5>
                    <p class="card-text small mb-0">Unread</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center p-2">
                    <h5 class="card-title mb-0" style="font-size: 1.25rem;"><?= $total_notifications - $unread_count ?></h5>
                    <p class="card-text small mb-0">Read</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center p-2">
                    <h5 class="card-title mb-0" style="font-size: 1.25rem;"><?= $total_pages ?></h5>
                    <p class="card-text small mb-0">Pages</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body p-2 px-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="type" class="form-label mb-1" style="font-size: 0.8rem; font-weight: 600;">Filter by Type</label>
                    <select class="form-select form-select-sm" id="type" name="type" style="font-size: 0.85rem;">
                        <option value="">All Types</option>
                        <option value="request" <?= $filter_type === 'request' ? 'selected' : '' ?>>Request</option>
                        <option value="approved" <?= $filter_type === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="issued" <?= $filter_type === 'issued' ? 'selected' : '' ?>>Issued</option>
                        <option value="rejected" <?= $filter_type === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="read" class="form-label mb-1" style="font-size: 0.8rem; font-weight: 600;">Filter by Status</label>
                    <select class="form-select form-select-sm" id="read" name="read" style="font-size: 0.85rem;">
                        <option value="">All</option>
                        <option value="0" <?= $filter_read === '0' ? 'selected' : '' ?>>Unread</option>
                        <option value="1" <?= $filter_read === '1' ? 'selected' : '' ?>>Read</option>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="notifications.php" class="btn btn-sm btn-outline-secondary px-3">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #073b1d, #0d4a2a); color: white;">
            <h6 class="mb-0">
                <i class="fas fa-bell me-2"></i>Notifications
                <?php if ($filter_type || $filter_read !== ''): ?>
                    <span class="badge bg-light text-dark ms-2" style="font-size: 0.7rem;">Filtered</span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body p-0">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="background: linear-gradient(135deg, #073b1d, #0d4a2a); color: white;">Type</th>
                                <th style="background: linear-gradient(135deg, #073b1d, #0d4a2a); color: white;">Title</th>
                                <th style="background: linear-gradient(135deg, #073b1d, #0d4a2a); color: white;">Message</th>
                                <th style="background: linear-gradient(135deg, #073b1d, #0d4a2a); color: white;">Date</th>
                                <th style="background: linear-gradient(135deg, #073b1d, #0d4a2a); color: white;">Status</th>
                                <th style="background: linear-gradient(135deg, #073b1d, #0d4a2a); color: white;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($notification = $result->fetch_assoc()): ?>
                                <tr class="<?= $notification['is_read'] ? '' : 'unread-notification' ?>">
                                    <td>
                                        <span class="badge notification-type-badge bg-<?= getNotificationBadgeColor($notification['type']) ?>">
                                            <?= ucfirst($notification['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="notification-title"><?= htmlspecialchars($notification['title']) ?></strong>
                                    </td>
                                    <td class="notification-message-cell">
                                        <?php if (strtolower($user_position) === 'faculty' || strtolower($user_position) === 'staff'): ?>
                                            <!-- Staff and Faculty: click shows full message in modal -->
                                            <span class="notification-message-restricted message-open"
                                                  data-title="<?= htmlspecialchars($notification['title']) ?>"
                                                  data-message="<?= htmlspecialchars($notification['message']) ?>"
                                                  title="Click to read full message">
                                                <?= htmlspecialchars($notification['message']) ?>
                                            </span>
                                        <?php else: ?>
                                            <!-- Admin: click shows full message in modal; separate link to issuance page -->
                                            <span class="notification-message-link message-open"
                                                  data-title="<?= htmlspecialchars($notification['title']) ?>"
                                                  data-message="<?= htmlspecialchars($notification['message']) ?>"
                                                  title="Click to read full message">
                                                <?= htmlspecialchars($notification['message']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="notification-date">
                                            <?= date('M d, Y', strtotime($notification['created_at'])) ?><br>
                                            <small class="text-muted"><?= date('g:i A', strtotime($notification['created_at'])) ?></small>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($notification['is_read']): ?>
                                            <span class="badge bg-success notification-status-badge">
                                                <i class="fas fa-check-circle me-1"></i>Read
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark notification-status-badge">
                                                <i class="fas fa-exclamation-circle me-1"></i>Unread
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (!$notification['is_read']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                                    <button type="submit" name="mark_read" class="btn btn-sm btn-success" title="Mark as read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if (($user_position) !== 'Faculty' && ($user_position) !== 'Staff'): ?>
                                                <a href="issuance.php?id=<?= $notification['related_id'] ?>" class="btn btn-sm btn-info" title="View details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-radius: 8px;
}

.card-header {
    background: linear-gradient(135deg, #073b1d, #0d4a2a);
    color: white;
    border-radius: 8px 8px 0 0 !important;
    border: none;
    padding: 10px 15px;
}

.table {
    margin-bottom: 0;
}

.table th {
    border: 1px solid #dee2e6;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    padding: 8px 10px;
    font-size: 0.85rem;
}

.table td {
    vertical-align: middle;
    padding: 8px 10px;
    border: 1px solid #dee2e6;
    font-size: 0.8rem;
}

.table-bordered {
    border: 1px solid #dee2e6;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.03);
}

/* Unread notification styling */
.unread-notification {
    background-color: rgba(234, 202, 38, 0.1) !important;
    border-left: 4px solid var(--accent-orange);
}

.unread-notification:hover {
    background-color: rgba(234, 202, 38, 0.15) !important;
}

/* Notification type badges */
.notification-type-badge {
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
    font-weight: 600;
    border-radius: 12px;
}

/* Notification title styling */
.notification-title {
    color: #2c3e50;
    font-size: 0.95rem;
}

/* Message cell styling */
.notification-message-cell {
    max-width: 300px;
}

/* Full message modal content */
.notification-full-message {
    white-space: pre-wrap;
    word-break: break-word;
}

.notification-message-link {
    text-decoration: none !important;
    color: #495057 !important;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding: 2px 0;
}

.notification-message-link:hover {
    text-decoration: none !important;
    color: #1a5f3c !important;
    background-color: rgba(26, 95, 60, 0.1);
    border-radius: 4px;
    padding: 2px 6px;
}

/* Restricted message styling for Faculty */
.notification-message-restricted {
    color: #6c757d !important;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding: 2px 0;
    cursor: pointer;
    position: relative;
}

.notification-message-restricted:hover {
    color: #dc3545 !important;
    background-color: rgba(220, 53, 69, 0.1);
    border-radius: 4px;
    padding: 2px 6px;
}

.notification-message-restricted::after {
    content: " 🔒";
    color: #dc3545;
    font-size: 0.8rem;
}

/* Date styling */
.notification-date {
    font-size: 0.8rem;
    color: #495057;
    text-align: center;
    display: block;
    line-height: 1.2;
}

/* Status badge styling */
.notification-status-badge {
    font-size: 0.7rem;
    padding: 0.3rem 0.6rem;
    font-weight: 600;
    border-radius: 12px;
}

/* Button styling */
.btn-sm {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
    margin: 0 1px;
}

.btn-group .btn {
    border-radius: 4px;
}

/* Pagination styling */
.pagination .page-link {
    color: #073b1d;
    border-color: #dee2e6;
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
}

.pagination .page-item.active .page-link {
    background-color: #073b1d;
    border-color: #073b1d;
}

.pagination .page-link:hover {
    color: #0d4a2a;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

/* Responsive table */
@media (max-width: 768px) {
    .table th,
    .table td {
        padding: 8px 4px;
        font-size: 0.85rem;
    }
    
    .notification-message-cell {
        max-width: 200px;
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .btn-group .btn {
        width: 100%;
        margin: 1px 0;
    }
}

/* Card styling improvements */
.card-body {
    padding: 0;
}

.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

/* Empty state styling */
.text-center.py-5 {
    padding: 3rem 1rem !important;
}

.text-center.py-5 i {
    color: #6c757d;
}

.text-center.py-5 h5 {
    color: #6c757d;
    margin-top: 1rem;
}

.text-center.py-5 p {
    color: #6c757d;
}
</style>

<script>
function viewNotification(notificationId) {
    // Deprecated alert; use showNotificationModal with data attributes instead.
    console.warn('viewNotification is deprecated. Use showNotificationModal via .message-open elements. ID:', notificationId);
}

function showNotificationModal(title, message) {
    // Safely set the modal contents using textContent to avoid HTML injection.
    const labelEl = document.getElementById('notificationModalLabel');
    const bodyEl = document.getElementById('notificationModalBody');
    if (labelEl) labelEl.textContent = title || 'Notification Details';

    if (bodyEl) {
        // Build a simple layout with pre-wrap formatting for long messages
        bodyEl.innerHTML = '';
        const msg = document.createElement('div');
        msg.className = 'notification-full-message';
        msg.textContent = message || '';
        bodyEl.appendChild(msg);
    }

    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    modal.show();
}

// Delegate clicks from any element with .message-open to open the modal with full text
(function initMessageOpenHandlers() {
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.message-open');
        if (trigger) {
            const title = trigger.getAttribute('data-title') || '';
            const message = trigger.getAttribute('data-message') || '';
            showNotificationModal(title, message);
            e.preventDefault();
            return false;
        }
    });
})();

function checkFacultyAccess() {
    // Double-check user position on client side (additional security)
    const userPosition = '<?= strtolower($user_position) ?>';
    if (userPosition === 'faculty' || userPosition === 'staff') {
        // Staff and Faculty users are restricted from accessing issuance page
        showFacultyAccessWarning();
        return false; // Prevent navigation
    }
    return true; // Allow navigation for admin users
}

function showFacultyAccessWarning() {
    // Create and show a styled warning modal for restricted users
    const modalHtml = `
        <div class="modal fade" id="facultyWarningModal" tabindex="-1" aria-labelledby="facultyWarningModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="facultyWarningModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Access Restricted
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-lock fa-3x text-danger mb-3"></i>
                        </div>
                        <h6 class="text-danger fw-bold mb-3">You can't access this page</h6>
                        <p class="text-muted mb-3">
                            Faculty and Staff members do not have permission to access the issuance page. 
                            This is restricted to administrative staff only.
                        </p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Need assistance?</strong><br>
                            Please contact the MIS (Management Information System) department for support.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Understood
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove any existing warning modal
    const existingModal = document.getElementById('facultyWarningModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add the modal to the body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('facultyWarningModal'));
    modal.show();
    
    // Clean up the modal after it's hidden
    document.getElementById('facultyWarningModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
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
