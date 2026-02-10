<?php
$pageTitle = 'Purchase Order';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$user_type = $_SESSION['user_type'] ?? '';
$user_id = $_SESSION['user']['id'] ?? 0;

// Check if we're in edit mode
$edit_mode = isset($_GET['edit']) && is_numeric($_GET['edit']);
$po_data = null;
$po_items = [];

if ($edit_mode) {
    $po_id = intval($_GET['edit']);

    // Fetch purchase order data
    $po_query = "SELECT * FROM purchase_orders WHERE po_id = ?";
    $stmt = $conn->prepare($po_query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    $po_result = $stmt->get_result();

    if ($po_result->num_rows > 0) {
        $po_data = $po_result->fetch_assoc();

        // Fetch purchase order items
        $items_query = "SELECT * FROM purchase_order_items WHERE po_id = ? ORDER BY item_number ASC";
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param("i", $po_id);
        $stmt->execute();
        $items_result = $stmt->get_result();

        while ($item = $items_result->fetch_assoc()) {
            $po_items[] = $item;
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'save_po':
            $result = savePurchaseOrder($_POST, $conn, $user_id);
            echo json_encode($result);
            exit;

        case 'load_po':
            $result = loadPurchaseOrder($_POST['po_id'], $conn);
            echo json_encode($result);
            exit;

        case 'generate_po_number':
            $result = generatePONumber($conn);
            echo json_encode($result);
            exit;
    }
}

// Function to generate next PO number
function generatePONumber($conn)
{
    $year = date('Y');
    $query = "SELECT po_number FROM purchase_orders WHERE po_number LIKE 'PO-$year-%' ORDER BY po_number DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $lastPO = $result->fetch_assoc()['po_number'];
        $lastNumber = intval(substr($lastPO, -3));
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }

    return ['success' => true, 'po_number' => "PO-$year-$newNumber"];
}

// Function to save purchase order
function savePurchaseOrder($data, $conn, $user_id)
{
    try {
        $conn->begin_transaction();

        // Prepare main PO data
        $po_number = $conn->real_escape_string($data['po_number']);
        $po_date = $conn->real_escape_string($data['po_date']);
        $supplier_name = $conn->real_escape_string($data['supplier_name']);
        $supplier_address = $conn->real_escape_string($data['supplier_address']);
        $payment_method = $conn->real_escape_string($data['payment_method'] ?? 'Check');
        $payment_details = $conn->real_escape_string($data['payment_details'] ?? '');
        $cash_amount = floatval($data['cash_amount'] ?? 0);
        $total_amount = floatval($data['total_amount'] ?? 0);
        $notes = $conn->real_escape_string($data['notes'] ?? '');

        // Check if PO exists
        $po_id = null;
        if (isset($data['po_id']) && !empty($data['po_id'])) {
            $po_id = intval($data['po_id']);

            // Update existing PO
            $sql = "UPDATE purchase_orders SET 
                    po_date = '$po_date',
                    supplier_name = '$supplier_name',
                    supplier_address = '$supplier_address',
                    payment_method = '$payment_method',
                    payment_details = '$payment_details',
                    cash_amount = $cash_amount,
                    total_amount = $total_amount,
                    notes = '$notes',
                    updated_at = NOW()
                    WHERE po_id = $po_id";
        } else {
            // Insert new PO
            $sql = "INSERT INTO purchase_orders (
                    po_number, po_date, supplier_name, supplier_address,
                    payment_method, payment_details, cash_amount, total_amount,
                    notes, created_by
                    ) VALUES (
                    '$po_number', '$po_date', '$supplier_name', '$supplier_address',
                    '$payment_method', '$payment_details', $cash_amount, $total_amount,
                    '$notes', $user_id
                    )";
        }

        if (!$conn->query($sql)) {
            throw new Exception('Failed to save purchase order: ' . $conn->error);
        }

        if (!$po_id) {
            $po_id = $conn->insert_id;
        }

        // Delete existing items for update
        $conn->query("DELETE FROM purchase_order_items WHERE po_id = $po_id");

        // Insert items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                if (!empty($item['description'])) {
                    $item_number = $index + 1;
                    $description = $conn->real_escape_string($item['description']);
                    $quantity = floatval($item['quantity'] ?? 0);
                    $unit_cost = floatval($item['unit_cost'] ?? 0);
                    $line_total = $quantity * $unit_cost;

                    $item_sql = "INSERT INTO purchase_order_items 
                                (po_id, item_number, item_description, quantity, unit_cost, line_total)
                                VALUES ($po_id, $item_number, '$description', $quantity, $unit_cost, $line_total)";

                    if (!$conn->query($item_sql)) {
                        throw new Exception('Failed to save item: ' . $conn->error);
                    }
                }
            }
        }

        // Update total amount based on items
        $conn->query("UPDATE purchase_orders SET total_amount = (
            SELECT COALESCE(SUM(line_total), 0) FROM purchase_order_items WHERE po_id = $po_id
        ) WHERE po_id = $po_id");

        $conn->commit();
        return ['success' => true, 'message' => 'Purchase order saved successfully', 'po_id' => $po_id];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to load purchase order
function loadPurchaseOrder($po_id, $conn)
{
    try {
        $po_id = intval($po_id);

        // Get PO data
        $po_sql = "SELECT * FROM purchase_orders WHERE po_id = $po_id";
        $po_result = $conn->query($po_sql);

        if (!$po_result || $po_result->num_rows === 0) {
            return ['success' => false, 'message' => 'Purchase order not found'];
        }

        $po_data = $po_result->fetch_assoc();

        // Get items
        $items_sql = "SELECT * FROM purchase_order_items WHERE po_id = $po_id ORDER BY item_number";
        $items_result = $conn->query($items_sql);

        $items = [];
        if ($items_result) {
            while ($item = $items_result->fetch_assoc()) {
                $items[] = $item;
            }
        }

        return [
            'success' => true,
            'po_data' => $po_data,
            'items' => $items
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Get existing POs for dropdown
$existing_pos_sql = "SELECT po_id, po_number, supplier_name, po_date, status FROM purchase_orders ORDER BY created_at DESC LIMIT 20";
$existing_pos_result = $conn->query($existing_pos_sql);
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

    /* Purchase Order Form */
    .po-container {
        background: var(--text-white);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
        padding: 40px;
    }

    .po-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--primary-green);
        padding-bottom: 20px;
    }

    .po-title {
        color: var(--primary-green);
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    .po-info {
        text-align: right;
    }

    .po-info-item {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .po-info-label {
        font-weight: 600;
        margin-right: 10px;
        color: var(--text-dark);
        min-width: 80px;
    }

    .po-info-input {
        border: none;
        border-bottom: 1px solid #ccc;
        padding: 5px 10px;
        font-size: 1rem;
        min-width: 150px;
        background: transparent;
    }

    .po-info-input:focus {
        outline: none;
        border-bottom-color: var(--primary-green);
    }

    .po-details {
        margin-bottom: 30px;
    }

    .po-details-row {
        display: flex;
        margin-bottom: 15px;
        align-items: center;
    }

    .po-details-label {
        font-weight: 600;
        color: var(--text-dark);
        min-width: 100px;
        margin-right: 15px;
    }

    .po-details-input {
        border: none;
        border-bottom: 1px solid #ccc;
        padding: 8px 10px;
        font-size: 1rem;
        flex: 1;
        background: transparent;
    }

    .po-details-input:focus {
        outline: none;
        border-bottom-color: var(--primary-green);
    }

    /* Table Styles */
    .po-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .po-table th {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: var(--text-white);
        padding: 15px 10px;
        text-align: center;
        font-weight: 600;
        border: 1px solid var(--primary-green);
    }

    .po-table td {
        padding: 12px 10px;
        border: 1px solid #ddd;
        text-align: center;
        vertical-align: middle;
    }

    .po-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .po-table tbody tr:hover {
        background-color: rgba(7, 59, 29, 0.05);
    }

    .po-table input {
        border: none;
        background: transparent;
        width: 100%;
        text-align: center;
        padding: 5px;
    }

    .po-table input:focus {
        outline: 1px solid var(--primary-green);
        background: white;
    }

    /* Payment Section */
    .payment-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .payment-item {
        display: flex;
        align-items: center;
    }

    .payment-label {
        font-weight: 600;
        margin-right: 10px;
        color: var(--text-dark);
    }

    .payment-input {
        border: none;
        border-bottom: 1px solid #ccc;
        padding: 5px 10px;
        font-size: 1rem;
        background: transparent;
        min-width: 150px;
    }

    .payment-input:focus {
        outline: none;
        border-bottom-color: var(--primary-green);
    }

    /* Signature Section */
    .signature-section {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 40px;
        margin-top: 50px;
    }

    .signature-box {
        text-align: center;
    }

    .signature-line {
        border-bottom: 1px solid #333;
        height: 60px;
        margin-bottom: 10px;
        position: relative;
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

    .btn-po {
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
        background-color: #1e7e34;
        transform: translateY(-2px);
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

        .po-container {
            padding: 20px;
        }

        .po-header {
            flex-direction: column;
            text-align: center;
        }

        .po-info {
            text-align: left;
            margin-top: 20px;
        }

        .signature-section {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .payment-section {
            flex-direction: column;
            gap: 15px;
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
            <li><a href="purchase_order.php" class="nav-link active">
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
    <div class="content-header page-title">
        <h1>Purchase Order</h1>
        <p>Create and manage purchase orders for procurement</p>
    </div>

    <!-- Purchase Order Form -->
    <div class="po-container">
        <div class="po-header">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 class="po-title" style="margin-bottom: 10px;">PURCHASE ORDER</h2>
            </div>
            <div class="po-info">
                <div class="po-info-item">
                    <span class="po-info-label">PO No.:</span>
                    <input type="text" class="po-info-input" id="poNumber" placeholder="Enter PO Number" value="<?= $edit_mode && $po_data ? htmlspecialchars($po_data['po_number']) : '' ?>">
                </div>
                <div class="po-info-item">
                    <span class="po-info-label">Date:</span>
                    <input type="date" class="po-info-input" id="poDate" value="<?= $edit_mode && $po_data ? $po_data['po_date'] : date('Y-m-d') ?>">
                </div>
            </div>
        </div>
        <a href="purchase_order_list.php" class="btn view-button mb-3 text-dark" style="background-color: var(--accent-orange); color: white; text-decoration: none; padding: 8px 16px; border-radius: 5px; display: inline-block; font-size: 14px;"><i class="fas fa-eye"></i> View Purchase Order List</a>

        <div class="po-details">
            <div class="po-details-row">
                <span class="po-details-label">TO:</span>
                <select class="po-details-input" id="supplierName" onchange="updateSupplierAddress()">
                    <option value="">Select Supplier</option>
                </select>
            </div>
            <div class="po-details-row">
                <span class="po-details-label">ADDRESS:</span>
                <input type="text" class="po-details-input" id="supplierAddress" placeholder="Enter Supplier Address" value="<?= $edit_mode && $po_data ? htmlspecialchars($po_data['supplier_address']) : '' ?>">
            </div>
        </div>

        <!-- Items Table -->
        <table class="po-table" id="itemsTable">
            <thead>
                <tr>
                    <th style="width: 8%;">NO</th>
                    <th style="width: 40%;">ITEM DESCRIPTION</th>
                    <th style="width: 15%;">QUANTITY</th>
                    <th style="width: 17%;">UNIT COST</th>
                    <th style="width: 20%;">AMOUNT</th>
                </tr>
            </thead>
            <tbody id="itemsTableBody">
                <!-- Dynamic rows will be added here -->
                <tr style="background-color: var(--primary-green); color: white; font-weight: bold;">
                    <td colspan="4" style="text-align: right; padding-right: 20px;">TOTAL AMOUNT:</td>
                    <td id="totalAmount">₱0.00</td>
                </tr>
            </tbody>
        </table>

        <!-- Row Management Buttons -->
        <div class="row-management" style="margin: 20px 0; text-align: center;">
            <button type="button" class="btn-po btn-success" onclick="addPORow()">
                <i class="fas fa-plus"></i> Add Row
            </button>
            <button type="button" class="btn-po btn-danger" style="background-color: var(--accent-red); color: white;" onclick="removeLastPORow()">
                <i class="fas fa-minus"></i> Remove Row
            </button>
        </div>

        <!-- Payment Section -->
        <div class="payment-section">
            <div class="payment-item">
                <span class="payment-label">Payment Thru: Check</span>
                <input type="text" class="payment-input" placeholder="Enter Check Details Here">
            </div>
            <div class="payment-item">
                <span class="payment-label">Cash:</span>
                <input type="text" class="payment-input" placeholder="PHP 0.00">
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Marilou Suarez</div>
                <div class="signature-title">Prepared By:</div>
                <div class="signature-subtitle">Purchasing Officer</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Checked By:</div>
                <div class="signature-subtitle">Finance Officer</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Approved By:</div>
                <div class="signature-subtitle">VP for Finance and Administration</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button type="button" class="btn-po btn-secondary" onclick="clearForm()">
                <i class="fas fa-undo"></i> Clear
            </button>
            <button type="button" class="btn-po btn-success" onclick="printPO()">
                <i class="fas fa-print"></i> Print
            </button>
            <button type="button" class="btn-po btn-primary" onclick="savePO()">
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
        const amount = quantity * unitCost;

        row.cells[4].textContent = '₱' + amount.toFixed(2);

        calculateGrandTotal();
    }

    // Calculate grand total
    function calculateGrandTotal() {
        const rows = document.querySelectorAll('#itemsTable tbody tr:not(:last-child)');
        let total = 0;

        rows.forEach(row => {
            const amountText = row.cells[4].textContent.replace('₱', '').replace(',', '');
            const amount = parseFloat(amountText) || 0;
            total += amount;
        });

        document.getElementById('totalAmount').textContent = '₱' + total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Clear form
    function clearForm() {
        if (confirm('Are you sure you want to clear all data?')) {
            document.getElementById('poNumber').value = '';
            document.getElementById('poDate').value = '<?= date('Y-m-d') ?>';
            document.getElementById('supplierName').value = '';
            document.getElementById('supplierAddress').value = '';

            const inputs = document.querySelectorAll('#itemsTable input');
            inputs.forEach(input => input.value = '');

            const amountCells = document.querySelectorAll('.amount-cell');
            amountCells.forEach(cell => cell.textContent = '₱0.00');

            document.getElementById('totalAmount').textContent = '₱0.00';
        }
    }

    // Print PO
    function printPO() {
        // Hide completely empty rows to save space during print
        const rows = Array.from(document.querySelectorAll('#itemsTable tbody tr:not(:last-child)'));
        const modifiedRows = [];

        rows.forEach(row => {
            const description = row.cells[1].querySelector('input')?.value.trim() || '';
            const qty = row.cells[2].querySelector('input')?.value.trim() || '';
            const unitCost = row.cells[3].querySelector('input')?.value.trim() || '';
            const amount = row.cells[4].textContent.replace('₱', '').trim();
            const total = parseFloat(amount) || 0;

            if (!description && !qty && !unitCost && total === 0) {
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

    // Save PO
    function savePO() {
        const poNumber = document.getElementById('poNumber').value;
        const poDate = document.getElementById('poDate').value;
        const supplierName = document.getElementById('supplierName').value;
        const supplierAddress = document.getElementById('supplierAddress').value;
        const paymentDetails = document.querySelector('.payment-input[placeholder="Enter Check Details Here"]').value;
        const cashAmount = document.querySelector('.payment-input[placeholder="PHP 0.00"]').value;

        if (!poNumber || !poDate || !supplierName) {
            alert('Please fill in all required fields (PO Number, Date, and Supplier Name)');
            return;
        }

        const items = [];
        const rows = document.querySelectorAll('#itemsTable tbody tr:not(:last-child)');
        rows.forEach((row, index) => {
            const descriptionElement = row.cells[1].querySelector('select') || row.cells[1].querySelector('input');
            const description = descriptionElement ? descriptionElement.value : '';
            const quantity = parseFloat(row.cells[2].querySelector('input').value) || 0;
            const unit_cost = parseFloat(row.cells[3].querySelector('input').value) || 0;

            if (description && (quantity > 0 || unit_cost > 0)) {
                items.push({
                    description: description,
                    quantity: quantity,
                    unit_cost: unit_cost
                });
            }
        });

        const data = {
            po_number: poNumber,
            po_date: poDate,
            supplier_name: supplierName,
            supplier_address: supplierAddress,
            payment_method: 'Check',
            payment_details: paymentDetails,
            cash_amount: parseFloat(cashAmount.replace(/[^\d.-]/g, '')) || 0,
            items: items
        };

        // Add po_id if in edit mode
        const urlParams = new URLSearchParams(window.location.search);
        const editId = urlParams.get('edit');
        if (editId) {
            data.po_id = editId;
        }

        // Debug: Log the data being sent
        console.log('Data being sent:', data);
        console.log('JSON string:', JSON.stringify(data));

        // Show loading state
        const saveBtn = document.querySelector('.btn-primary');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveBtn.disabled = true;

        fetch('../actions/save_purchase_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Purchase order saved successfully!\nPO Number: ' + (result.po_number || data.po_number));
                    // Optionally refresh the existing PO dropdown
                    location.reload();
                } else {
                    alert('Failed to save purchase order: ' + result.message);
                }
            })
            .catch(error => {
                alert('Error saving purchase order: ' + error.message);
            })
            .finally(() => {
                // Restore button state
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
    }

    // Load existing PO
    function loadExistingPO() {
        const poId = document.getElementById('existingPOSelect').value;

        if (!poId) {
            alert('Please select a purchase order to load');
            return;
        }

        fetch('../actions/load_purchase_order.php?po_id=' + poId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fill form fields
                    document.getElementById('poNumber').value = data.po_number;
                    document.getElementById('poDate').value = data.po_date;
                    document.getElementById('supplierName').value = data.supplier_name;
                    document.getElementById('supplierAddress').value = data.supplier_address || '';

                    // Fill payment details
                    if (data.payment_details) {
                        document.querySelector('.payment-input[placeholder="Enter Check Details Here"]').value = data.payment_details;
                    }
                    if (data.cash_amount > 0) {
                        document.querySelector('.payment-input[placeholder="PHP 0.00"]').value = 'PHP ' + data.cash_amount.toFixed(2);
                    }

                    // Clear existing items
                    const rows = document.querySelectorAll('#itemsTable tbody tr:not(:last-child)');
                    rows.forEach(row => {
                        row.cells[1].querySelector('input').value = '';
                        row.cells[2].querySelector('input').value = '';
                        row.cells[3].querySelector('input').value = '';
                        row.cells[4].textContent = '₱0.00';
                    });

                    // Fill items
                    data.items.forEach((item, index) => {
                        if (index < rows.length) {
                            const row = rows[index];
                            row.cells[1].querySelector('input').value = item.item_description;
                            row.cells[2].querySelector('input').value = item.quantity;
                            row.cells[3].querySelector('input').value = item.unit_cost;
                            row.cells[4].textContent = '₱' + item.line_total.toFixed(2);
                        }
                    });

                    calculateGrandTotal();
                    alert('Purchase order loaded successfully!');
                } else {
                    alert('Failed to load purchase order: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error loading purchase order: ' + error.message);
            });
    }

    // Load canvass items for dropdown
    let canvassItems = [];

    function loadCanvassItems() {
        fetch('../api/get_canvass_items.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    canvassItems = data.items;
                } else {
                    console.error('Failed to load canvass items:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading canvass items:', error);
            });
    }

    // Add new row to the purchase order table
    function addPORow() {
        const tbody = document.getElementById('itemsTableBody');
        const totalRow = tbody.lastElementChild; // Get the total row
        const rowCount = tbody.querySelectorAll('tr:not(:last-child)').length + 1;

        // Build options for the dropdown - extract unique descriptions from canvassItems
        let optionsHtml = '<option value="">Choose item from canvass list</option>';
        const uniqueDescriptions = [...new Set(canvassItems.map(item => item.description))];
        uniqueDescriptions.forEach(description => {
            optionsHtml += `<option value="${description}">${description}</option>`;
        });

        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${rowCount}</td>
            <td>
                <select class="form-select form-select-sm" onchange="calculateRowTotal(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td><input type="number" placeholder="0" onchange="calculateRowTotal(this)" min="0"></td>
            <td><input type="number" placeholder="0.00" onchange="calculateRowTotal(this)" min="0" step="0.01"></td>
            <td class="amount-cell">₱0.00</td>
        `;

        // Insert before the total row
        tbody.insertBefore(newRow, totalRow);
    }

    // Suppliers Data
    let suppliersData = [];

    // Load Suppliers
    function loadSuppliers() {
        console.log('Loading suppliers...');
        fetch('../api/get_suppliers.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    suppliersData = data.suppliers;
                    const select = document.getElementById('supplierName');
                    // Keep the default option
                    select.innerHTML = '<option value="">Select Supplier</option>';

                    data.suppliers.forEach(supplier => {
                        const option = document.createElement('option');
                        option.value = supplier.name;
                        option.textContent = supplier.name;
                        select.appendChild(option);
                    });

                    // Pre-select if in edit mode (server-side value)
                    const preselectedValue = "<?= $edit_mode && $po_data ? htmlspecialchars($po_data['supplier_name']) : '' ?>";
                    if (preselectedValue) {
                        select.value = preselectedValue;
                    }
                } else {
                    console.error('Failed to load suppliers:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading suppliers:', error);
            });
    }

    // Update Supplier Address
    function updateSupplierAddress() {
        const select = document.getElementById('supplierName');
        const selectedName = select.value;
        const addressInput = document.getElementById('supplierAddress');

        const supplier = suppliersData.find(s => s.name === selectedName);
        if (supplier) {
            addressInput.value = supplier.address;
        } else {
            addressInput.value = '';
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadSuppliers();
        loadCanvassItems();
    });

    // Auto-fill item details based on description and selected supplier
    function autoFillItemDetails(selectElement) {
        const row = selectElement.closest('tr');
        const description = selectElement.value;
        const supplierName = document.getElementById('supplierName').value;

        if (!description || !supplierName) return;

        // Find match for item + supplier
        const matchedItem = canvassItems.find(item =>
            item.description === description && item.supplier === supplierName
        );

        const quantityInput = row.cells[2].querySelector('input');
        const unitCostInput = row.cells[3].querySelector('input');

        if (matchedItem) {
            quantityInput.value = matchedItem.quantity;
            unitCostInput.value = matchedItem.unit_cost;
        } else {
            // Optional: clear if no match found for this supplier, or keep empty
            quantityInput.value = '';
            unitCostInput.value = '';
        }

        // Recalculate total immediately
        calculateRowTotal(selectElement);
    }

    // Add row with existing data for edit mode
    function addPORowWithData(itemNumber, description, quantity, unitCost) {
        const tbody = document.getElementById('itemsTableBody');
        const totalRow = tbody.lastElementChild; // Get the total row

        // Build options for the dropdown
        let optionsHtml = '<option value="">Choose items that you canvass</option>';

        // Check if the existing description is in canvass items
        let foundInCanvass = false;
        canvassItems.forEach(item => {
            const selected = item.description === description ? 'selected' : '';
            if (item.description === description) foundInCanvass = true;
            optionsHtml += `<option value="${item.description}" ${selected}>${item.description}</option>`;
        });

        // If the existing description is not in canvass items, add it as a selected option
        if (!foundInCanvass && description && description.trim() !== '') {
            optionsHtml += `<option value="${description}" selected>${description}</option>`;
        }

        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${itemNumber}</td>
            <td>
                <select class="form-select form-select-sm" onchange="calculateRowTotal(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td><input type="number" placeholder="0" onchange="calculateRowTotal(this)" min="0"></td>
            <td><input type="number" placeholder="0.00" onchange="calculateRowTotal(this)" min="0" step="0.01"></td>
            <td class="amount-cell">₱0.00</td>
        `;

        // Insert before the total row
        tbody.insertBefore(newRow, totalRow);

        // Populate the row with existing data
        const quantityInput = newRow.cells[2].querySelector('input');
        const unitCostInput = newRow.cells[3].querySelector('input');

        quantityInput.value = quantity;
        unitCostInput.value = unitCost;

        // Calculate and display total
        const lineTotal = quantity * unitCost;
        newRow.cells[4].textContent = '₱' + lineTotal.toFixed(2);

        // Recalculate grand total
        calculateGrandTotal();
    }

    // Remove last row from the purchase order table
    function removeLastPORow() {
        const tbody = document.getElementById('itemsTableBody');
        const rows = tbody.querySelectorAll('tr:not(:last-child)'); // Exclude total row

        if (rows.length > 0) {
            rows[rows.length - 1].remove();
            // Update row numbers
            updateRowNumbers();
            calculateGrandTotal();
        }
    }

    // Update row numbers after adding/removing rows
    function updateRowNumbers() {
        const tbody = document.getElementById('itemsTableBody');
        const rows = tbody.querySelectorAll('tr:not(:last-child)');

        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
        });
    }

    // Initialize with one empty row or load existing data
    document.addEventListener('DOMContentLoaded', function() {
        // Load canvass items first
        loadCanvassItems();

        <?php if ($edit_mode && !empty($po_items)): ?>
            // Wait for canvass items to load before adding existing PO items
            setTimeout(() => {
                <?php foreach ($po_items as $item): ?>
                    addPORowWithData(
                        <?= $item['item_number'] ?>,
                        '<?= htmlspecialchars($item['item_description']) ?>',
                        <?= $item['quantity'] ?>,
                        <?= $item['unit_cost'] ?>
                    );
                <?php endforeach; ?>
            }, 500);
        <?php else: ?>
            // Wait for canvass items to load before adding empty row
            setTimeout(() => {
                addPORow();
            }, 500);
        <?php endif; ?>
    });

    // Generate new PO number
    function generateNewPONumber() {
        fetch('../actions/generate_po_number.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('poNumber').value = data.po_number;
                    // Clear form for new PO
                    clearForm();
                    document.getElementById('poNumber').value = data.po_number;
                    document.getElementById('existingPOSelect').value = '';
                } else {
                    alert('Failed to generate PO number: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error generating PO number: ' + error.message);
            });
    }
    // Autofill item details and calculate total
    function onItemSelect(select) {
        const row = select.closest('tr');
        const description = select.value;
        const item = canvassItems.find(i => i.description === description);

        if (item) {
            row.cells[2].querySelector('input').value = item.quantity;
            row.cells[3].querySelector('input').value = item.unit_cost;
        } else if (description === '') {
            // Clear if empty selection
            row.cells[2].querySelector('input').value = '';
            row.cells[3].querySelector('input').value = '';
        }

        calculateRowTotal(select);
    }
</script>

<style media="print">
    @page {
        size: A4;
        margin: 0.5in;
    }

    .view-button {
        display: none !important;
    }

    .page-title {
        display: none !important;
    }

    .sidebar,
    .action-buttons,
    .row-management {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }

    .po-container {
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        font-size: 10pt;
        line-height: 1.2;
    }

    body {
        background: white !important;
        font-family: Arial, sans-serif;
        font-size: 10pt;
    }

    /* Header styling */
    .po-header {
        text-align: center;
        margin-bottom: 15px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }

    .po-header h1 {
        font-size: 14pt;
        font-weight: bold;
        margin: 0;
        text-transform: uppercase;
    }

    .po-header .college-info {
        font-size: 9pt;
        margin: 2px 0;
    }

    /* Form details section */
    .po-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 9pt;
    }

    .po-details .left-details,
    .po-details .right-details {
        width: 48%;
    }

    .po-details label {
        font-weight: bold;
        display: inline-block;
        width: 80px;
    }

    .po-details input {
        border: none;
        border-bottom: 1px solid #000;
        background: transparent;
        font-size: 9pt;
        padding: 2px;
    }

    /* Table styling */
    .po-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 9pt;
    }

    .po-table th,
    .po-table td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
        vertical-align: top;
    }

    .po-table th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
        font-size: 8pt;
        text-transform: uppercase;
    }

    .po-table .qty-col,
    .po-table .unit-cost-col,
    .po-table .amount-col {
        text-align: right;
        width: 12%;
    }

    .po-table .item-col {
        width: 40%;
    }

    .po-table .unit-col {
        width: 12%;
        text-align: center;
    }

    .po-table .no-col {
        width: 8%;
        text-align: center;
    }

    .po-table input {
        border: none;
        background: transparent;
        width: 100%;
        font-size: 9pt;
        padding: 2px;
    }

    /* Total row styling */
    .po-table .total-row {
        background-color: #f0f0f0 !important;
        font-weight: bold;
    }

    .po-table .total-row td {
        text-align: right;
        font-size: 10pt;
    }

    /* Signature section */
    .signature-section {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        font-size: 9pt;
    }

    .signature-box {
        width: 30%;
        text-align: center;
    }

    .signature-line {
        border-top: 1px solid #000;
        margin-top: 30px;
        padding-top: 5px;
        font-weight: bold;
    }

    .signature-title {
        font-size: 8pt;
        margin-top: 2px;
    }

    /* Payment section */
    .payment-section {
        margin-top: 15px;
        font-size: 9pt;
        border: 1px solid #000;
        padding: 8px;
    }

    .payment-section h4 {
        margin: 0 0 8px 0;
        font-size: 10pt;
        text-transform: uppercase;
    }

    .payment-details {
        display: flex;
        justify-content: space-between;
    }

    .payment-details .payment-item {
        margin-right: 20px;
    }

    .payment-details label {
        font-weight: bold;
        margin-right: 5px;
    }

    .payment-details input {
        border: none;
        border-bottom: 1px solid #000;
        background: transparent;
        font-size: 9pt;
        padding: 2px;
        width: 120px;
    }

    /* Compact spacing for one-page fit */
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        margin: 5px 0;
    }

    p {
        margin: 3px 0;
    }

    .form-group {
        margin-bottom: 8px;
    }

    /* Hide empty rows in print */
    .print-hide {
        display: none !important;
    }
</style>

<?php include '../includes/footer.php'; ?>