<?php
$pageTitle = 'Office Inventory';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';
$dashboard_link = ($user_type == 'Admin') ? '../dashboard.php' : '../dashboard.php';

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

    /* Office Inventory Form Container */
    .office-inventory-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Header Section */
    .form-header {
        background: linear-gradient(135deg, var(--accent-green-approved) 0%, #20c997 100%);
        color: var(--text-white);
        padding: 20px 30px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .college-logo {
        width: 80px;
        height: 80px;
        background: var(--text-white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--primary-green);
    }

    .header-info h1 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .header-info h2 {
        margin: 5px 0 0 0;
        font-size: 1.2rem;
        font-weight: 600;
        opacity: 0.9;
    }

    .header-info p {
        margin: 5px 0 0 0;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .form-number {
        text-align: right;
    }

    .form-number h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .form-number p {
        margin: 5px 0 0 0;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* Contact Info Bar */
    .contact-bar {
        background: var(--primary-green);
        color: var(--text-white);
        padding: 10px 30px;
        font-size: 0.8rem;
        display: flex;
        justify-content: space-between;
    }

    /* Form Content */
    .form-content {
        padding: 30px;
    }

    .form-title {
        text-align: center;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        text-transform: uppercase;
    }

    .semester-info {
        text-align: center;
        margin-bottom: 30px;
        font-size: 1rem;
        color: var(--text-dark);
    }

    /* Form Fields */
    .form-fields {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .form-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group label {
        font-weight: 600;
        color: var(--text-dark);
        min-width: 120px;
    }

    .form-control {
        flex: 1;
        padding: 8px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(7, 59, 29, 0.1);
    }

    /* Inventory Table */
    .inventory-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .inventory-table th {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #333;
    }

    .inventory-table td {
        padding: 10px 8px;
        border: 1px solid #ddd;
        text-align: center;
        vertical-align: middle;
        min-height: 40px;
    }

    .inventory-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .inventory-table input {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.85rem;
    }

    .inventory-table input:focus {
        outline: none;
        border-color: var(--primary-green);
    }

    /* Row Management */
    .row-management {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        justify-content: center;
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
        margin-top: 30px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }

    .signature-box {
        text-align: center;
        padding: 20px 10px;
    }

    .signature-title {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
        font-size: 0.9rem;
    }

    .signature-line {
        height: 50px;
        border-bottom: 2px solid var(--text-dark);
        margin: 15px 0;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 5px;
    }

    .signature-name {
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 3px;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .signature-role {
        font-size: 0.75rem;
        color: #666;
        font-style: italic;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
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
            size: A4 landscape;
            margin: 0.5cm;
        }

        body {
            font-family: Arial, sans-serif !important;
            font-size: 11px !important;
            color: black !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        .sidebar,
        .action-buttons,
        .row-management,
        .content-header {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .office-inventory-container {
            box-shadow: none !important;
            margin: 0 !important;
            background: white !important;
        }

        .form-content {
            padding: 10px !important;
        }

        .inventory-table {
            font-size: 10px !important;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 4px !important;
            border: 1px solid black !important;
        }

        .signatures-section {
            margin-top: 20px !important;
        }

        .signature-line {
            height: 30px !important;
            margin: 10px 0 !important;
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

        .form-fields {
            grid-template-columns: 1fr;
        }

        .signatures-section {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .action-buttons {
            flex-direction: column;
            align-items: center;
        }

        .inventory-table {
            font-size: 0.75rem;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 6px 4px;
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
            <li><a href="office_inventory.php" class="nav-link active">
                    <i class="fas fa-building"></i> Office Inventory
                </a></li>
            <li><a href="property_inventory.php" class="nav-link">
                    <i class="fas fa-boxes"></i> Property Inventory
                </a></li>
            <li><a href="property_issuance.php" class="nav-link">
                    <i class="fas fa-hand-holding"></i> Property Issuance
                </a></li>
                <li><a href="equipment_transfer_request.php" class="nav-link">
                    <i class="fas fa-exchange-alt"></i> Transfer Request
                </a></li>
                <li><a href="borrowers_forms.php" class="nav-link">
                    <i class="fas fa-hand-holding"></i> Borrower Forms
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
        <h1>Office Inventory Form</h1>
        <p>Manage and track office property inventory</p>
    </div>

    <!-- Office Inventory Form -->
    <div class="office-inventory-container">
        <!-- Header Section -->
        <div class="form-header">
            <div class="header-left">
                <div class="college-logo">
                    <i class="fas fa-university"></i>
                </div>
                <div class="header-info">
                    <h1>Davao Central College</h1>
                    <h2>Property Custodian</h2>
                    <p>Juan dela Cruz St., Toril, Davao City, Philippines</p>
                </div>
            </div>
            <div class="form-number">
                <h3>PC-007</h3>
                <p>Revision: 2</p>
                <p>Date Issued: July 2025</p>
            </div>
        </div>

        <!-- Contact Information Bar -->
        <div class="contact-bar">
            <span>• Office Email: dccproperty@dcdomain@gmail.com</span>
            <span>• Email Address: davaocentralcollege2021@gmail.com</span>
            <span>• Website: www.dcc.edu.ph</span>
        </div>

        <div class="form-content">
            <form id="officeInventoryForm" action="../actions/save_office_inventory.php" method="POST">
                <!-- Form Title -->
                <h2 class="form-title">Office Inventory Form</h2>
                
                <!-- Semester Information -->
                <div class="semester-info">
                    <div style="display: flex; gap: 30px; justify-content: center; align-items: center; margin-bottom: 15px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                            <input type="checkbox" name="1st" value="1st Semester" style="transform: scale(1.2);">
                            1st Semester
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                            <input type="checkbox" name="2nd" value="2nd Semester" style="transform: scale(1.2);">
                            2nd Semester
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                            <input type="checkbox" name="Summer" value="Summer" style="transform: scale(1.2);">
                            Summer
                        </label>
                    </div>
                    <div class="col-md-6" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-left: 290px;">
                        <strong>SY</strong>
                        <input type="text" name="school_year" class="form-control" style="width: 200px; display: inline-block;" placeholder="e.g., 2024-2025" required>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="form-fields">
                    <div class="form-group">
                        <label for="building">Building:</label>
                        <input type="text" id="building" name="building" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="office">Office:</label>
                        <input type="text" id="office" name="office" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="accountable_person">Accountable Person:</label>
                        <input type="text" id="accountable_person" name="accountable_person" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="inventory_date">Date of Inventory:</label>
                        <input type="date" id="inventory_date" name="inventory_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Row Management Buttons -->
                <div class="row-management">
                    <button type="button" class="btn-add-row" onclick="addInventoryRow()">
                        <i class="fas fa-plus"></i> Add Row
                    </button>
                    <button type="button" class="btn-remove-row" onclick="removeLastRow()">
                        <i class="fas fa-minus"></i> Remove Row
                    </button>
                </div>

                <!-- Inventory Table -->
                <table class="inventory-table" id="inventoryTable">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Equipment/Item Category</th>
                            <th style="width: 15%;">Description of Equipment/Item</th>
                            <th style="width: 8%;">Brand</th>
                            <th style="width: 10%;">Number of Equipment on Site</th>
                            <th style="width: 12%;">Serial #/ID #/ Model</th>
                            <th style="width: 10%;">Date of Acquisition</th>
                            <th style="width: 8%;">Warranty Date</th>
                            <th style="width: 10%;">Original Source of Equipment</th>
                            <th style="width: 10%;">Condition of Equipment</th>
                            <th style="width: 5%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTableBody">
                        <tr>
                            <td><input type="text" name="items[0][category]" placeholder="Category"></td>
                            <td><input type="text" name="items[0][description]" placeholder="Description"></td>
                            <td><input type="text" name="items[0][brand]" placeholder="Brand"></td>
                            <td><input type="number" name="items[0][quantity]" placeholder="Qty" min="0"></td>
                            <td><input type="text" name="items[0][serial_number]" placeholder="Serial/ID/Model"></td>
                            <td><input type="date" name="items[0][acquisition_date]"></td>
                            <td><input type="date" name="items[0][warranty_date]"></td>
                            <td><input type="text" name="items[0][source]" placeholder="Source"></td>
                            <td>
                                <select name="items[0][condition]">
                                    <option value="">Select</option>
                                    <option value="Excellent">Excellent</option>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                    <option value="Damaged">Damaged</option>
                                </select>
                            </td>
                            <td><input type="text" name="items[0][remarks]" placeholder="Remarks"></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Signatures Section -->
                <div class="signatures-section">
                    <div class="signature-box">
                        <div class="signature-title">Prepared By:</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">Faculty/Staff Name</div>
                        <div class="signature-role">Signature over Printed Name</div>
                    </div>

                    <div class="signature-box">
                        <div class="signature-title">Checked By:</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">Mary Grace M. Baytola / Marilou L. Suarez</div>
                        <div class="signature-role">Property Custodian / Administrative Officer</div>
                    </div>

                    <div class="signature-box">
                        <div class="signature-title">Noted By:</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">Delia C. Advincula, PhD</div>
                        <div class="signature-role">Vice Pres. for Finance and Administration</div>
                    </div>

                    <div class="signature-box">
                        <div class="signature-title">Approved By:</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">Ruben L. Dela Cruz, PhD, RSC</div>
                        <div class="signature-role">School President</div>
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
</div>

<script>
    let rowCount = 1;

    // Add new inventory row
    function addInventoryRow() {
        const tbody = document.getElementById('inventoryTableBody');
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td><input type="text" name="items[${rowCount}][category]" placeholder="Category"></td>
            <td><input type="text" name="items[${rowCount}][description]" placeholder="Description"></td>
            <td><input type="text" name="items[${rowCount}][brand]" placeholder="Brand"></td>
            <td><input type="number" name="items[${rowCount}][quantity]" placeholder="Qty" min="0"></td>
            <td><input type="text" name="items[${rowCount}][serial_number]" placeholder="Serial/ID/Model"></td>
            <td><input type="date" name="items[${rowCount}][acquisition_date]"></td>
            <td><input type="date" name="items[${rowCount}][warranty_date]"></td>
            <td><input type="text" name="items[${rowCount}][source]" placeholder="Source"></td>
            <td>
                <select name="items[${rowCount}][condition]">
                    <option value="">Select</option>
                    <option value="Excellent">Excellent</option>
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Poor">Poor</option>
                    <option value="Damaged">Damaged</option>
                </select>
            </td>
            <td><input type="text" name="items[${rowCount}][remarks]" placeholder="Remarks"></td>
        `;
        
        tbody.appendChild(row);
        rowCount++;
    }

    // Remove last row
    function removeLastRow() {
        const tbody = document.getElementById('inventoryTableBody');
        if (tbody.children.length > 1) {
            tbody.removeChild(tbody.lastElementChild);
            rowCount--;
        }
    }

    // Clear form
    function clearForm() {
        if (confirm('Are you sure you want to clear all form data?')) {
            document.getElementById('officeInventoryForm').reset();
            
            // Reset table to single row
            const tbody = document.getElementById('inventoryTableBody');
            tbody.innerHTML = `
                <tr>
                    <td><input type="text" name="items[0][category]" placeholder="Category"></td>
                    <td><input type="text" name="items[0][description]" placeholder="Description"></td>
                    <td><input type="text" name="items[0][brand]" placeholder="Brand"></td>
                    <td><input type="number" name="items[0][quantity]" placeholder="Qty" min="0"></td>
                    <td><input type="text" name="items[0][serial_number]" placeholder="Serial/ID/Model"></td>
                    <td><input type="date" name="items[0][acquisition_date]"></td>
                    <td><input type="date" name="items[0][warranty_date]"></td>
                    <td><input type="text" name="items[0][source]" placeholder="Source"></td>
                    <td>
                        <select name="items[0][condition]">
                            <option value="">Select</option>
                            <option value="Excellent">Excellent</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </td>
                    <td><input type="text" name="items[0][remarks]" placeholder="Remarks"></td>
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
    document.getElementById('officeInventoryForm').addEventListener('submit', function(e) {
        const building = document.getElementById('building').value;
        const office = document.getElementById('office').value;
        const accountablePerson = document.getElementById('accountable_person').value;
        const inventoryDate = document.getElementById('inventory_date').value;
        
        if (!building || !office || !accountablePerson || !inventoryDate) {
            e.preventDefault();
            alert('Please fill in all required fields (Building, Office, Accountable Person, and Date of Inventory).');
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