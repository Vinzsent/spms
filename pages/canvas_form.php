<?php
$pageTitle = 'Canvass Form';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';

// Check if we're in edit mode
$edit_mode = isset($_GET['edit']) && is_numeric($_GET['edit']);
$canvass_data = null;
$canvass_items = [];

if ($edit_mode) {
    $canvass_id = intval($_GET['edit']);
    
    // Fetch canvass data
    $canvass_query = "SELECT * FROM canvass WHERE canvass_id = ?";
    $stmt = $conn->prepare($canvass_query);
    $stmt->bind_param("i", $canvass_id);
    $stmt->execute();
    $canvass_result = $stmt->get_result();
    
    if ($canvass_result->num_rows > 0) {
        $canvass_data = $canvass_result->fetch_assoc();
        
        // Fetch canvass items
        $items_query = "SELECT * FROM canvass_items WHERE canvass_id = ? ORDER BY item_number ASC";
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param("i", $canvass_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        
        while ($item = $items_result->fetch_assoc()) {
            $canvass_items[] = $item;
        }
    }
}

// Fetch suppliers from database
$suppliers_query = "SELECT supplier_id, supplier_name FROM supplier ORDER BY supplier_name ASC";
$suppliers_result = $conn->query($suppliers_query);
$suppliers_array = [];
if ($suppliers_result && $suppliers_result->num_rows > 0) {
    while ($supplier = $suppliers_result->fetch_assoc()) {
        $suppliers_array[] = $supplier;
    }
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

    /* Canvass Form Container */
    .canvass-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
        padding: 40px;
    }

    .canvass-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--primary-green);
        padding-bottom: 20px;
    }

    .canvass-title {
        color: var(--primary-green);
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    .canvass-info {
        text-align: right;
    }

    .canvass-info-item {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .canvass-info-label {
        font-weight: 600;
        margin-right: 10px;
        color: var(--text-dark);
        min-width: 80px;
    }

    .canvass-info-input {
        border: none;
        border-bottom: 1px solid #ccc;
        padding: 5px 10px;
        font-size: 1rem;
        min-width: 150px;
        background: transparent;
    }

    .canvass-info-input:focus {
        outline: none;
        border-bottom-color: var(--primary-green);
    }

    /* Table Styles */
    .canvass-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .canvass-table th {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 15px 10px;
        text-align: center;
        font-weight: 600;
        border: 1px solid var(--primary-green);
    }

    .canvass-table td {
        padding: 12px 10px;
        border: 1px solid #ddd;
        text-align: center;
        vertical-align: middle;
    }

    .canvass-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .canvass-table tbody tr:hover {
        background-color: rgba(7, 59, 29, 0.05);
    }

    .canvass-table input, .canvass-table select, .canvass-table textarea {
        border: none;
        background: transparent;
        width: 100%;
        text-align: center;
        padding: 5px;
    }

    .canvass-table input:focus, .canvass-table select:focus, .canvass-table textarea:focus {
        outline: 1px solid var(--primary-green);
        background: white;
    }

    .canvass-table select {
        cursor: pointer;
    }

    /* Signature Section */
    .signature-section {
        display: flex;
        justify-content: center;
        margin-top: 50px;
    }

    .signature-box {
        text-align: center;
        min-width: 300px;
    }

    .signature-line {
        border-bottom: 1px solid #333;
        height: 60px;
        margin-bottom: 10px;
        position: relative;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 5px;
        font-weight: 500;
    }

    .signature-title {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
    }

    .signature-subtitle {
        font-size: 0.9rem;
        color: #666;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        justify-content: flex-end;
    }

    .btn-canvass {
        padding: 12px 24px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: var(--primary-green);
        color: var(--text-white);
    }

    .btn-primary:hover {
        background-color: var(--dark-green);
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

    .btn-success {
        background-color: var(--accent-green-approved);
        color: var(--text-white);
    }

    .btn-success:hover {
        background-color: var(--primary-green);
        transform: translateY(-2px);
    }

    .btn-danger:hover {
        background-color: var(--primary-green);
        transform: translateY(-5px);
    }

    .btn-danger {
        background-color: var(--accent-red);
        transform: translateY(-2px);
        color: var(--text-white);
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

        .canvass-container {
            padding: 20px;
        }

        .canvass-header {
            flex-direction: column;
            text-align: center;
        }

        .canvass-info {
            text-align: left;
            margin-top: 20px;
        }

        .signature-section {
            margin-top: 30px;
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
            <li><a href="canvas_form.php" class="nav-link active">
                    <i class="fas fa-clipboard-list"></i> Canvass Form
                </a></li>
            <li><a href="canvass_form_list.php" class="nav-link">
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
        <h1>Canvass Form</h1>
        <p>Compare supplier prices and create canvass reports</p>
    </div>

    <!-- Canvass Form -->
    <div class="canvass-container">
        <div class="canvass-header">
            <h2 class="canvass-title">PRICE CANVASS FORM</h2>
            <div class="canvass-info">
                <div class="canvass-info-item">
                    <span class="canvass-info-label">Date:</span>
                    <input type="date" class="canvass-info-input" id="canvassDate" value="<?= $edit_mode && $canvass_data ? $canvass_data['canvass_date'] : date('Y-m-d') ?>">
                </div>
            </div>
        </div>
        <a href="canvass_form_list.php" class="btn mb-3 view-button" style="background-color: var(--accent-orange); color: dark; text-decoration: none;"><i class="fas fa-eye"></i> View Canvass List</a>
        <!-- Canvass Table -->
        <table class="canvass-table" id="canvassTable">
            <thead>
                <tr>
                    <th style="width: 20%;">SUPPLIER</th>
                    <th style="width: 35%;">ITEM DESCRIPTION</th>
                    <th style="width: 15%;">QUANTITY</th>
                    <th style="width: 15%;">UNIT COST</th>
                    <th style="width: 15%;">TOTAL COST</th>
                </tr>
            </thead>
            <tbody id="canvassTableBody">
                <!-- Dynamic rows will be added here -->
                <tr style="background-color: var(--primary-green); color: white; font-weight: bold;">
                    <td colspan="4" style="text-align: right; padding-right: 20px;">GRAND TOTAL:</td>
                    <td id="grandTotal">₱0.00</td>
                </tr>
            </tbody>
        </table>

       

        <!-- Row Management Buttons -->
        <div class="row-management" style="margin: 20px 0; text-align: center;">
            <button type="button" class="btn-canvass btn-success" onclick="addRow()">
                <i class="fas fa-plus"></i> Add Row
            </button>
            <button type="button" class="btn-danger btn-canvass" onclick="removeLastRow()">
                <i class="fas fa-minus"></i> Remove Row
            </button>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"><?= htmlspecialchars(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? '')) ?></div>
                <div class="signature-title">Canvassed By:</div>
                <div class="signature-subtitle"><?= htmlspecialchars($_SESSION['user']['position'] ?? 'Purchasing Officer') ?></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button type="button" class="btn-canvass btn-secondary" onclick="clearForm()">
                <i class="fas fa-undo"></i> Clear
            </button>
            <button type="button" class="btn-canvass btn-success" onclick="printCanvass()">
                <i class="fas fa-print"></i> Print
            </button>
            <button type="button" class="btn-canvass btn-primary" onclick="saveCanvass()">
                <i class="fas fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<script>
    // Calculate row total when quantity or unit cost changes
    function calculateRowTotal(input) {
        const row = input.closest('tr');
        const quantity = parseFloat(row.cells[2].querySelector('input').value) || 0;
        const unitCost = parseFloat(row.cells[3].querySelector('input').value) || 0;
        const totalCost = quantity * unitCost;
        
        row.cells[4].textContent = '₱' + totalCost.toFixed(2);
        
        calculateGrandTotal();
    }

    // Calculate grand total
    function calculateGrandTotal() {
        const rows = document.querySelectorAll('#canvassTable tbody tr:not(:last-child)');
        let total = 0;
        
        rows.forEach(row => {
            const totalText = row.cells[4].textContent.replace('₱', '').replace(',', '');
            const amount = parseFloat(totalText) || 0;
            total += amount;
        });
        
        document.getElementById('grandTotal').textContent = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Clear form
    function clearForm() {
        if (confirm('Are you sure you want to clear all data?')) {
            // Reset date to today
            document.getElementById('canvassDate').value = '<?= date('Y-m-d') ?>';
            
            // Clear all selects
            const selects = document.querySelectorAll('#canvassTable select');
            selects.forEach(select => select.value = '');
            
            // Clear all inputs
            const inputs = document.querySelectorAll('#canvassTable input');
            inputs.forEach(input => input.value = '');
            
            // Clear all textareas
            const textareas = document.querySelectorAll('#canvassTable textarea');
            textareas.forEach(textarea => textarea.value = '');
            
            // Reset total cells
            const totalCells = document.querySelectorAll('.total-cost-cell');
            totalCells.forEach(cell => cell.textContent = '₱0.00');
            
            // Reset grand total
            document.getElementById('grandTotal').textContent = '₱0.00';
        }
    }

    // Print canvass
    function printCanvass() {
        // Hide completely empty rows to save space during print
        const rows = Array.from(document.querySelectorAll('#canvassTable tbody tr'));
        const modifiedRows = [];
        rows.forEach(row => {
            // Skip the grand total row
            if (row.querySelector('#grandTotal')) return;

            const supplier = row.cells[0]?.querySelector('select')?.value?.trim() || '';
            const description = row.cells[1]?.querySelector('textarea')?.value?.trim() || '';
            const qty = row.cells[2]?.querySelector('input')?.value?.trim() || '';
            const unit = row.cells[3]?.querySelector('input')?.value?.trim() || '';
            const totalText = (row.cells[4]?.textContent || '').replace('₱','').replace(/,/g,'').trim();
            const total = parseFloat(totalText) || 0;

            if (!supplier && !description && !qty && !unit && total === 0) {
                row.classList.add('print-hide');
                modifiedRows.push(row);
            }
        });

        const restore = () => {
            modifiedRows.forEach(r => r.classList.remove('print-hide'));
        };

        // Ensure restoration after printing
        const afterPrint = () => {
            restore();
            window.removeEventListener('afterprint', afterPrint);
        };
        window.addEventListener('afterprint', afterPrint);

        window.print();
    }

    // Save canvass
    function saveCanvass() {
        const items = [];
        const rows = document.querySelectorAll('#canvassTable tbody tr:not(:last-child)');
        rows.forEach((row, index) => {
            const supplier = row.cells[0].querySelector('select').value;
            const description = row.cells[1].querySelector('textarea').value;
            const quantity = parseFloat(row.cells[2].querySelector('input').value) || 0;
            const unit_cost = parseFloat(row.cells[3].querySelector('input').value) || 0;
            
            if (supplier && description && (quantity > 0 || unit_cost > 0)) {
                items.push({
                    supplier: supplier,
                    description: description,
                    quantity: quantity,
                    unit_cost: unit_cost
                });
            }
        });
        
        if (items.length === 0) {
            alert('Please add at least one item to the canvass');
            return;
        }
        
        const canvassDate = document.getElementById('canvassDate').value;
        
        // Show loading state
        const saveBtn = document.querySelector('.btn-primary');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveBtn.disabled = true;
        
        // Save canvass data directly
        const data = {
            canvass_date: canvassDate,
            items: items
        };
        
        <?php if ($edit_mode && $canvass_data): ?>
        data.canvass_id = <?= $canvass_data['canvass_id'] ?>;
        data.edit_mode = true;
        <?php endif; ?>
        
        fetch('../actions/save_canvass.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                <?php if ($edit_mode): ?>
                alert('Canvass updated successfully!');
                window.location.href = 'canvass_form_list.php';
                <?php else: ?>
                alert('Canvass saved successfully!\nCanvass ID: ' + result.canvass_id);
                location.reload();
                <?php endif; ?>
            } else {
                alert('Failed to save canvass: ' + result.message);
            }
        })
        .catch(error => {
            alert('Error saving canvass: ' + error.message);
        })
        .finally(() => {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }

    // Auto-resize textarea function
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.max(textarea.scrollHeight, 20) + 'px';
    }

    // Add new row to the table
    function addRow() {
        const tbody = document.getElementById('canvassTableBody');
        const grandTotalRow = tbody.lastElementChild; // Get the grand total row
        
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select onchange="calculateRowTotal(this)">
                    <option value="">Select Supplier</option>
                    <?php foreach ($suppliers_array as $supplier): ?>
                        <option value="<?= htmlspecialchars($supplier['supplier_name']) ?>">
                            <?= htmlspecialchars($supplier['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><textarea placeholder="Enter item description" onchange="calculateRowTotal(this)" oninput="autoResize(this)" style="resize: none; overflow: hidden; min-height: 20px; width: 100%; border: none; background: transparent; text-align: left; padding: 5px;"></textarea></td>
            <td><input type="number" placeholder="0" onchange="calculateRowTotal(this)" min="0"></td>
            <td><input type="number" placeholder="0.00" onchange="calculateRowTotal(this)" min="0" step="0.01"></td>
            <td class="total-cost-cell">₱0.00</td>
        `;
        
        // Insert before the grand total row
        tbody.insertBefore(newRow, grandTotalRow);
    }

    // Add row with existing data for edit mode
    function addRowWithData(supplier, description, quantity, unitCost) {
        const tbody = document.getElementById('canvassTableBody');
        const grandTotalRow = tbody.lastElementChild; // Get the grand total row
        
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select onchange="calculateRowTotal(this)">
                    <option value="">Select Supplier</option>
                    <?php foreach ($suppliers_array as $supplier): ?>
                        <option value="<?= htmlspecialchars($supplier['supplier_name']) ?>">
                            <?= htmlspecialchars($supplier['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><textarea placeholder="Enter item description" onchange="calculateRowTotal(this)" oninput="autoResize(this)" style="resize: none; overflow: hidden; min-height: 20px; width: 100%; border: none; background: transparent; text-align: left; padding: 5px;"></textarea></td>
            <td><input type="number" placeholder="0" onchange="calculateRowTotal(this)" min="0"></td>
            <td><input type="number" placeholder="0.00" onchange="calculateRowTotal(this)" min="0" step="0.01"></td>
            <td class="total-cost-cell">₱0.00</td>
        `;
        
        // Insert before the grand total row
        tbody.insertBefore(newRow, grandTotalRow);
        
        // Populate the row with existing data
        const select = newRow.cells[0].querySelector('select');
        const textarea = newRow.cells[1].querySelector('textarea');
        const quantityInput = newRow.cells[2].querySelector('input');
        const unitCostInput = newRow.cells[3].querySelector('input');
        
        select.value = supplier;
        textarea.value = description;
        quantityInput.value = quantity;
        unitCostInput.value = unitCost;
        
        // Calculate and display total
        const totalCost = quantity * unitCost;
        newRow.cells[4].textContent = '₱' + totalCost.toFixed(2);
        
        // Auto-resize textarea
        autoResize(textarea);
        
        // Recalculate grand total
        calculateGrandTotal();
    }
    
    // Remove last row from the table
    function removeLastRow() {
        const tbody = document.getElementById('canvassTableBody');
        const rows = tbody.querySelectorAll('tr:not(:last-child)'); // Exclude grand total row
        
        if (rows.length > 0) {
            rows[rows.length - 1].remove();
            calculateGrandTotal();
        }
    }
    
    // Initialize with one empty row or load existing data
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($edit_mode && !empty($canvass_items)): ?>
            // Load existing canvass items
            <?php foreach ($canvass_items as $item): ?>
                addRowWithData(
                    '<?= htmlspecialchars($item['supplier_name']) ?>',
                    '<?= htmlspecialchars($item['item_description']) ?>',
                    <?= $item['quantity'] ?>,
                    <?= $item['unit_cost'] ?>
                );
            <?php endforeach; ?>
        <?php else: ?>
            addRow();
        <?php endif; ?>
    });
</script>

<style media="print">
    @page {
        size: auto; /* Auto-size based on content */
        margin: 0; /* No margins - use full paper size */
    }
    
    body {
        font-family: Arial, sans-serif;
        font-size: 9pt; /* slightly smaller overall text */
        line-height: 1.2;
        margin: 0;
        padding: 0;
        background: white;
        color: black;
    }
    
    .sidebar, .action-buttons, .content-header, .no-print, .row-management .view-button {
        display: none !important;
    }

    .view-button{
        display: none !important;
    }
    
    .canvass-container {
        width: 100%;
        margin: 0;
        padding: 0.2cm; /* tighter container padding */
        box-shadow: none;
        border: 0.4px solid #bbb; /* thinner border */
        page-break-after: avoid;
        page-break-inside: avoid;
    }
    
    .canvass-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.2cm; /* reduce spacing */
        padding-bottom: 0.1cm;
        border-bottom: 0.5px solid #000; /* thinner divider */
    }
    
    .canvass-title {
        font-size: 11pt; /* smaller title for print */
        font-weight: bold;
        margin: 0;
    }
    
    .canvass-info {
        text-align: right;
    }
    
    .canvass-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* keep cells compact */
        margin: 0.2cm 0; /* reduce vertical margin */
        font-size: 7pt; /* more compact font size */
        page-break-inside: avoid;
    }
    
    .canvass-table th, 
    .canvass-table td {
        border: 0.3px solid #888; /* thinner cell borders */
        padding: 0 1px; /* tighter cell padding */
        text-align: left;
    }

    .canvass-table tr {
        height: 10px !important;  /* reduce row height more */
        line-height: 1 !important;
    }
    
    .canvass-table th {
        background-color: #fff; /* remove shading to look thinner */
        font-weight: 600;
        text-align: center;
        padding: 1px 1px; /* thinner header cells */
        font-size: 7pt; /* smaller header text */
    }
    
    .canvass-table input,
    .canvass-table select,
    .canvass-table textarea {
        width: 100%;
        border: none;
        background: transparent;
        font-size: 7.5pt; /* smaller form control text */
        padding: 0 1px; /* minimal padding */
        line-height: 1.05;
        height: 12px; /* compress control height */
    }

    /* Make selects visually thinner in print */
    .canvass-table select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: none !important;
        height: 12px; /* align with row height */
    }

    /* Make the grand total row minimal in print */
    .canvass-table tbody tr:last-child {
        background: #fff !important;
        color: #000 !important;
    }
    .canvass-table tbody tr:last-child td {
        font-weight: 600;
        padding: 0 1px !important;
        border-top: 0.3px solid #000;
    }
    
    .signature-section {
        margin-top: 0.5cm; /* tighter space before signatures */
        display: flex;
        justify-content: space-between;
    }
    
    .signature-box {
        text-align: center;
        width: 48%;
    }
    
    .signature-line {
        border-top: none; /* Remove underline in print */
        margin: 0.3cm 0;
        padding-top: 0.3cm;
    }
    
    .print-only {
        display: block !important;
    }
    
    .main-content {
        margin-left: 0 !important;
    }
    
    .canvass-container {
        box-shadow: none !important;
        padding: 8px !important; /* more compact */
    }
    
    body {
        background: white !important;
    }

    /* Hide helper class applied before printing */
    .print-hide { display: none !important; }
</style>

<?php include '../includes/footer.php'; ?>
