<?php
$pageTitle = 'Inventory Management';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';
$dashboard_link = ($user_type == 'Admin') ? '../admin_dashboard.php' : '../dashboard.php';

// Get inventory data
$sql = "SELECT i.*, s.supplier_name 
        FROM inventory i 
        LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
        ORDER BY i.date_created DESC";
$result = $conn->query($sql);

// Get stock movement logs
$stock_logs_sql = "SELECT sl.*, i.item_name, s.supplier_name 
                   FROM stock_logs sl 
                   LEFT JOIN inventory i ON sl.inventory_id = i.inventory_id 
                   LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
                   ORDER BY sl.date_created DESC LIMIT 50";
$stock_logs_result = $conn->query($stock_logs_sql);

// Get suppliers for dropdown
$suppliers_sql = "SELECT supplier_id, supplier_name FROM supplier WHERE status = 'Active' ORDER BY supplier_name";
$suppliers_result = $conn->query($suppliers_sql);

// Calculate statistics
$total_items = $result ? $result->num_rows : 0;
$low_stock_count = 0;
$out_of_stock_count = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['current_stock'] <= $row['reorder_level']) {
            $low_stock_count++;
        }
        if ($row['current_stock'] == 0) {
            $out_of_stock_count++;
        }
    }
    $result->data_seek(0); // Reset pointer
}

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
    --primary-green: #073b1d;
    --dark-green: #073b1d;
    --light-green: #2d8aad;
    --accent-orange: #ff6b35;
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
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
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
    background-color: rgba(255,255,255,0.1);
    color: var(--text-white);
    border-left-color: var(--accent-orange);
}

.nav-link.active {
    background-color: rgba(255,255,255,0.15);
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
    border-top: 1px solid rgba(255,255,255,0.1);
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
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.content-header h1 {
    margin: 0;
    font-weight: 700;
    font-size: 2.2rem;
}

/* Stats Cards */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--text-white);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: var(--text-white);
}

.stat-icon.items { background-color: var(--primary-green); }
.stat-icon.low-stock { background-color: var(--accent-yellow); }
.stat-icon.out-of-stock { background-color: var(--accent-red); }
.stat-icon.movements { background-color: var(--accent-blue); }

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Alert Styles */
.alert-card {
    background: linear-gradient(135deg, var(--accent-red) 0%, #c0392b 100%);
    color: var(--text-white);
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.alert-card.warning {
    background: linear-gradient(135deg, var(--accent-yellow) 0%, #e67e22 100%);
}

/* Table Styles */
.table-container {
    background: var(--text-white);
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 30px;
}

.table-header {
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
    color: var(--text-white);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h3 {
    margin: 0;
    font-weight: 600;
}

.btn-add {
    background-color: var(--accent-orange);
    border: none;
    color: var(--text-white);
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-add:hover {
    background-color: #e55a2b;
    transform: translateY(-2px);
}

/* Stock Level Indicators */
.stock-level {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stock-level.critical {
    background-color: var(--accent-red);
    color: var(--text-white);
}

.stock-level.low {
    background-color: var(--accent-yellow);
    color: var(--text-dark);
}

.stock-level.normal {
    background-color: var(--accent-blue);
    color: var(--text-white);
}

.stock-level.out {
    background-color: #6c757d;
    color: var(--text-white);
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
    
    .stats-container {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="">ASSET</h4> <h4>MANAGEMENT</h4>
        <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-item">
            <li><a href="<?= $dashboard_link ?>" class="nav-link">
                <i class="fas fa-chart-line"></i> Dashboard
            </a></li>
            <li><a href="suppliers.php" class="nav-link">
                <i class="fas fa-users"></i> Supplier List
            </a></li>
            <li><a href="supply_request.php" class="nav-link">
                <i class="fas fa-clipboard-list"></i> Supply Request
            </a></li>
            <li><a href="procurement.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i> Procurement
            </a></li>
            <li><a href="Inventory.php" class="nav-link active">
                <i class="fas fa-boxes"></i> Inventory
            </a></li>
            <li><a href="transaction_list.php" class="nav-link">
                <i class="fas fa-exchange-alt"></i> Transactions
            </a></li>
            <li><a href="../logout.php" class="nav-link logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a></li>
        </ul>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="content-header">
        <h1>Inventory Management</h1>
        <p>Track supplies, monitor stock levels, and manage inventory movements</p>
    </div>

    <!-- Low Stock Alerts -->
    <?php if ($low_stock_count > 0 || $out_of_stock_count > 0): ?>
        <div class="alert-card <?= $out_of_stock_count > 0 ? '' : 'warning' ?>">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="mb-1">Stock Alert</h5>
                    <p class="mb-0">
                        <?php if ($out_of_stock_count > 0): ?>
                            <strong><?= $out_of_stock_count ?></strong> items are out of stock
                        <?php endif; ?>
                        <?php if ($low_stock_count > 0): ?>
                            <?= $out_of_stock_count > 0 ? ' and ' : '' ?>
                            <strong><?= $low_stock_count ?></strong> items are running low
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon items">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-number"><?= $total_items ?></div>
            <div class="stat-label">Total Items</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon low-stock">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-number"><?= $low_stock_count ?></div>
            <div class="stat-label">Low Stock Items</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon out-of-stock">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-number"><?= $out_of_stock_count ?></div>
            <div class="stat-label">Out of Stock</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon movements">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-number"><?= $stock_logs_result ? $stock_logs_result->num_rows : 0 ?></div>
            <div class="stat-label">Recent Movements</div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>Inventory Items</h3>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Unit</th>
                        <th>Reorder Level</th>
                        <th>Supplier</th>
                        <th>Location</th>
                        <th>Last Updated</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $stock_level = 'normal';
                            if ($row['current_stock'] == 0) {
                                $stock_level = 'out';
                            } elseif ($row['current_stock'] <= $row['reorder_level']) {
                                $stock_level = 'critical';
                            } elseif ($row['current_stock'] <= ($row['reorder_level'] * 1.5)) {
                                $stock_level = 'low';
                            }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td>
                                    <strong><?= $row['current_stock'] ?></strong>
                                    <span class="stock-level <?= $stock_level ?> ms-2">
                                        <?= strtoupper($stock_level) ?>
                                    </span>
                                </td>
                                <td><?= $row['unit'] ?></td>
                                <td><?= $row['reorder_level'] ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td><?= htmlspecialchars($row['location'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($row['date_updated'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $stock_level == 'out' ? 'danger' : ($stock_level == 'critical' ? 'warning' : 'success') ?>">
                                        <?= ucfirst($stock_level) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" title="View Details" onclick="viewItem(<?= $row['inventory_id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" title="Stock In" onclick="stockIn(<?= $row['inventory_id'] ?>)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Stock Out" onclick="stockOut(<?= $row['inventory_id'] ?>)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No inventory items found</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                                    Add First Item
                                </button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Movement Logs -->
    <div class="table-container">
        <div class="table-header">
            <h3>Recent Stock Movements</h3>
            <button class="btn btn-add" onclick="viewAllMovements()">
                <i class="fas fa-list"></i> View All
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Previous Stock</th>
                        <th>New Stock</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stock_logs_result && $stock_logs_result->num_rows > 0): ?>
                        <?php while ($log = $stock_logs_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($log['date_created'])) ?></td>
                                <td><?= htmlspecialchars($log['item_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $log['movement_type'] == 'IN' ? 'success' : 'warning' ?>">
                                        <?= $log['movement_type'] ?>
                                    </span>
                                </td>
                                <td><?= $log['quantity'] ?></td>
                                <td><?= $log['previous_stock'] ?></td>
                                <td><?= $log['new_stock'] ?></td>
                                <td><?= htmlspecialchars($log['notes']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No stock movements recorded</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/add_inventory.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Office Supplies">Office Supplies</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Furniture">Furniture</option>
                                <option value="Tools">Tools</option>
                                <option value="Medical">Medical</option>
                                <option value="Food">Food</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" name="current_stock" class="form-control" required min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit</label>
                            <select name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <option value="pc">Piece</option>
                                <option value="box">Box</option>
                                <option value="kg">Kilogram</option>
                                <option value="liter">Liter</option>
                                <option value="set">Set</option>
                                <option value="pack">Pack</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" name="reorder_level" class="form-control" required min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                <?php 
                                if ($suppliers_result) {
                                    $suppliers_result->data_seek(0);
                                    while ($supplier = $suppliers_result->fetch_assoc()): 
                                ?>
                                    <option value="<?= $supplier['supplier_id'] ?>">
                                        <?= htmlspecialchars($supplier['supplier_name']) ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" name="unit_cost" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g., Storage Room A">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Movement Modal -->
<div class="modal fade" id="stockMovementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Stock Movement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockMovementForm" action="../actions/stock_movement.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="inventory_id" id="movement_inventory_id">
                    <input type="hidden" name="movement_type" id="movement_type">
                    
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" id="movement_item_name" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Movement Type</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success flex-fill" onclick="setMovementType('IN')">
                                <i class="fas fa-plus"></i> Stock In
                            </button>
                            <button type="button" class="btn btn-warning flex-fill" onclick="setMovementType('OUT')">
                                <i class="fas fa-minus"></i> Stock Out
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Movement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Mobile sidebar toggle
    $('.sidebar-toggle').on('click', function() {
        $('.sidebar').toggleClass('show');
    });
});

function stockIn(inventoryId) {
    $('#movement_inventory_id').val(inventoryId);
    $('#movement_type').val('IN');
    fetchItemDetails(inventoryId);
    $('#stockMovementModal').modal('show');
}

function stockOut(inventoryId) {
    $('#movement_inventory_id').val(inventoryId);
    $('#movement_type').val('OUT');
    fetchItemDetails(inventoryId);
    $('#stockMovementModal').modal('show');
}

function fetchItemDetails(inventoryId) {
    $.ajax({
        url: '../actions/get_inventory_item.php',
        type: 'GET',
        data: { inventory_id: inventoryId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#movement_item_name').val(response.item.item_name);
            } else {
                $('#movement_item_name').val('Item not found');
            }
        },
        error: function() {
            $('#movement_item_name').val('Error loading item');
        }
    });
}

function setMovementType(type) {
    $('#movement_type').val(type);
    $('.btn-success, .btn-warning').removeClass('active');
    if (type === 'IN') {
        $('.btn-success').addClass('active');
    } else {
        $('.btn-warning').addClass('active');
    }
}

function viewItem(inventoryId) {
    // Implement view item details functionality
    console.log('View item:', inventoryId);
}

function viewAllMovements() {
    // Implement view all movements functionality
    console.log('View all movements');
}
</script>

<?php include '../includes/footer.php'; ?>
