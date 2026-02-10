<?php

$pageTitle = 'Received Items';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$raw_user_type = $_SESSION['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));
$user_id = $_SESSION['user']['id'] ?? 0;

// Fetch purchase order records that are Approved or Received
// Specifically those created by Purchasing Officer for transparency
$query = "
    SELECT 
        p.po_id,
        p.po_number,
        p.po_date,
        p.supplier_name,
        p.total_amount,
        p.status,
        p.received_date,
        CONCAT(u_creator.first_name, ' ', u_creator.last_name) as created_by_name,
        CONCAT(u_receiver.first_name, ' ', u_receiver.last_name) as received_by_name,
        COUNT(poi.poi_id) as item_count
    FROM purchase_orders p
    LEFT JOIN user u_creator ON p.created_by = u_creator.id
    LEFT JOIN user u_receiver ON p.received_by = u_receiver.id
    LEFT JOIN purchase_order_items poi ON p.po_id = poi.po_id
    GROUP BY p.po_id
    ORDER BY CASE 
        WHEN p.status = 'Approved' THEN 0 
        WHEN p.status = 'Pending' THEN 1 
        WHEN p.status = 'Draft' THEN 2 
        ELSE 3 
    END, p.created_at DESC
";

$result = $conn->query($query);
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

    .main-content {
        margin-left: 280px;
        padding: 20px;
        min-height: 100vh;
    }

    .content-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .list-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .list-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .po-table {
        width: 100%;
        border-collapse: collapse;
    }

    .po-table th {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 15px 12px;
        text-align: left;
    }

    .po-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #e9ecef;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
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

    .status-received {
        background: linear-gradient(135deg, var(--accent-blue), #357abd);
        color: white;
    }

    .btn-received {
        background-color: var(--accent-orange);
        color: var(--text-dark);
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-received:hover {
        background-color: #d4b422;
        transform: translateY(-2px);
    }

    /* Sidebar Styles (matching purchase_order_list.php) */
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

    .sidebar-nav {
        padding: 20px 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        color: var(--text-white);
        text-decoration: none;
        transition: 0.3s;
        border-left: 4px solid transparent;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        border-left-color: var(--accent-orange);
    }

    .nav-link.active {
        background: rgba(255, 255, 255, 0.15);
        border-left-color: var(--accent-orange);
        font-weight: 600;
    }

    .nav-link i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .main-content {
            margin-left: 0;
        }
    }
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4>DARTS</h4>
        <div style="font-size: 0.9rem; opacity: 0.9;">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name']) ?></div>
    </div>
    <nav class="sidebar-nav">
        <a href="../dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="received_items.php" class="nav-link active"><i class="fas fa-box-open"></i> Received Items</a>
        <a href="purchase_order_list.php" class="nav-link"><i class="fas fa-file-invoice"></i> Purchase Order List</a>
        <a href="Inventory.php" class="nav-link"><i class="fas fa-box"></i> Supply Inventory</a>
        <a href="property_inventory.php" class="nav-link"><i class="fas fa-box"></i> Property Inventory</a>
        <a href="../logout.php" class="nav-link" style="color: var(--accent-red); margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="content-header">
        <h1>Received Items Tracking</h1>
        <p>Manage and track the receipt of approved purchase orders</p>
    </div>

    <div class="list-container">
        <div class="list-header">
            <h2 style="margin: 0; font-size: 1.5rem;">Purchase Records</h2>
        </div>

        <table class="po-table">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Amount</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Marked Received By</th>
                    <th>Received Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['po_number']) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($row['po_date'])) ?></td>
                            <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                            <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($row['created_by_name']) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['received_by_name'] ?? '---') ?></td>
                            <td><?= $row['received_date'] ? date('M d, Y g:i A', strtotime($row['received_date'])) : '---' ?></td>
                            <td>
                                <?php if ($row['status'] !== 'Received' && (in_array($user_type, ['propertycustodian', 'supplyincharge', 'purchasingofficer', 'admin']))): ?>
                                    <button class="btn-received" onclick="markAsReceived(<?= $row['po_id'] ?>)">
                                        <i class="fas fa-check-circle"></i> Mark Received
                                    </button>
                                <?php elseif ($row['status'] === 'Received'): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Already Received</span>
                                <?php else: ?>
                                    <span class="text-muted">No Action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p>No approved or received purchase orders found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function markAsReceived(poId) {
        if (!confirm('Are you sure you want to mark this item as received?')) return;

        fetch('../actions/mark_as_received.php', {
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
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    }
</script>

<?php include '../includes/footer.php'; ?>