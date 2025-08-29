<?php

$pageTitle = 'Issuance - Noted Stage';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Check if user is Immediate Head
$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
if (strtolower($user_type) !== 'immediate head') {
    header("Location: issuance.php");
    exit();
}

// Get user information
$user_name = '';
if (isset($_SESSION['name'])) {
    $user_name = $_SESSION['name'];
} elseif (isset($_SESSION['user']['name'])) {
    $user_name = $_SESSION['user']['name'];
} elseif (isset($_SESSION['user']['first_name']) && isset($_SESSION['user']['last_name'])) {
    $user_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
} else {
    $user_name = 'Unknown User';
}

// Get supply requests that need to be noted (no noted_by yet)
$sql = "SELECT sr.*, 
        sr.noted_by, sr.checked_by, sr.verified_by, sr.issued_by, sr.approved_by,
        sr.noted_date, sr.checked_date, sr.verified_date, sr.issued_date, sr.approved_date
        FROM supply_request sr 
        WHERE sr.noted_by IS NULL
        ORDER BY sr.date_requested DESC";
$result = $conn->query($sql);

$total_requests = $result->num_rows;
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
    background: var(--primary-bg);
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
    color: #dc3545;
    margin-top: 2rem;
}

.nav-link.logout:hover {
    background: rgba(255, 255, 255, 0.15);
    color: var(--text-light);
    transform: translateX(8px) scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Main Content */
.main-content {
    margin-left: 280px;
    min-height: 100vh;
    background: var(--light-bg);
}

/* Header */
.page-header {
    background: linear-gradient(135deg, #17a2b8, #138496);
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
    background: linear-gradient(135deg, #17a2b8, #138496);
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
    background: rgba(23, 162, 184, 0.05);
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

.btn-info-modern {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

.btn-success-modern {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
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

/* Enhanced Hover Effects */
.hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.hover-lift:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.hover-scale {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.hover-scale:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.nav-link {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.5s;
}

.nav-link:hover::before {
    left: 100%;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.15);
    color: var(--text-light);
    transform: translateX(8px) scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.nav-link.active {
    background: rgba(255, 255, 255, 0.25);
    border-left: 4px solid var(--secondary-color);
    box-shadow: inset 0 0 20px rgba(255, 107, 53, 0.1);
}

.nav-link.active:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateX(5px) scale(1.01);
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
</style>

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

<!-- Main Content -->
<div class="main-content" >
    <!-- Page Header -->
    <div class="page-header" style="background: #073b1d;">
        <div class="d-flex justify-content-between align-items-center">
            <div >
                <h1 class="page-title">Noted Stage - Immediate Head</h1>
                <p class="page-subtitle">Review and acknowledge supply requests</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stats-card hover-lift">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <div class="stats-number"><?= $total_requests ?></div>
                    <div class="stats-label">Pending Noted</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card hover-lift">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #28a745, #1e7e34); color: white;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">0</div>
                    <div class="stats-label">Noted Today</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card hover-lift">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ffc107, #ffb300); color: #000;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number">0</div>
                    <div class="stats-label">Average Time</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supply Requests Section -->
    <div class="content-section">
        <div class="section-header" style="background:#073b1d;">
            <h2 class="section-title">
                <i class="fas fa-sticky-note me-2"></i>
                Requests Pending Noted
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
                                <th><i class="fas fa-hashtag me-2"></i>Quantity</th>
                                <th><i class="fas fa-dollar-sign me-2"></i>Total Cost</th>
                                <th><i class="fas fa-user me-2"></i>Requested By</th>
                                <th><i class="fas fa-tasks me-2"></i>Status</th>
                                <th><i class="fas fa-cogs me-2"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="table-row-hover">
                                    <td>
                                        <strong><?= date('M d, Y', strtotime($row['date_requested'])) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= date('h:i A', strtotime($row['date_requested'])) ?></small>
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
                                        <span class="status-badge status-pending">
                                            Pending
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-info-modern btn-action hover-scale" 
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
                                                    data-category="<?= htmlspecialchars($row['category']) ?>">
                                                <i class="fas fa-eye me-1"></i>View
                                            </button>
                                            <button class="btn btn-success-modern btn-action hover-scale" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#noteRequestModal"
                                                    data-request-id="<?= $row['request_id'] ?>">
                                                <i class="fas fa-sticky-note me-1"></i>Mark as Noted
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
                    <i class="fas fa-sticky-note"></i>
                    <h5>No Requests Pending Noted</h5>
                    <p>All supply requests have been acknowledged or are in other stages.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;">
                <h5 class="modal-title" id="viewRequestModalLabel">
                    <i class="fas fa-eye me-2"></i>Request Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-calendar me-2"></i>Request Information</h6>
                        <p><strong>Date Requested:</strong> <span id="viewDate"></span></p>
                        <p><strong>Department:</strong> <span id="viewDepartment"></span></p>
                        <p><strong>Purpose:</strong> <span id="viewPurpose"></span></p>
                        <p><strong>Category:</strong> <span id="viewCategory"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-box me-2"></i>Item Details</h6>
                        <p><strong>Description:</strong> <span id="viewDescription"></span></p>
                        <p><strong>Quantity:</strong> <span id="viewQuantity"></span></p>
                        <p><strong>Total Cost:</strong> <span id="viewCost"></span></p>
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

<!-- Note Request Modal -->
<div class="modal fade" id="noteRequestModal" tabindex="-1" aria-labelledby="noteRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color: white;">
                <h5 class="modal-title" id="noteRequestModalLabel">
                    <i class="fas fa-sticky-note me-2"></i>Mark as Noted
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="noteRequestForm" action="../actions/update_issuance_status.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="noteRequestId">
                    <input type="hidden" name="status_action" value="noted">
                    <input type="hidden" name="action_by" value="<?= htmlspecialchars($user_name) ?>">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Confirmation:</strong> Are you sure you want to mark this request as "Noted"? This will acknowledge the request and move it to the next stage.
                    </div>
                    
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks (Optional):</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" 
                                  placeholder="Add any additional remarks or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="noteRequestBtn">
                        <i class="fas fa-sticky-note me-1"></i>Mark as Noted
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
        
        $('#viewDate').text(new Date(data.date).toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric'
        }));
        $('#viewDepartment').text(data.department);
        $('#viewPurpose').text(data.purpose);
        $('#viewCategory').text(data.category);
        $('#viewDescription').text(data.description);
        $('#viewQuantity').text(data.quantity + ' ' + data.unit);
        $('#viewCost').text('₱' + parseFloat(data.cost).toLocaleString(undefined, {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        }));
    });
    
    // Note Request Modal
    $(document).on('click', '[data-bs-target="#noteRequestModal"]', function() {
        const requestId = $(this).data('request-id');
        $('#noteRequestId').val(requestId);
    });
    
    // Form submission
    $('#noteRequestForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#noteRequestBtn');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    $('#noteRequestModal').modal('hide');
                    alert('Request marked as noted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.log('AJAX Error:', xhr.responseText);
                alert('An error occurred while processing the request. Please try again.');
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?> 