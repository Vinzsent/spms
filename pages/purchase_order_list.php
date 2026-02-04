<?php
$pageTitle = 'Purchase Order List';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';

// Fetch purchase order records with user information
$po_query = "
    SELECT 
        p.po_id,
        p.po_number,
        p.po_date,
        p.supplier_name,
        p.supplier_address,
        p.total_amount,
        p.status,
        p.notes,
        p.created_at,
        CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
        COUNT(poi.poi_id) as item_count
    FROM purchase_orders p
    LEFT JOIN user u ON p.created_by = u.id
    LEFT JOIN purchase_order_items poi ON p.po_id = poi.po_id
    GROUP BY p.po_id
    ORDER BY p.created_at DESC
";

$po_result = $conn->query($po_query);
?>

<style>
    :root {
        --primary-green: #073b1d;
        --dark-green: #073b1d;
        --light-green: #2d8aad;
        --accent-orange: #EACA26;
        --accent-blue: #4a90e2;
        --accent-green-approved: #28a745;
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

    .sidebar-header h4 {
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

    /* List Container */
    .list-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .list-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .list-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background-color: var(--accent-orange);
        color: var(--text-white);
    }

    .btn-primary:hover {
        background-color: #e8690b;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: var(--text-white);
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }

    /* Table Styles */
    .po-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .po-table th {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid var(--primary-green);
    }

    .po-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }

    .po-table tbody tr:hover {
        background-color: rgba(7, 59, 29, 0.05);
    }

    /* Status Badges */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-draft {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }

    .status-pending {
        background: linear-gradient(135deg, var(--accent-orange), #e8690b);
        color: white;
    }

    .status-approved {
        background: linear-gradient(135deg, var(--accent-green-approved), #1e7e34);
        color: white;
    }

    .status-rejected {
        background: linear-gradient(135deg, var(--accent-red), #c82333);
        color: white;
    }

    .status-completed {
        background: linear-gradient(135deg, var(--accent-blue), #357abd);
        color: white;
    }

    .status-cancelled {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    /* Action Buttons in Table */
    .table-actions {
        display: flex;
        gap: 8px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
        border-radius: 4px;
    }

    .btn-info {
        background-color: var(--accent-blue);
        color: white;
    }

    .btn-info:hover {
        background-color: #357abd;
    }

    .btn-warning {
        background-color: var(--accent-orange);
        color: white;
    }

    .btn-warning:hover {
        background-color: #e8690b;
    }

    .btn-danger {
        background-color: var(--accent-red);
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        margin-bottom: 10px;
        color: var(--text-dark);
    }

    /* Mobile Responsiveness */
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
            padding: 15px;
        }

        .list-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .po-table {
            font-size: 0.9rem;
        }

        .po-table th,
        .po-table td {
            padding: 10px 8px;
        }

        .table-actions {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4>DARTS</h4>
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
            <li><a href="procurement.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Procurement
                </a></li>
            <li><a href="canvas_form.php" class="nav-link">
                    <i class="fas fa-clipboard-list"></i> Canvass Form
                </a></li>
            <li><a href="canvass_form_list.php" class="nav-link">
                    <i class="fas fa-list"></i> Canvass List
                </a></li>
            <li><a href="purchase_order.php" class="nav-link">
                    <i class="fas fa-shopping-basket"></i> Purchase Order
                </a></li>
            <li><a href="purchase_order_list.php" class="nav-link active">
                    <i class="fas fa-file-invoice"></i> Purchase Order List
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
        <h1>Purchase Order List</h1>
        <p>View and manage all purchase order records</p>
    </div>

    <!-- Purchase Order List -->
    <div class="list-container">
        <div class="list-header">
            <h2 class="list-title">All Purchase Order Records</h2>
            <div class="action-buttons">
                <a href="../actions/export_purchase_orders_excel.php" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
                <a href="purchase_order.php" class="btn btn-primary text-dark">
                    <i class="fas fa-plus"></i> New Purchase Order
                </a>
            </div>
        </div>

        <?php if ($po_result && $po_result->num_rows > 0): ?>
            <table class="po-table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Total Amount</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($po = $po_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($po['po_number']) ?></strong>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($po['po_date'])) ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($po['supplier_name']) ?></strong>
                                    <?php if ($po['supplier_address']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($po['supplier_address']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <strong>₱<?= number_format($po['total_amount'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info" style="background-color: var(--primary-green); color: white; font-weight: bold; font-size: 12px; padding: 4px 8px; border-radius: 4px;"><?= $po['item_count'] ?> items</span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower($po['status']) ?>">
                                    <?= htmlspecialchars($po['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($po['created_by_name'] ?? 'Unknown') ?>
                            </td>
                            <td>
                                <?= date('M d, Y g:i A', strtotime($po['created_at'])) ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-info btn-sm" onclick="viewPurchaseOrder(<?= $po['po_id'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-warning btn-sm" onclick="editPurchaseOrder(<?= $po['po_id'] ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deletePurchaseOrder(<?= $po['po_id'] ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-invoice"></i>
                <h3>No Purchase Order Records Found</h3>
                <p>Start by creating your first purchase order.</p>
                <a href="purchase_order.php" class="btn btn-primary text-dark">
                    <i class="fas fa-plus"></i> Create New Purchase Order
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Purchase Order Modal -->
<div class="modal fade" id="viewPurchaseOrderModal" tabindex="-1" aria-labelledby="viewPurchaseOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%); color: white;">
                <h5 class="modal-title" id="viewPurchaseOrderLabel">Purchase Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body" id="purchaseOrderDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printPurchaseOrderDetails()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // View purchase order details
    function viewPurchaseOrder(poId) {
        fetch(`../actions/get_purchase_order_details.php?id=${poId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPurchaseOrderDetails(data.purchase_order, data.items);
                    $('#viewPurchaseOrderModal').modal('show');
                } else {
                    alert('Error loading purchase order details: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
    }

    // Display purchase order details in modal
    function displayPurchaseOrderDetails(po, items) {
        let itemsHtml = '';
        let grandTotal = 0;

        items.forEach(item => {
            itemsHtml += `
                <tr>
                    <td>${item.item_number}</td>
                    <td>${item.item_description}</td>
                    <td>${item.quantity}</td>
                    <td>₱${parseFloat(item.unit_cost).toFixed(2)}</td>
                    <td>₱${parseFloat(item.line_total).toFixed(2)}</td>
                </tr>
            `;
            grandTotal += parseFloat(item.line_total);
        });

        const content = `
            <div class="purchase-order-details">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Purchase Order Information</h4>
                        <p><strong>PO Number:</strong> ${po.po_number}</p>
                        <p><strong>Date:</strong> ${new Date(po.po_date).toLocaleDateString()}</p>
                        <p><strong>Status:</strong> <span class="status-badge status-${po.status.toLowerCase()}">${po.status}</span></p>
                        <p><strong>Payment Method:</strong> ${po.payment_method || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h4>Supplier Information</h4>
                        <p><strong>Supplier:</strong> ${po.supplier_name}</p>
                        <p><strong>Address:</strong> ${po.supplier_address || 'N/A'}</p>
                        <p><strong>Total Amount:</strong> ₱${parseFloat(po.total_amount).toFixed(2)}</p>
                        <p><strong>Created By:</strong> ${po.created_by_name || 'Unknown'}</p>
                    </div>
                </div>
                
                <h4>Items</h4>
                <table class="table table-bordered">
                    <thead style="background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%); color: white;">
                        <tr>
                            <th>#</th>
                            <th>Item Description</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                        <tr style="background-color: var(--primary-green); color: white; font-weight: bold;">
                            <td colspan="4" style="text-align: right;">GRAND TOTAL:</td>
                            <td>₱${grandTotal.toFixed(2)}</td>
                        </tr>
                    </tbody>
                </table>
                
                ${po.notes ? `<div class="mt-3"><h5>Notes:</h5><p>${po.notes}</p></div>` : ''}
            </div>
        `;

        document.getElementById('purchaseOrderDetailsContent').innerHTML = content;
    }

    // Edit purchase order
    function editPurchaseOrder(poId) {
        window.location.href = `purchase_order.php?edit=${poId}`;
    }

    // Delete purchase order
    function deletePurchaseOrder(poId) {
        if (confirm('Are you sure you want to delete this purchase order? This action cannot be undone.')) {
            fetch('../actions/delete_purchase_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        po_id: poId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Purchase order deleted successfully');
                        location.reload();
                    } else {
                        alert('Error deleting purchase order: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
        }
    }

    // Print purchase order details
    function printPurchaseOrderDetails() {
        const printContent = document.getElementById('purchaseOrderDetailsContent').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Purchase Order Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; }
                        .status-draft { background-color: #6c757d; color: white; }
                        .status-pending { background-color: #fd7e14; color: white; }
                        .status-approved { background-color: #28a745; color: white; }
                        .status-rejected { background-color: #e74c3c; color: white; }
                        .status-completed { background-color: #4a90e2; color: white; }
                        .status-cancelled { background-color: #dc3545; color: white; }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
</script>

<?php include '../includes/footer.php'; ?>