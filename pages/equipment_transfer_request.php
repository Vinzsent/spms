<?php
$pageTitle = 'Equipment Transfer Request';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';
$dashboard_link = ($user_type == 'Admin') ? '../dashboard.php' : '../dashboard.php';

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
    .transfer-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .transfer-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 20px 30px;
        text-align: center;
        position: relative;
    }

    .college-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .college-logo {
        width: 60px;
        height: 60px;
        background: var(--text-white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: var(--primary-green);
    }

    .college-info {
        flex: 1;
        text-align: center;
        margin: 0 20px;
    }

    .college-name {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .college-subtitle {
        font-size: 0.9rem;
        margin: 5px 0;
        opacity: 0.9;
    }

    .form-code {
        background: var(--text-white);
        color: var(--primary-green);
        padding: 8px 15px;
        border-radius: 5px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .transfer-title {
        margin: 15px 0 0 0;
        font-weight: 700;
        font-size: 1.6rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-content {
        padding: 30px;
    }

    /* Form Fields */
    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        align-items: end;
    }

    .form-group {
        margin-bottom: 20px;
        flex: 1;
    }

    .form-group.small {
        flex: 0 0 150px;
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
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(7, 59, 29, 0.1);
    }

    .underline-input {
        border: none;
        border-bottom: 2px solid #333;
        border-radius: 0;
        background: transparent;
        padding: 8px 5px;
        font-weight: 600;
    }

    .underline-input:focus {
        border-bottom-color: var(--primary-green);
        box-shadow: none;
    }

    /* Transfer Sections */
    .transfer-sections {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin: 30px 0;
        padding: 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background-color: #fafafa;
    }

    .transfer-section h4 {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 15px;
        text-transform: uppercase;
        font-size: 1rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary-green);
        padding-bottom: 5px;
    }

    .section-field {
        margin-bottom: 15px;
    }

    .section-field label {
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    /* Checkboxes */
    .checkbox-group {
        display: flex;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary-green);
    }

    .checkbox-item label {
        margin: 0;
        font-weight: 500;
        cursor: pointer;
    }

    /* Signatures Section */
    .signatures-section {
        margin-top: 40px;
        padding-top: 30px;
        border-top: 2px solid var(--bg-light);
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary-green);
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

    .signature-role {
        font-weight: 600;
        margin-top: 10px;
        color: var(--text-dark);
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
        .content-header {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .transfer-container {
            box-shadow: none !important;
            margin: 0 !important;
            background: white !important;
        }

        .form-content {
            padding: 0 !important;
        }

        .transfer-header {
            background: white !important;
            color: black !important;
            border: 2px solid black !important;
        }

        .college-logo {
            background: white !important;
            border: 2px solid black !important;
            color: black !important;
        }

        .form-code {
            background: white !important;
            color: black !important;
            border: 1px solid black !important;
        }

        .form-control,
        .underline-input {
            border-color: black !important;
            background: white !important;
        }

        .transfer-sections {
            border-color: black !important;
            background: white !important;
        }

        .signature-box {
            border-color: black !important;
            background: white !important;
        }

        .signature-line {
            border-bottom-color: black !important;
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

        .college-header {
            flex-direction: column;
            gap: 15px;
        }

        .transfer-sections {
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

        .form-row {
            flex-direction: column;
            gap: 10px;
        }

        .checkbox-group {
            flex-direction: column;
            gap: 10px;
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
            <li><a href="property_issuance.php" class="nav-link">
                    <i class="fas fa-hand-holding"></i> Property Issuance
                </a></li>
                <li><a href="equipment_transfer_request.php" class="nav-link active">
                    <i class="fas fa-exchange-alt"></i> Transfer Request
                </a></li>
                <li><a href="borrowers_forms.php" class="nav-link">
                    <i class="fas fa-hand-holding"></i> Borrower Forms
                </a></li>
                <li><a href="aircon_list.php" class="nav-link">
                    <i class="fas fa-snowflake"></i> Aircons
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
        <h1>Equipment Transfer Request Form</h1>
        <p>Request form for transfer of equipment/furniture between departments</p>
    </div>

    <!-- Transfer Request Form -->
    <div class="transfer-container">
        <div class="transfer-header">
            <div class="college-header">
                <div class="college-logo">
                    <i class="fas fa-university"></i>
                </div>
                <div class="college-info">
                    <h2 class="college-name">Davao Central College</h2>
                    <p class="college-subtitle">Property Custodian</p>
                    <p class="college-subtitle">Juan dela Cruz St., Toril, Davao City</p>
                </div>
                <div class="form-code">
                    PC-005<br>
                    Revision: 2<br>
                    Date Revised:<br>
                    July 2022
                </div>
            </div>
            <h2 class="transfer-title">Request Form for Transfer of Equipment/Furniture Between Department/Unit/Office</h2>
        </div>

        <div class="form-content">
            <form id="transferForm" action="../actions/save_transfer_request.php" method="POST">
                <!-- Basic Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="description">Description of Item(s):</label>
                        <input type="text" id="description" name="description" class="form-control underline-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="serialModel">Serial #/Model#:</label>
                        <input type="text" id="serialModel" name="serial_model" class="form-control underline-input">
                    </div>
                </div>

                <!-- Transfer Type -->
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="permanent" name="transfer_type[]" value="permanent">
                        <label for="permanent">Permanent Transfer:</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="temporary" name="transfer_type[]" value="temporary">
                        <label for="temporary">Temporary Transfer:</label>
                    </div>
                </div>

                <!-- Date of Return Field - Hidden by default -->
                <div class="form-row" id="returnDateRow" style="display: none;">
                    <div class="form-group">
                        <label for="returnDate">Date of Return:</label>
                        <input type="date" id="returnDate" name="return_date" class="form-control underline-input">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reasonTransfer">Reason of Transfer:</label>
                        <input type="text" id="reasonTransfer" name="reason_transfer" class="form-control underline-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="transferDate">Date:</label>
                        <input type="date" id="transferDate" name="transfer_date" class="form-control underline-input" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Transfer Sections -->
                <div class="transfer-sections">
                    <div class="transfer-from">
                        <h4>Transferring From</h4>
                        <div class="section-field">
                            <label for="fromDept">Department/Unit/Office Name:</label>
                            <select id="fromDept" name="from_department" class="form-control" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="section-field">
                            <label for="fromPropertyCode">Property Code:</label>
                            <input type="text" id="fromPropertyCode" name="from_property_code" class="form-control">
                        </div>
                        <div class="section-field">
                            <label for="fromBuildingRoom">Building and Room Number:</label>
                            <input type="text" id="fromBuildingRoom" name="from_building_room" class="form-control">
                        </div>
                    </div>

                    <div class="transfer-to">
                        <h4>Transferring To</h4>
                        <div class="section-field">
                            <label for="toDept">Department/Unit/Office Name:</label>
                            <select id="toDept" name="to_department" class="form-control" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="section-field">
                            <label for="toPropertyCode">Property Code:</label>
                            <input type="text" id="toPropertyCode" name="to_property_code" class="form-control">
                        </div>
                        <div class="section-field">
                            <label for="toBuildingRoom">Building and Room Number:</label>
                            <input type="text" id="toBuildingRoom" name="to_building_room" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Signatures Section -->
                <div class="signatures-section">
                    <h3 class="section-title">Approvals and Signatures</h3>
                    <div class="signatures-grid">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-title">Requested By:</div>
                            <div class="signature-subtitle">Signature over printed name</div>
                        </div>

                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-title">Noted by:</div>
                            <div class="signature-subtitle">Immediate Head</div>
                        </div>

                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-title">Checked By:</div>
                            <div class="signature-subtitle">MARY GRACE M. BAYTOLA</div>
                            <div class="signature-role">Property Custodian</div>
                        </div>

                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-title">Approved by:</div>
                            <div class="signature-subtitle">DR. DELIA C. ADVINCULA</div>
                            <div class="signature-role">VP for Finance and Administration</div>
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
                        <i class="fas fa-save"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Clear form function
    function clearForm() {
        if (confirm('Are you sure you want to clear all form data?')) {
            document.getElementById('transferForm').reset();
        }
    }

    // Print form function
    function printForm() {
        // Validate required fields before printing
        const form = document.getElementById('transferForm');
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = 'red';
            } else {
                field.style.borderColor = '';
            }
        });
        
        // Additional validation for temporary transfer return date
        const temporaryTransfer = document.getElementById('temporary');
        const returnDateInput = document.getElementById('returnDate');
        
        if (temporaryTransfer.checked && !returnDateInput.value) {
            isValid = false;
            returnDateInput.style.borderColor = 'red';
        }
        
        if (!isValid) {
            alert('Please fill in all required fields before printing.');
            return;
        }
        
        window.print();
    }

    // Form validation
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        const transferTypes = document.querySelectorAll('input[name="transfer_type[]"]:checked');
        
        if (transferTypes.length === 0) {
            e.preventDefault();
            alert('Please select at least one transfer type (Permanent or Temporary).');
            return;
        }
        
        // Prevent selecting both permanent and temporary
        if (transferTypes.length > 1) {
            e.preventDefault();
            alert('Please select only one transfer type.');
            return;
        }
        
        // Validate return date for temporary transfers
        const temporaryTransfer = document.getElementById('temporary');
        const returnDateInput = document.getElementById('returnDate');
        
        if (temporaryTransfer.checked && !returnDateInput.value) {
            e.preventDefault();
            alert('Please specify the Date of Return for temporary transfer.');
            returnDateInput.focus();
            return;
        }
    });

    // Prevent selecting both transfer types and handle Date of Return field
    document.querySelectorAll('input[name="transfer_type[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                // Uncheck other transfer types
                document.querySelectorAll('input[name="transfer_type[]"]').forEach(other => {
                    if (other !== this) {
                        other.checked = false;
                    }
                });
                
                // Show/hide Date of Return field based on temporary transfer selection
                const returnDateRow = document.getElementById('returnDateRow');
                const returnDateInput = document.getElementById('returnDate');
                
                if (this.value === 'temporary') {
                    // Show Date of Return field for temporary transfer
                    returnDateRow.style.display = 'block';
                    returnDateInput.required = true;
                } else {
                    // Hide Date of Return field for permanent transfer
                    returnDateRow.style.display = 'none';
                    returnDateInput.required = false;
                    returnDateInput.value = ''; // Clear the value when hidden
                }
            } else {
                // If temporary transfer is unchecked, hide Date of Return field
                if (this.value === 'temporary') {
                    const returnDateRow = document.getElementById('returnDateRow');
                    const returnDateInput = document.getElementById('returnDate');
                    returnDateRow.style.display = 'none';
                    returnDateInput.required = false;
                    returnDateInput.value = '';
                }
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
