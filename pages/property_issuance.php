<?php
$pageTitle = 'Property Issuance';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';
$dashboard_link = ($user_type == 'Admin') ? '../dashboard.php' : '../dashboard.php';

// Get inventory items for dropdown
$inventory_sql = "SELECT i.*, s.supplier_name 
                  FROM inventory i 
                  LEFT JOIN supplier s ON i.supplier_id = s.supplier_id 
                  WHERE i.current_stock > 0 
                  ORDER BY i.item_name ASC";
$inventory_result = $conn->query($inventory_sql);

// Get departments for dropdown
$departments = [
    'Academic Affairs',
    'Administration',
    'Finance',
    'Human Resources',
    'IT Department',
    'Maintenance',
    'Security',
    'Student Affairs'
];

// Store session messages for modal display
$session_message = '';
$session_error = '';
if (isset($_SESSION['message'])) {
    $session_message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $session_error = $_SESSION['error'];
    unset($_SESSION['error']);
}
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

    /* Form Container */
    .issuance-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .issuance-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 20px 30px;
        text-align: center;
        position: relative;
    }

    .issuance-title {
        margin: 0;
        font-weight: 700;
        font-size: 1.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-content {
        padding: 30px;
    }

    /* Form Header Info */
    .form-header-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--bg-light);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(7, 59, 29, 0.1);
    }

    /* Items Table */
    .items-section {
        margin: 30px 0;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary-green);
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .items-table th {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 15px 12px;
        text-align: center;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
        text-align: center;
        vertical-align: middle;
    }

    .items-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .items-table input,
    .items-table select {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .items-table input:focus,
    .items-table select:focus {
        outline: none;
        border-color: var(--primary-green);
    }

    /* Row Management Buttons */
    .row-management {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .btn-add-row,
    .btn-remove-row {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-add-row {
        background-color: var(--accent-green-approved);
        color: var(--text-white);
    }

    .btn-add-row:hover {
        background-color: var(--primary-green);
        transform: translateY(-2px);
    }

    .btn-remove-row {
        background-color: var(--accent-red);
        color: var(--text-white);
    }

    .btn-remove-row:hover {
        background-color: var(--primary-green);
        transform: translateY(-2px);
    }

    /* Signatures Section */
    .signatures-section {
        margin-top: 40px;
        padding-top: 30px;
        border-top: 2px solid var(--bg-light);
    }

    .signatures-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 40px;
        margin-top: 30px;
    }

    .signature-box {
        text-align: center;
        padding: 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background-color: #fafafa;
    }

    .signature-line {
        height: 60px;
        border-bottom: 2px solid var(--text-dark);
        margin-bottom: 15px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 5px;
    }

    .signature-title {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .signature-subtitle {
        font-size: 0.8rem;
        color: #666;
        font-style: italic;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 40px;
        justify-content: center;
    }

    .btn-action {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 140px;
        justify-content: center;
    }

    .btn-primary {
        background-color: var(--primary-green);
        color: var(--text-white);
    }

    .btn-primary:hover {
        background-color: var(--dark-green);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(7, 59, 29, 0.3);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: var(--text-white);
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }

    .btn-success {
        background-color: var(--accent-green-approved);
        color: var(--text-white);
    }

    .btn-success:hover {
        background-color: #218838;
        transform: translateY(-2px);
    }

    /* Print Styles */
    @media print {
        @page {
            size: A4 portrait;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif !important;
            font-size: 12px !important;
            color: black !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        .sidebar,
        .action-buttons,
        .row-management,
        .content-header,
        .issuance-header {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .issuance-container {
            box-shadow: none !important;
            margin: 0 !important;
            background: white !important;
        }

        .form-content {
            padding: 0 !important;
        }

        .print-container {
            display: block !important;
            width: 100% !important;
            margin: 0 auto !important;
        }

        .print-title {
            text-align: center !important;
            margin: 0 0 10px 0 !important;
            text-transform: uppercase !important;
            font-size: 14px !important;
            font-weight: bold !important;
        }

        .print-info {
            margin-bottom: 10px !important;
            font-size: 12px !important;
        }

        .print-info p {
            margin: 3px 0 !important;
        }

        .print-table {
            border-collapse: collapse !important;
            width: 100% !important;
            margin-bottom: 20px !important;
            font-size: 12px !important;
        }

        .print-table th,
        .print-table td {
            border: 1px solid black !important;
            padding: 5px !important;
            text-align: center !important;
        }

        .print-table th {
            background: #f2f2f2 !important;
            font-weight: bold !important;
        }

        .print-signatures {
            margin-top: 20px !important;
            font-size: 12px !important;
        }

        .print-sign-section {
            margin-bottom: 30px !important;
        }

        .print-sign-name {
            text-transform: uppercase !important;
            font-weight: bold !important;
            text-decoration: underline !important;
            display: block !important;
            margin-top: 40px !important;
        }

        .print-role {
            display: block !important;
            margin-top: -2px !important;
        }

        .print-received {
            margin-top: 40px !important;
        }

        .print-line {
            margin-top: 40px !important;
            border-top: 1px solid black !important;
            width: 200px !important;
        }

        /* Hide regular form elements */
        .form-header-info,
        .items-section,
        .signatures-section {
            display: none !important;
        }
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

        .form-header-info {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .signatures-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .action-buttons {
            flex-direction: column;
            align-items: center;
        }

        .items-table {
            font-size: 0.8rem;
        }

        .items-table th,
        .items-table td {
            padding: 8px 4px;
        }
    }
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>DARTS</h3>
        <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
    </div>

    <nav class="sidebar-nav">
            <ul class="nav-item">
            <li><a href="<?= $dashboard_link ?>" class="nav-link">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a></li>
            <li><a href="office_inventory.php" class="nav-link">
                    <i class="fas fa-building"></i> Office Inventory
                </a></li>
            <li><a href="property_inventory.php" class="nav-link">
                    <i class="fas fa-boxes"></i> Property Inventory
                </a></li>
            <li><a href="rooms_inventory.php" class="nav-link">
                    <i class="fas fa-door-open"></i> Rooms Inventory
                </a></li>
            <li><a href="property_issuance.php" class="nav-link active">
                    <i class="fas fa-hand-holding"></i> Property Issuance
                </a></li>
                <li><a href="equipment_transfer_request.php" class="nav-link">
                    <i class="fas fa-exchange-alt"></i> Transfer Request
                </a></li>
                <li><a href="borrowers_forms.php" class="nav-link">
                    <i class="fas fa-hand-holding"></i> Borrower Forms
                </a></li>
                <li><a href="aircon_list.php" class="nav-link">
                    <i class="fas fa-snowflake"></i> Aircons
                </a></li>
                <li><a href="property_release_logs.php" class="nav-link">
                    <i class="fas fa-file"></i> Release Records
                </a></li>
            <li><a href="../logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a></li>
                    </a></li>
            </ul>
        </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="content-header">
        <h1>Property Issuance/Transfer Slip</h1>
        <p>Create and manage property transfer and issuance documents</p>
    </div>

    <!-- Property Issuance Form -->
    <div class="issuance-container">
        <div class="issuance-header">
            <h2 class="issuance-title">Property Issuance/Transfer Slip</h2>
        </div>

        <div class="form-content">
            <form id="issuanceForm" action="../actions/save_property_issuance.php" method="POST">
                <!-- Form Header Information -->
                <div class="form-header-info">
                    <div>
                        <div class="form-group">
                            <label for="transferringDept">Transferring Department/Unit/Office:</label>
                            <select id="transferringDept" name="transferring_dept" class="form-control" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="receivingDept">Receiving Department/Unit/Office:</label>
                            <select id="receivingDept" name="receiving_dept" class="form-control" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reasonTransfer">Reason of Transfer:</label>
                            <input type="text" id="reasonTransfer" name="reason_transfer" class="form-control" placeholder="Enter reason for transfer" required>
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="transferDate">Date of Transfer:</label>
                            <input type="date" id="transferDate" name="transfer_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="transferTime">Time:</label>
                            <?php date_default_timezone_set('Asia/Manila'); ?>
                            <input type="time" id="transferTime" name="transfer_time" class="form-control" 
                                   value="<?= date('H:i') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="items-section">
                    <h3 class="section-title">Items to Transfer</h3>
                    
                    <div class="row-management">
                        <button type="button" class="btn-add-row" onclick="addItemRow()">
                            <i class="fas fa-plus"></i> Add Row
                        </button>
                        <button type="button" class="btn-remove-row" onclick="removeLastRow()">
                            <i class="fas fa-minus"></i> Remove Row
                        </button>
                    </div>

                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 8%;">Number of Items</th>
                                <th style="width: 25%;">Items</th>
                                <th style="width: 30%;">Description</th>
                                <th style="width: 15%;">Serial Number</th>
                                <th style="width: 11%;">Transferring Code</th>
                                <th style="width: 11%;">Receiving Code</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <tr>
                                <td><input type="number" name="items[0][quantity]" min="1" value="1" required></td>
                                <td>
                                    <select name="items[0][item_id]" class="item-select" required onchange="updateItemDescription(this, 0)">
                                        <option value="">Select Item</option>
                                        <?php if ($inventory_result && $inventory_result->num_rows > 0): ?>
                                            <?php while ($item = $inventory_result->fetch_assoc()): ?>
                                                <option value="<?= $item['inventory_id'] ?>" 
                                                        data-description="<?= htmlspecialchars($item['description'] ?? $item['item_name']) ?>"
                                                        data-stock="<?= $item['current_stock'] ?>">
                                                    <?= htmlspecialchars($item['item_name']) ?> (Stock: <?= $item['current_stock'] ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </td>
                                <td><input type="text" name="items[0][description]" placeholder="Item description" readonly></td>
                                <td><input type="text" name="items[0][serial_number]" placeholder="Serial number"></td>
                                <td><input type="text" name="items[0][transferring_code]" placeholder="Transfer code"></td>
                                <td><input type="text" name="items[0][receiving_code]" placeholder="Receive code"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Signatures Section -->
                <div class="signatures-section">
                    <h3 class="section-title">Signatures</h3>
                    <div class="signatures-grid">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-title">MARY GRACE M. BAYTOLA</div>
                            <div class="signature-subtitle">Property Custodian</div>
                            <div style="margin-top: 10px; font-weight: 600;">Prepared by:</div>
                        </div>

                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-title">MARILOU S. SUAREZ</div>
                            <div class="signature-subtitle">Administrative Officer</div>
                            <div style="margin-top: 10px; font-weight: 600;">Noted by:</div>
                        </div>

                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-title">DR. DELIA C. AQUINILLA</div>
                            <div class="signature-subtitle">VP for Finance and Administration</div>
                            <div style="margin-top: 10px; font-weight: 600;">Approved by:</div>
                        </div>

                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-title"></div>
                            <div class="signature-subtitle">Signature over Printed Name</div>
                            <div style="margin-top: 10px; font-weight: 600;">Received by:</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn-action btn-secondary" onclick="clearForm()">
                        <i class="fas fa-undo"></i> Clear
                    </button>
                    <button type="button" class="btn-action btn-success" onclick="printForm()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="submit" class="btn-action btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Print Layout (Hidden by default, shown only when printing) -->
    <div class="print-container" style="display: none;">
        <h2 class="print-title">Property Issuance/Transfer Slip</h2>

        <div class="print-info">
            <p>Transferring Department/Unit/Office: <span id="print-transferring">_____________________________</span> Date of Transfer: <span id="print-date">__________</span></p>
            <p>Receiving Department/Unit/Office: <span id="print-receiving">_______________________________</span> Time: <span id="print-time">__________</span></p>
            <p>Reason of Transfer: <span id="print-reason">_____________________________________________________________________</span></p>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th style="width:10%;">Number of Items</th>
                    <th style="width:20%;">Items</th>
                    <th style="width:20%;">Description</th>
                    <th style="width:20%;">Serial Number</th>
                    <th style="width:15%;">Transferring Code</th>
                    <th style="width:15%;">Receiving Code</th>
                </tr>
            </thead>
            <tbody id="print-table-body">
                <!-- Rows will be populated by JavaScript -->
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
            </tbody>
        </table>

        <div class="print-signatures">
            <div class="print-sign-section">
                <p>Prepared by:</p>
                <span class="print-sign-name">Mary Grace M. Baytola</span>
                <span class="print-role">Property Custodian</span>
            </div>

            <div class="print-sign-section">
                <p>Noted By:</p>
                <span class="print-sign-name">Marilou S. Suarez</span>
                <span class="print-role">Administrative Officer</span>
            </div>

            <div class="print-sign-section">
                <p>Approved by:</p>
                <span class="print-sign-name">Dr. Delia C. Advincula</span>
                <span class="print-role">VP for Finance and Administration</span>
            </div>

            <div class="print-received">
                <p>Received by:</p>
                <div class="print-line"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let rowCount = 1;

    // Add new item row
    function addItemRow() {
        const tbody = document.getElementById('itemsTableBody');
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td><input type="number" name="items[${rowCount}][quantity]" min="1" value="1" required></td>
            <td>
                <select name="items[${rowCount}][item_id]" class="item-select" required onchange="updateItemDescription(this, ${rowCount})">
                    <option value="">Select Item</option>
                    <?php 
                    if ($inventory_result) {
                        $inventory_result->data_seek(0);
                        while ($item = $inventory_result->fetch_assoc()): 
                    ?>
                        <option value="<?= $item['inventory_id'] ?>" 
                                data-description="<?= htmlspecialchars($item['description'] ?? $item['item_name']) ?>"
                                data-stock="<?= $item['current_stock'] ?>">
                            <?= htmlspecialchars($item['item_name']) ?> (Stock: <?= $item['current_stock'] ?>)
                        </option>
                    <?php 
                        endwhile;
                    }
                    ?>
                </select>
            </td>
            <td><input type="text" name="items[${rowCount}][description]" placeholder="Item description" readonly></td>
            <td><input type="text" name="items[${rowCount}][serial_number]" placeholder="Serial number"></td>
            <td><input type="text" name="items[${rowCount}][transferring_code]" placeholder="Transfer code"></td>
            <td><input type="text" name="items[${rowCount}][receiving_code]" placeholder="Receive code"></td>
        `;
        
        tbody.appendChild(row);
        rowCount++;
    }

    // Remove last row
    function removeLastRow() {
        const tbody = document.getElementById('itemsTableBody');
        if (tbody.children.length > 1) {
            tbody.removeChild(tbody.lastElementChild);
            rowCount--;
        }
    }

    // Update item description when item is selected
    function updateItemDescription(selectElement, rowIndex) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const description = selectedOption.getAttribute('data-description') || '';
        const descriptionInput = selectElement.closest('tr').querySelector(`input[name="items[${rowIndex}][description]"]`);
        
        if (descriptionInput) {
            descriptionInput.value = description;
        }
    }

    // Clear form
    function clearForm() {
        if (confirm('Are you sure you want to clear all form data?')) {
            document.getElementById('issuanceForm').reset();
            
            // Reset table to single row
            const tbody = document.getElementById('itemsTableBody');
            tbody.innerHTML = `
                <tr>
                    <td><input type="number" name="items[0][quantity]" min="1" value="1" required></td>
                    <td>
                        <select name="items[0][item_id]" class="item-select" required onchange="updateItemDescription(this, 0)">
                            <option value="">Select Item</option>
                            <?php 
                            if ($inventory_result) {
                                $inventory_result->data_seek(0);
                                while ($item = $inventory_result->fetch_assoc()): 
                            ?>
                                <option value="<?= $item['inventory_id'] ?>" 
                                        data-description="<?= htmlspecialchars($item['description'] ?? $item['item_name']) ?>"
                                        data-stock="<?= $item['current_stock'] ?>">
                                    <?= htmlspecialchars($item['item_name']) ?> (Stock: <?= $item['current_stock'] ?>)
                                </option>
                            <?php 
                                endwhile;
                            }
                            ?>
                        </select>
                    </td>
                    <td><input type="text" name="items[0][description]" placeholder="Item description" readonly></td>
                    <td><input type="text" name="items[0][serial_number]" placeholder="Serial number"></td>
                    <td><input type="text" name="items[0][transferring_code]" placeholder="Transfer code"></td>
                    <td><input type="text" name="items[0][receiving_code]" placeholder="Receive code"></td>
                </tr>
            `;
            rowCount = 1;
        }
    }

    // Print form
    function printForm() {
        window.print();
    }

    // Form validation before submit
    document.getElementById('issuanceForm').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.item-select');
        let hasItems = false;
        
        items.forEach(item => {
            if (item.value) {
                hasItems = true;
            }
        });
        
        if (!hasItems) {
            e.preventDefault();
            alert('Please select at least one item to transfer.');
            return false;
        }
        
        // Check if quantities don't exceed available stock
        let stockError = false;
        items.forEach((item, index) => {
            if (item.value) {
                const selectedOption = item.options[item.selectedIndex];
                const availableStock = parseInt(selectedOption.getAttribute('data-stock'));
                const quantityInput = item.closest('tr').querySelector('input[type="number"]');
                const requestedQuantity = parseInt(quantityInput.value);
                
                if (requestedQuantity > availableStock) {
                    stockError = true;
                    alert(`Requested quantity (${requestedQuantity}) exceeds available stock (${availableStock}) for ${selectedOption.text}`);
                }
            }
        });
        
        if (stockError) {
            e.preventDefault();
            return false;
        }
    });

    // Show session messages if any
    <?php if ($session_message): ?>
        alert('<?= addslashes($session_message) ?>');
    <?php endif; ?>
    
    <?php if ($session_error): ?>
        alert('Error: <?= addslashes($session_error) ?>');
    <?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>