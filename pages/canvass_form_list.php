<?php
$pageTitle = 'Canvass List';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';

// Fetch canvass records with user information
$canvass_query = "
    SELECT 
        c.canvass_id,
        c.canvass_date,
        c.total_amount,
        c.status,
        c.notes,
        c.created_at,
        CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
        COUNT(ci.canvass_item_id) as item_count
    FROM canvass c
    LEFT JOIN user u ON c.created_by = u.id
    LEFT JOIN canvass_items ci ON c.canvass_id = ci.canvass_id
    GROUP BY c.canvass_id
    ORDER BY c.created_at DESC
";

$canvass_result = $conn->query($canvass_query);
?>

<style>
    :root {
        --primary-green: #073b1d;
        --dark-green: #073b1d;
        --light-green: #2d8aad;
        --accent-orange: #fd7e14;
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
    .canvass-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .canvass-table th {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid var(--primary-green);
    }

    .canvass-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }

    .canvass-table tbody tr:hover {
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

    .status-completed {
        background: linear-gradient(135deg, var(--accent-blue), #357abd);
        color: white;
    }

    .status-approved {
        background: linear-gradient(135deg, var(--accent-green-approved), #1e7e34);
        color: white;
    }

    .status-cancelled {
        background: linear-gradient(135deg, var(--accent-red), #c82333);
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

        .canvass-table {
            font-size: 0.9rem;
        }

        .canvass-table th,
        .canvass-table td {
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
            <li><a href="canvass_form_list.php" class="nav-link active">
                    <i class="fas fa-list"></i> Canvass List
                </a></li>
            <li><a href="purchase_order.php" class="nav-link">
                    <i class="fas fa-shopping-basket"></i> Purchase Order
                </a></li>
            <li><a href="purchase_order_list.php" class="nav-link">
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
        <h1>Canvass List</h1>
        <p>View and manage all canvass records</p>
    </div>

    <!-- Canvass List -->
    <div class="list-container">
        <div class="list-header">
            <h2 class="list-title">All Canvass Records</h2>
            <div class="action-buttons">
                <a href="canvas_form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Canvass
                </a>
            </div>
        </div>

        <?php if ($canvass_result && $canvass_result->num_rows > 0): ?>
            <table class="canvass-table">
                <thead>
                    <tr>
                        <th>Canvass #</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($canvass = $canvass_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($canvass['canvass_id']) ?></strong>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($canvass['canvass_date'])) ?>
                            </td>
                            <td>
                                <strong>₱<?= number_format($canvass['total_amount'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info" style="background-color: var(--primary-green); color: white; font-weight: bold; font-size: 14px; padding: 5px 10px; border-radius: 4px; text-transform: uppercase; text-shadow: none; box-shadow: none; transition: none; text-decoration: none; text-align: center; display: inline-block; margin: 0; line-height: 1.2; letter-spacing: normal; word-spacing: normal; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; box-sizing: border-box;"><?= $canvass['item_count'] ?> items</span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower($canvass['status']) ?>">
                                    <?= htmlspecialchars($canvass['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= htmlspecialchars($canvass['created_by_name'] ?? 'Unknown') ?>
                            </td>
                            <td>
                                <?= date('M d, Y g:i A', strtotime($canvass['created_at'])) ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-info btn-sm" onclick="viewCanvass(<?= $canvass['canvass_id'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-warning btn-sm" onclick="editCanvass(<?= $canvass['canvass_id'] ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteCanvass(<?= $canvass['canvass_id'] ?>)">
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
                <i class="fas fa-clipboard-list"></i>
                <h3>No Canvass Records Found</h3>
                <p>Start by creating your first canvass form.</p>
                <a href="canvas_form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Canvass
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Canvass Modal -->
<div class="modal fade" id="viewCanvassModal" tabindex="-1" aria-labelledby="viewCanvassLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%); color: white;">
        <h5 class="modal-title" id="viewCanvassLabel">Canvass Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
      </div>
      <div class="modal-body" id="canvassDetailsContent">
        <!-- Content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="printCanvassDetails()">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>
  </div>
</div>


<script>
    // View canvass details
    function viewCanvass(canvassId) {
        fetch(`../actions/get_canvass_details.php?id=${canvassId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayCanvassDetails(data.canvass, data.items);
                    $('#viewCanvassModal').modal('show');
                } else {
                    alert('Error loading canvass details: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
    }

    // Display canvass details in modal
    function displayCanvassDetails(canvass, items) {
        let itemsHtml = '';
        let grandTotal = 0;

        items.forEach(item => {
            itemsHtml += `
                <tr>
                    <td>${item.supplier_name}</td>
                    <td>${item.item_description}</td>
                    <td>${item.quantity}</td>
                    <td>₱${parseFloat(item.unit_cost).toFixed(2)}</td>
                    <td>₱${parseFloat(item.total_cost).toFixed(2)}</td>
                </tr>
            `;
            grandTotal += parseFloat(item.total_cost);
        });

        const content = `
            <div class="canvass-details">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Canvass Information</h4>
                        <p><strong>Canvass Number:</strong> ${canvass.canvass_id}</p>
                        <p><strong>Date:</strong> ${new Date(canvass.canvass_date).toLocaleDateString()}</p>
                        <p><strong>Status:</strong> <span class="status-badge status-${canvass.status.toLowerCase()}">${canvass.status}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Amount:</strong> ₱${parseFloat(canvass.total_amount).toFixed(2)}</p>
                        <p><strong>Created By:</strong> ${canvass.created_by_name || 'Unknown'}</p>
                        <p><strong>Created At:</strong> ${new Date(canvass.created_at).toLocaleString()}</p>
                    </div>
                </div>
                
                <h4>Items</h4>
                <table class="table table-bordered">
                    <thead style="background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%); color: white;">
                        <tr>
                            <th>Supplier</th>
                            <th>Item Description</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
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
                
                ${canvass.notes ? `<div class="mt-3"><h5>Notes:</h5><p>${canvass.notes}</p></div>` : ''}
            </div>
        `;

        document.getElementById('canvassDetailsContent').innerHTML = content;
    }

    // Edit canvass
    function editCanvass(canvassId) {
        window.location.href = `canvas_form.php?edit=${canvassId}`;
    }

    // Delete canvass
    function deleteCanvass(canvassId) {
        if (confirm('Are you sure you want to delete this canvass? This action cannot be undone.')) {
            fetch('../actions/delete_canvass.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ canvass_id: canvassId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Canvass deleted successfully');
                    location.reload();
                } else {
                    alert('Error deleting canvass: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    }

    // Print canvass details
    function printCanvassDetails() {
        const printContent = document.getElementById('canvassDetailsContent').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Canvass Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; }
                        .status-draft { background-color: #6c757d; color: white; }
                        .status-completed { background-color: #4a90e2; color: white; }
                        .status-approved { background-color: #28a745; color: white; }
                        .status-cancelled { background-color: #e74c3c; color: white; }
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
