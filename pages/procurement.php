<?php
$pageTitle = 'Procurement Management';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';

// Get procurement data
$sql = "SELECT p.*, s.supplier_name 
        FROM procurement p 
        LEFT JOIN supplier s ON p.supplier_id = s.supplier_id 
        ORDER BY p.date_created DESC";
$result = $conn->query($sql);

// Get suppliers for dropdown
$suppliers_sql = "SELECT supplier_id, supplier_name FROM supplier WHERE status = 'Active' ORDER BY supplier_name";
$suppliers_result = $conn->query($suppliers_sql);

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
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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

    .stat-icon.users {
        background-color: var(--primary-green);
    }

    .stat-icon.suppliers {
        background-color: var(--accent-orange);
    }

    .stat-card.pending .stat-icon {
        background-color: var(--accent-red);
    }

    .stat-card.received .stat-icon {
        background-color: var(--accent-blue);
    }

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

    /* Table Styles */
    .table-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
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

    /* Modal Styles */
    .modal-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        border-bottom: none;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-body {
        padding: 2rem;
    }

    .form-control-plaintext {
        background-color: var(--bg-light);
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        font-weight: 500;
    }

    .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1rem 2rem;
    }

    /* Action buttons in table */
    .btn-sm {
        margin: 0 2px;
        transition: all 0.3s ease;
    }

    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        .modal-dialog {
            margin: 1rem;
        }

        .modal-body {
            padding: 1rem;
        }
    }
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="">ASSET</h4>
        <h4>MANAGEMENT</h4>
        <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-item">
            <li><a href="../dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a></li>
            <li><a href="suppliers.php" class="nav-link">
                    <i class="fas fa-users"></i> Supplier List
                </a></li>
            <li><a href="supply_request.php" class="nav-link">
                    <i class="fas fa-clipboard-list"></i> Supply Request
                </a></li>
            <li><a href="procurement.php" class="nav-link active">
                    <i class="fas fa-shopping-cart"></i> Procurement
                </a></li>
                <li><a href="inventory.php" class="nav-link">
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
        <h1>Procurement Management</h1>
        <p>Manage purchase details, invoices, and delivery status</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon users">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number"><?= $suppliers_result->num_rows ?></div>
            <div class="stat-label">Active Suppliers</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon suppliers">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-number"><?= $result->num_rows ?></div>
            <div class="stat-label">Total Purchases</div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Pending Receipts</div>
        </div>

        <div class="stat-card received">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Received Items</div>
        </div>
    </div>

    <!-- Procurement Table -->
    <div class="table-container">
        <div class="table-header">
            <h3>Procurement Records</h3>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProcurementModal">
                <i class="fas fa-plus"></i> New Purchase
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Supplier</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($row['date_created'])) ?></td>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td><?= $row['quantity'] ?> <?= $row['unit'] ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td>₱<?= number_format($row['unit_price'], 2) ?></td>
                                <td>₱<?= number_format($row['quantity'] * $row['unit_price'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] == 'Received' ? 'success' : 'warning' ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-btn" title="View Details"
                                        data-bs-toggle="modal" data-bs-target="#viewModal"
                                        data-id="<?= $row['procurement_id'] ?>"
                                        data-date="<?= date('M d, Y', strtotime($row['date_created'])) ?>"
                                        data-item="<?= htmlspecialchars($row['item_name']) ?>"
                                        data-quantity="<?= $row['quantity'] ?>"
                                        data-unit="<?= $row['unit'] ?>"
                                        data-supplier="<?= htmlspecialchars($row['supplier_name']) ?>"
                                        data-price="<?= $row['unit_price'] ?>"
                                        data-total="<?= $row['quantity'] * $row['unit_price'] ?>"
                                        data-status="<?= $row['status'] ?>"
                                        data-notes="<?= htmlspecialchars($row['notes'] ?? '') ?>">View
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning edit-btn" title="Edit"
                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="<?= $row['procurement_id'] ?>"
                                        data-item="<?= htmlspecialchars($row['item_name']) ?>"
                                        data-quantity="<?= $row['quantity'] ?>"
                                        data-unit="<?= $row['unit'] ?>"
                                        data-supplier="<?= $row['supplier_id'] ?>"
                                        data-price="<?= $row['unit_price'] ?>"
                                        data-notes="<?= htmlspecialchars($row['notes'] ?? '') ?>">Edit
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($row['status'] != 'Received'): ?>
                                        <button class="btn btn-sm btn-success approve-btn" title="Mark as Received"
                                            data-bs-toggle="modal" data-bs-target="#approveModal"
                                            data-id="<?= $row['procurement_id'] ?>"
                                            data-item="<?= htmlspecialchars($row['item_name']) ?>">Receive
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Received
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No procurement records found</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProcurementModal">
                                    Add First Purchase
                                </button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!--View Procurement Modal-->
<?php include '../modals/view_procurement.php'; ?>

<!-- Edit Modal -->
<?php include '../modals/edit_procurement.php'; ?>

<!-- Approve/Received Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark as Received</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" action="../actions/approve_procurement.php" method="POST">
                <input type="hidden" id="approve-id" name="procurement_id">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center">Are you sure you want to mark this item as received?</p>
                    <p class="text-center"><strong id="approve-item-name"></strong></p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Received Date</label>
                            <input type="date" name="received_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Received Notes (Optional)</label>
                            <textarea name="received_notes" class="form-control" rows="3" placeholder="Any additional notes about the received items..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Received</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Procurement Modal -->
<div class="modal fade" id="addProcurementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Purchase Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/add_procurement.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <input type="text" name="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                <?php while ($supplier = $suppliers_result->fetch_assoc()): ?>
                                    <option value="<?= $supplier['supplier_id'] ?>">
                                        <?= htmlspecialchars($supplier['supplier_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
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
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit Price</label>
                            <input type="number" name="unit_price" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice</label>
                            <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Delivery Receipt</label>
                            <input type="file" name="delivery_receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Mobile sidebar toggle
        $('.sidebar-toggle').on('click', function() {
            $('.sidebar').toggleClass('show');
        });

        // Auto-calculate total
        $('input[name="quantity"], input[name="unit_price"]').on('input', function() {
            const quantity = parseFloat($('input[name="quantity"]').val()) || 0;
            const unitPrice = parseFloat($('input[name="unit_price"]').val()) || 0;
            const total = quantity * unitPrice;
            // You can add a total display field if needed
        });

        // View Modal functionality
        $('.view-btn').on('click', function() {
            console.log('View button clicked!');
            const button = $(this);
            
            // Store the data in a global variable for the modal shown event
            window.currentViewData = {
                date: button.data('date'),
                item: button.data('item'),
                quantity: button.data('quantity'),
                unit: button.data('unit'),
                supplier: button.data('supplier'),
                price: button.data('price'),
                total: button.data('total'),
                status: button.data('status'),
                notes: button.data('notes')
            };
            
            // Debug: Log the data attributes
            console.log('View button clicked with data:', window.currentViewData);
        });

        // Modal shown event for debugging and populating data
        $('#viewModal').on('shown.bs.modal', function () {
            console.log('View modal is now visible');
            console.log('Modal elements found:', {
                date: $('#view-date').length,
                item: $('#view-item').length,
                quantity: $('#view-quantity').length,
                supplier: $('#view-supplier').length,
                price: $('#view-price').length,
                total: $('#view-total').length,
                status: $('#view-status').length,
                notes: $('#view-notes').length
            });
            
            // Populate the modal with stored data
            if (window.currentViewData) {
                console.log('Populating modal with data:', window.currentViewData);
                
                $('#view-date').text(window.currentViewData.date || 'N/A');
                $('#view-item').text(window.currentViewData.item || 'N/A');
                $('#view-quantity').text((window.currentViewData.quantity || '0') + ' ' + (window.currentViewData.unit || ''));
                $('#view-supplier').text(window.currentViewData.supplier || 'N/A');
                $('#view-price').text('₱' + (parseFloat(window.currentViewData.price || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2
                })));
                $('#view-total').text('₱' + (parseFloat(window.currentViewData.total || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2
                })));

                // Status with badge styling
                const status = window.currentViewData.status || 'Pending';
                const statusBadge = status === 'Received' ?
                    '<span class="badge bg-success">' + status + '</span>' :
                    '<span class="badge bg-warning">' + status + '</span>';
                $('#view-status').html(statusBadge);

                $('#view-notes').text(window.currentViewData.notes || 'No notes available');
                
                // Debug: Log the populated elements
                console.log('Modal elements populated:', {
                    date: $('#view-date').text(),
                    item: $('#view-item').text(),
                    quantity: $('#view-quantity').text(),
                    supplier: $('#view-supplier').text(),
                    price: $('#view-price').text(),
                    total: $('#view-total').text(),
                    status: $('#view-status').html(),
                    notes: $('#view-notes').text()
                });
            } else {
                console.log('No data available for modal population');
            }
        });

        // Edit Modal functionality
        $('.edit-btn').on('click', function() {
            const button = $(this);
            $('#edit-id').val(button.data('id'));
            $('#edit-item').val(button.data('item'));
            $('#edit-quantity').val(button.data('quantity'));
            $('#edit-unit').val(button.data('unit'));
            $('#edit-supplier').val(button.data('supplier'));
            $('#edit-price').val(button.data('price'));
            $('#edit-notes').val(button.data('notes'));
            
            // Populate supplier options if not already done
            if ($('#edit-supplier option').length <= 1) {
                const addModalSupplier = $('select[name="supplier_id"] option').clone();
                $('#edit-supplier').html(addModalSupplier);
            }
        });

        // Approve Modal functionality
        $('.approve-btn').on('click', function() {
            const button = $(this);
            $('#approve-id').val(button.data('id'));
            $('#approve-item-name').text(button.data('item'));
        });

        // Auto-calculate total in edit modal
        $('#edit-quantity, #edit-price').on('input', function() {
            const quantity = parseFloat($('#edit-quantity').val()) || 0;
            const unitPrice = parseFloat($('#edit-price').val()) || 0;
            const total = quantity * unitPrice;
            // You can add a total display field if needed
        });
    });
</script>

<?php include '../includes/footer.php'; ?>