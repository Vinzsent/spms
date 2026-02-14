<?php
$pageTitle = 'Maintenance Management';
include '../includes/auth.php';
include '../includes/db.php';

// Check if tables exist (basic check)
$check_table = $conn->query("SHOW TABLES LIKE 'maintenance_work_orders'");
if ($check_table->num_rows == 0) {
    $_SESSION['error'] = "Maintenance tables not found. Please run the database setup script.";
}

// Handle Form Submission: Create Work Order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_wo'])) {
    $asset_id = $_POST['asset_id'];
    $type = $_POST['maintenance_type'];
    $priority = $_POST['priority'];
    $description = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $scheduled_start = $_POST['scheduled_start'];

    $stmt = $conn->prepare("INSERT INTO maintenance_work_orders (asset_id, maintenance_type, priority, description, assigned_to, scheduled_start, status) VALUES (?, ?, ?, ?, ?, ?, 'Open')");
    $stmt->bind_param("isssss", $asset_id, $type, $priority, $description, $assigned_to, $scheduled_start);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Work Order created successfully.";
    } else {
        $_SESSION['error'] = "Error creating Work Order: " . $conn->error;
    }
    header("Location: maintenance.php");
    exit;
}

// Get Maintenance Stats
$total_wo = $conn->query("SELECT COUNT(*) as count FROM maintenance_work_orders")->fetch_assoc()['count'];
$open_wo = $conn->query("SELECT COUNT(*) as count FROM maintenance_work_orders WHERE status = 'Open'")->fetch_assoc()['count'];
$in_progress_wo = $conn->query("SELECT COUNT(*) as count FROM maintenance_work_orders WHERE status = 'In Progress'")->fetch_assoc()['count'];
$completed_wo = $conn->query("SELECT COUNT(*) as count FROM maintenance_work_orders WHERE status = 'Completed'")->fetch_assoc()['count'];

// Get Work Orders
$result = $conn->query("
    SELECT wo.*, i.item_name, i.brand 
    FROM maintenance_work_orders wo 
    JOIN property_inventory i ON wo.asset_id = i.inventory_id 
    ORDER BY wo.created_at DESC
");

// Get Assets for Dropdown
$assets = $conn->query("SELECT inventory_id, item_name, brand FROM property_inventory WHERE status = 'Active'");

include '../includes/header.php';
?>

<style>
    /* Reuse styles from Inventory.php for consistency */
    :root {
        --primary-green: #073b1d;
        --accent-orange: #EACA26;
        --accent-red: #e74c3c;
        --accent-blue: #4a90e2;
        --bg-light: #f8f9fa;
    }

    body {
        background-color: var(--bg-light);
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-green);
    }

    .table-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-open {
        background: #e3f2fd;
        color: #0d47a1;
    }

    .status-progress {
        background: #fff3e0;
        color: #e65100;
    }

    .status-completed {
        background: #e8f5e9;
        color: #1b5e20;
    }

    /* Sidebar & Layout (Simplified for brevity, assuming header.php handles much) */
    .main-content {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Matches Inventory.php header style */
    .content-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, #0a4f25 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="container-fluid mt-4">
    <div class="content-header">
        <div>
            <h1 class="mb-2">Maintenance Management</h1>
            <p class="mb-0">Track work orders, schedule maintenance, and manage asset reliability.</p>
            <a href="../dashboard.php" class="btn btn-outline-light btn-sm mt-3">
                <i class="fas fa-arrow-left me-2"></i> Previous Page
            </a>
        </div>
        <button class="btn btn-warning text-dark fw-bold" onclick="document.getElementById('newWorkOrderModal').style.display='block'">
            <i class="fas fa-plus me-2"></i>New Work Order
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-number"><?= $total_wo ?></div>
            <div class="text-muted">Total Work Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-primary"><?= $open_wo ?></div>
            <div class="text-muted">Open / Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-warning"><?= $in_progress_wo ?></div>
            <div class="text-muted">In Progress</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-success"><?= $completed_wo ?></div>
            <div class="text-muted">Completed</div>
        </div>
    </div>

    <!-- Work Orders Table -->
    <div class="table-container">
        <h4 class="mb-4"><i class="fas fa-list me-2"></i>Work Orders</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Asset</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>WO-<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['item_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($row['brand']) ?></small>
                                </td>
                                <td><?= $row['maintenance_type'] ?></td>
                                <td>
                                    <?php
                                    $p_color = match ($row['priority']) {
                                        'Critical' => 'text-danger fw-bold',
                                        'High' => 'text-danger',
                                        'Medium' => 'text-warning',
                                        default => 'text-success'
                                    };
                                    ?>
                                    <span class="<?= $p_color ?>"><?= $row['priority'] ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['assigned_to'] ?: 'Unassigned') ?></td>
                                <td>
                                    <?php
                                    $s_class = match ($row['status']) {
                                        'Open' => 'status-open',
                                        'In Progress' => 'status-progress',
                                        'Completed' => 'status-completed',
                                        default => 'bg-light'
                                    };
                                    ?>
                                    <span class="status-badge <?= $s_class ?>"><?= $row['status'] ?></span>
                                </td>
                                <td><?= $row['scheduled_start'] ? date('M d, Y', strtotime($row['scheduled_start'])) : '-' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No work orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Work Order Modal -->
<div id="newWorkOrderModal" class="modal" tabindex="-1" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-tools me-2"></i>Create New Work Order</h5>
                <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('newWorkOrderModal').style.display='none'"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Asset <span class="text-danger">*</span></label>
                            <select class="form-select" name="asset_id" required>
                                <option value="">Select Asset...</option>
                                <?php while ($asset = $assets->fetch_assoc()): ?>
                                    <option value="<?= $asset['inventory_id'] ?>"><?= htmlspecialchars($asset['item_name'] . ' (' . $asset['brand'] . ')') ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="maintenance_type" required>
                                <option value="PM">Preventive Maintenance (PM)</option>
                                <option value="CM">Corrective Maintenance (CM)</option>
                                <option value="PdM">Predictive Maintenance (PdM)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Scheduled Start</label>
                            <input type="date" class="form-control" name="scheduled_start" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="3" required placeholder="Describe the issue or task..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign To</label>
                            <input type="text" class="form-control" name="assigned_to" placeholder="Technician Name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('newWorkOrderModal').style.display='none'">Cancel</button>
                    <button type="submit" name="create_wo" class="btn btn-success">Create Work Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>