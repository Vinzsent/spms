<?php
$pageTitle = 'Employee Inventory List';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Get user information from session with multiple fallbacks
$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['user']['id'] ?? '';

// Check if user is Supply In-charge or Admin
$allowed_roles = ['property custodian', 'admin'];
if (!in_array(strtolower(trim($user_type)), $allowed_roles)) {
    echo '<div class="container mt-5"><div class="alert alert-danger" role="alert">
          <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Access Denied</h4>
          <p>You do not have permission to view this page. This page is restricted to the <strong>Property Custodian</strong> and <strong>Admin</strong> roles.</p>
          <hr>
          <button class="btn btn-primary" onclick="window.history.back()">Go Back</button>
        </div></div>';
    include '../includes/footer.php';
    exit;
}

$office = $_GET['office'] ?? '';
if (empty($office)) {
    echo "<script>alert('Invalid office specified.'); window.location.href='employee_inventory.php';</script>";
    exit;
}

// Fetch all users associated with this department/office directly from user table
$employees = [];
$user_ids = [];

$user_query = "
    SELECT id, first_name, last_name, user_type, username, email 
    FROM user 
    WHERE department = ?
";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $office);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $fullname = trim($row['first_name'] . ' ' . $row['last_name']);
    $employees[$row['id']] = [
        'id' => $row['id'],
        'fullname' => $fullname,
        'role' => $row['user_type'],
        'email' => $row['email'] ?? ($row['username'] . '@dcc.edu.ph'),
        'username' => $row['username'],
        'department' => $office,
        'inventory' => []
    ];
    $user_ids[] = $row['id'];
}
$stmt->close();

// Fetch inventory items for each user in this department
if (!empty($user_ids)) {
    // 1. Get from supply_request
    $supply_query = "
        SELECT user_id, item_name, category, brand, color, size, type, quantity_requested, quality_issued, status, date_requested
        FROM supply_request
        WHERE user_id IN (" . implode(',', array_map('intval', $user_ids)) . ")
    ";
    $stmt = $conn->prepare($supply_query);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $u_id = $row['user_id'];
        $qty = $row['quality_issued'] ?: $row['quantity_requested'];
        $employees[$u_id]['inventory'][] = [
            'item' => $row['item_name'] . ($row['brand'] ? ' (' . $row['brand'] . ')' : ''),
            'category' => $row['category'] ?: 'Supplies',
            'status' => $row['status'] ?: 'Issued',
            'assigned' => $qty . ' ' . ($row['unit'] ?: 'pcs') . ($row['size'] ? ' [' . $row['size'] . ']' : '')
        ];
    }
    $stmt->close();

    // 2. Get from borrowers_forms
    $borrow_query = "
        SELECT user_id, Item_name, deparment, purpose, date, quantity, remarks
        FROM borrowers_forms
        WHERE user_id IN (" . implode(',', array_map('intval', $user_ids)) . ")
    ";
    $stmt = $conn->prepare($borrow_query);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $u_id = $row['user_id'];
        $employees[$u_id]['inventory'][] = [
            'item' => $row['Item_name'] . ($row['remarks'] ? ' - ' . $row['remarks'] : ''),
            'category' => 'Property',
            'status' => 'Borrowed',
            'assigned' => $row['quantity'] . ' pcs (' . $row['date'] . ')'
        ];
    }
    $stmt->close();

    // 3. Get from employee_inventory
    $emp_inv_query = "
        SELECT id, user_id, item_type, item_name, category, brand, color, size, type, serial_number, quantity, unit, date_issued, status, remarks
        FROM employee_inventory
        WHERE user_id IN (" . implode(',', array_map('intval', $user_ids)) . ")
    ";
    $stmt = $conn->prepare($emp_inv_query);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $u_id = $row['user_id'];
        $item_details = $row['item_name'];
        if (!empty($row['brand'])) $item_details .= ' (' . $row['brand'] . ')';
        if (!empty($row['serial_number'])) $item_details .= ' [S/N: ' . $row['serial_number'] . ']';
        if (!empty($row['remarks'])) $item_details .= ' - ' . $row['remarks'];

        $employees[$u_id]['inventory'][] = [
            'emp_inv_id'    => $row['id'],          // id from employee_inventory for edit/delete
            'editable'      => true,
            'item'          => $item_details,
            'category'      => $row['category'] ?: ($row['item_type'] == 'Supply' ? 'Supply' : 'Property'),
            'status'        => $row['status'] ?: 'Issued',
            'assigned'      => $row['quantity'] . ' ' . ($row['unit'] ?: 'pcs') . ($row['date_issued'] ? ' (' . $row['date_issued'] . ')' : ''),
            // raw fields for edit form
            'raw_item_type'     => $row['item_type'],
            'raw_item_name'     => $row['item_name'],
            'raw_brand'         => $row['brand'],
            'raw_size'          => $row['size'],
            'raw_color'         => $row['color'],
            'raw_type'          => $row['type'],
            'raw_category'      => $row['category'],
            'raw_serial_number' => $row['serial_number'],
            'raw_quantity'      => $row['quantity'],
            'raw_unit'          => $row['unit'],
            'raw_date_issued'   => $row['date_issued'],
            'raw_status'        => $row['status'],
            'raw_remarks'       => $row['remarks'],
        ];
    }
    $stmt->close();
}

$employees_list = array_values($employees);
?>

<?php include('../includes/navbar.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($office) ?> - Employee Directory</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        /* ── Reset & Base ── */
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            background: #f6f9fc;
            color: #1a2634;
            min-height: 100vh;
            padding: 0;
        }

        /* ── Sidebar + main wrapper ── */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar container space reservation for desktop */
        .sidebar-area {
            width: 256px;
            flex-shrink: 0;
        }

        /* ── Main content area ── */
        .main-content {
            flex: 1;
            padding: 2rem 1.5rem;
            margin-top: 65px; /* Pushes content down past the fixed top navbar */
            overflow-x: hidden;
        }

        /* ── Container (for the directory content) ── */
        .container {
            max-width: 1360px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .header-text {
            display: flex;
            flex-direction: column;
        }

        .header-left h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #073b1d, #1a5f3c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }

        .header-left .subtitle {
            font-size: 0.95rem;
            color: #5e6f8d;
            margin-top: 0.2rem;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-left .subtitle span {
            background: rgba(7, 59, 29, 0.1);
            padding: 0.15rem 0.8rem;
            border-radius: 40px;
            font-weight: 600;
            color: #073b1d;
            font-size: 0.8rem;
            -webkit-text-fill-color: #073b1d;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ffffff;
            border: 1px solid #dce5ef;
            border-radius: 60px;
            padding: 0.5rem 1.2rem;
            font-family: "Inter", sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1a2634;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            white-space: nowrap;
        }

        .back-btn i {
            font-size: 0.85rem;
            color: #073b1d;
            transition: transform 0.2s ease;
        }

        .back-btn:hover {
            background: rgba(7, 59, 29, 0.05);
            border-color: #073b1d;
            box-shadow: 0 4px 12px rgba(7, 59, 29, 0.06);
        }

        .back-btn:hover i {
            transform: translateX(-3px);
        }

        .back-btn:active {
            transform: scale(0.96);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: #ffffff;
            border-radius: 60px;
            padding: 0.1rem 0.1rem 0.1rem 1.2rem;
            box-shadow: 0 4px 12px rgba(0, 20, 40, 0.06);
            border: 1px solid #e2eaf2;
            transition: box-shadow 0.2s, border-color 0.2s;
            min-width: 220px;
        }

        .search-wrapper:focus-within {
            border-color: #073b1d;
            box-shadow: 0 4px 16px rgba(7, 59, 29, 0.12);
        }

        .search-wrapper i {
            color: #073b1d;
            font-size: 0.95rem;
        }

        .search-wrapper input {
            border: none;
            background: transparent;
            padding: 0.7rem 0.8rem;
            font-size: 0.9rem;
            font-family: "Inter", sans-serif;
            outline: none;
            width: 180px;
            color: #1a2634;
        }

        .search-wrapper input::placeholder {
            color: #a5b6cc;
        }

        .search-wrapper .clear-btn {
            background: none;
            border: none;
            color: #a5b6cc;
            padding: 0 0.8rem 0 0.2rem;
            cursor: pointer;
            font-size: 0.8rem;
            display: none;
        }

        .search-wrapper .clear-btn.visible {
            display: block;
        }

        .filter-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            background: #ffffff;
            padding: 0.3rem;
            border-radius: 60px;
            box-shadow: 0 4px 12px rgba(0, 20, 40, 0.04);
            border: 1px solid #e2eaf2;
        }

        .filter-tabs button {
            background: transparent;
            border: none;
            padding: 0.45rem 1.2rem;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 500;
            font-family: "Inter", sans-serif;
            color: #4d6079;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .filter-tabs button:hover {
            color: #073b1d;
            background: rgba(7, 59, 29, 0.08);
        }

        .filter-tabs button.active {
            background: #EACA26;
            color: #073b1d;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(234, 202, 38, 0.35);
        }

        .stats-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-bottom: 2rem;
            padding: 0.75rem 1.2rem;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            border: 1px solid #eaf0f6;
        }

        .stats-bar .count {
            font-size: 0.9rem;
            color: #4d6079;
        }

        .stats-bar .count strong {
            color: #1a2634;
            font-weight: 600;
        }

        .stats-bar .view-options {
            display: flex;
            gap: 0.3rem;
        }

        .stats-bar .view-options button {
            background: transparent;
            border: 1px solid #dce5ef;
            border-radius: 8px;
            padding: 0.35rem 0.7rem;
            font-size: 0.8rem;
            color: #6f82a0;
            cursor: pointer;
            transition: all 0.15s;
        }

        .stats-bar .view-options button:hover {
            background: rgba(7, 59, 29, 0.05);
            border-color: #073b1d;
        }

        .stats-bar .view-options button.active-view {
            background: rgba(7, 59, 29, 0.1);
            border-color: #073b1d;
            color: #073b1d;
        }

        /* Grid View */
        .employee-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* List View */
        .employee-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .employee-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.5rem 1.2rem 1.2rem;
            box-shadow: 0 4px 16px rgba(0, 20, 40, 0.04);
            border: 1px solid #eaf0f6;
            transition: transform 0.2s ease, box-shadow 0.25s ease, border-color 0.2s;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(2px);
        }

        .employee-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #073b1d, #1a5f3c, #EACA26);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .employee-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(7, 59, 29, 0.08);
            border-color: rgba(7, 59, 29, 0.2);
        }

        .employee-card:hover::before {
            opacity: 1;
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.8rem;
        }

        .avatar {
            flex-shrink: 0;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.3rem;
            color: #ffffff;
            text-transform: uppercase;
            background: linear-gradient(135deg, #073b1d, #0a4f28);
            box-shadow: 0 4px 10px rgba(7, 59, 29, 0.2);
            position: relative;
        }

        .card-info {
            flex: 1;
            min-width: 0;
        }

        .card-info .name {
            font-size: 1.05rem;
            font-weight: 600;
            color: #073b1d;
            letter-spacing: -0.01em;
            line-height: 1.3;
        }

        .card-info .role {
            font-size: 0.8rem;
            color: #4d6079;
            font-weight: 500;
            margin-top: 0.05rem;
        }

        .card-info .department-badge {
            display: inline-block;
            margin-top: 0.3rem;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.15rem 0.7rem;
            border-radius: 40px;
            background: rgba(7, 59, 29, 0.1);
            color: #073b1d;
        }

        .card-details {
            margin-top: 0.5rem;
            padding-top: 0.7rem;
            border-top: 1px solid #ecf2f8;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .card-details .detail-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.8rem;
            color: #4d6079;
        }

        .card-details .detail-item i {
            width: 16px;
            color: #073b1d;
            font-size: 0.75rem;
            text-align: center;
        }

        .card-details .detail-item a {
            color: #073b1d;
            text-decoration: none;
            transition: color 0.15s;
        }

        .card-details .detail-item a:hover {
            color: #0a4f28;
            text-decoration: underline;
        }

        .card-actions {
            margin-top: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn-inventory {
            background: #073b1d;
            border: 1px solid #073b1d;
            border-radius: 8px;
            padding: 0.45rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            font-family: "Inter", sans-serif;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-inventory:hover {
            background: #EACA26;
            border-color: #EACA26;
            color: #073b1d;
        }

        /* List View specific styling */
        .employee-list .employee-card {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-radius: 12px;
        }

        .employee-list .employee-card::before {
            width: 4px;
            height: 100%;
            top: 0;
            left: 0;
            right: auto;
        }

        .employee-list .card-top {
            margin-bottom: 0;
            flex: 2;
        }

        .employee-list .card-details {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
            flex-direction: row;
            gap: 2rem;
            flex: 3;
            justify-content: flex-start;
        }

        .employee-list .card-actions {
            margin-top: 0;
            flex: 1;
            justify-content: flex-end;
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 1rem;
            color: #6f82a0;
        }

        .no-results i {
            font-size: 2.5rem;
            color: #073b1d;
            margin-bottom: 0.8rem;
            opacity: 0.5;
        }

        .no-results h3 {
            font-weight: 500;
            font-size: 1.1rem;
            color: #1a2634;
        }

        .no-results p {
            font-size: 0.9rem;
            margin-top: 0.2rem;
        }

        /* ── Modal styles ── */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(7, 59, 29, 0.4);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1.5rem;
            animation: fadeIn 0.25s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-custom-inventory {
            background: #ffffff;
            border-radius: 28px;
            max-width: 680px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 32px 64px rgba(0, 20, 40, 0.25);
            animation: slideUp 0.3s ease;
            padding: 0;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2rem 1rem 2rem;
            border-bottom: 1px solid #eaf0f6;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .modal-header .modal-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modal-header .modal-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            color: #ffffff;
            text-transform: uppercase;
            background: linear-gradient(135deg, #073b1d, #0a4f28);
            flex-shrink: 0;
        }

        .modal-header .modal-user-info .modal-name {
            font-size: 1.15rem;
            font-weight: 600;
            color: #073b1d;
        }

        .modal-header .modal-user-info .modal-role {
            font-size: 0.85rem;
            color: #4d6079;
        }

        .modal-close {
            background: #f0f5fa;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #073b1d;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .modal-close:hover {
            background: rgba(7, 59, 29, 0.1);
            color: #073b1d;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 1.5rem 2rem 2rem 2rem;
        }

        .modal-body .inventory-summary {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            background: rgba(7, 59, 29, 0.04);
            border-radius: 16px;
            padding: 0.8rem 1.2rem;
            border: 1px solid rgba(7, 59, 29, 0.08);
        }

        .modal-body .inventory-summary .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #4d6079;
        }

        .modal-body .inventory-summary .stat-item strong {
            color: #073b1d;
            font-weight: 600;
        }

        .modal-body .inventory-summary .stat-item i {
            color: #073b1d;
            width: 18px;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .inventory-table thead {
            background: #f6f9fc;
            border-radius: 12px;
        }

        .inventory-table th {
            text-align: left;
            padding: 0.7rem 0.8rem;
            font-weight: 600;
            color: #073b1d;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid rgba(7, 59, 29, 0.1);
        }

        .inventory-table td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #ecf2f8;
            color: #1a2634;
        }

        .inventory-table tbody tr:last-child td {
            border-bottom: none;
        }

        .inventory-table .status-badge {
            display: inline-block;
            padding: 0.15rem 0.6rem;
            border-radius: 40px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .status-badge.available {
            background: rgba(7, 59, 29, 0.1);
            color: #073b1d;
        }

        .status-badge.maintenance {
            background: #fff3e0;
            color: #b45f3a;
        }

        .modal-body .empty-inventory {
            text-align: center;
            padding: 2rem 1rem;
            color: #8a9bb5;
        }

        .modal-body .empty-inventory i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #073b1d;
            opacity: 0.3;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Responsive adjustments ── */
        @media (max-width: 992px) {
            .employee-list .employee-card {
                flex-direction: column;
                align-items: stretch;
            }
            .employee-list .card-top,
            .employee-list .card-details,
            .employee-list .card-actions {
                flex: none;
            }
            .employee-list .card-details {
                flex-direction: column;
                gap: 0.3rem;
                padding-top: 0.5rem;
                border-top: 1px solid #ecf2f8;
                margin-top: 0.5rem;
            }
            .employee-list .card-actions {
                margin-top: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .app-wrapper {
                flex-direction: column;
            }

            .sidebar-area {
                width: 100% !important;
                display: none;
            }

            .main-content {
                padding: 1rem;
                margin-top: 56px;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }

            .header-left {
                flex-direction: column;
                align-items: stretch;
                gap: 0.8rem;
            }

            .back-btn {
                align-self: flex-start;
            }

            .header-left h1 {
                font-size: 1.6rem;
            }

            .header-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 0.8rem;
            }

            .search-wrapper {
                width: 100%;
            }

            .search-wrapper input {
                width: 100%;
            }

            .filter-tabs {
                border-radius: 16px;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding: 0.3rem 0.5rem;
                justify-content: flex-start;
                gap: 0.2rem;
            }

            .filter-tabs button {
                padding: 0.35rem 0.9rem;
                font-size: 0.75rem;
                flex-shrink: 0;
            }

            .stats-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .employee-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .modal-custom-inventory {
                max-width: 100%;
                border-radius: 20px;
                margin: 0.5rem;
            }

            .modal-header {
                padding: 1rem 1.2rem 0.8rem 1.2rem;
            }

            .modal-body {
                padding: 1rem 1.2rem 1.5rem 1.2rem;
            }

            .inventory-table {
                font-size: 0.75rem;
            }

            .inventory-table th,
            .inventory-table td {
                padding: 0.5rem 0.4rem;
            }

            .modal-body .inventory-summary {
                flex-direction: column;
                gap: 0.4rem;
            }
        }

        .hidden {
            display: none !important;
        }

        .employee-card {
            animation: fadeUp 0.4s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .employee-card:nth-child(1) { animation-delay: 0.02s; }
        .employee-card:nth-child(2) { animation-delay: 0.04s; }
        .employee-card:nth-child(3) { animation-delay: 0.06s; }
        .employee-card:nth-child(4) { animation-delay: 0.08s; }
        .employee-card:nth-child(5) { animation-delay: 0.10s; }
        .employee-card:nth-child(6) { animation-delay: 0.12s; }
    </style>
</head>

<body>

    <div class="app-wrapper">
        <!-- Sidebar Area -->
        <div class="sidebar-area">
            <?php include '../includes/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">

                <!-- Page Header -->
                <header class="page-header">
                    <div class="header-left">
                        <button class="back-btn" onclick="history.back()" aria-label="Go back">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <div class="header-text">
                            <h1><i class="fas fa-users" style="color:#073b1d; margin-right:0.4rem;"></i> <?= htmlspecialchars($office) ?> - Team Directory</h1>
                            <div class="subtitle">
                                <span id="totalCount">0</span> active employees
                            </div>
                        </div>
                    </div>
                    <div class="header-actions">
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search by name, role, email..." />
                            <button class="clear-btn" id="clearBtn" aria-label="Clear search"><i class="fas fa-times-circle"></i></button>
                        </div>
                        <div class="filter-tabs" id="filterTabs">
                            <!-- Injected by JS -->
                        </div>
                    </div>
                </header>

                <!-- Stats Bar -->
                <div class="stats-bar">
                    <div class="count">Showing <strong id="visibleCount">0</strong> employees</div>
                    <div class="view-options">
                        <button class="active-view" data-view="grid" title="Grid view"><i class="fas fa-th"></i></button>
                        <button data-view="list" title="List view"><i class="fas fa-list"></i></button>
                    </div>
                </div>

                <!-- Employee Grid/List -->
                <div class="employee-grid" id="employeeGrid">
                    <!-- Injected by JS -->
                </div>

            </div>
        </div>
    </div>

    <div class="modal-overlay" id="inventoryModal">
        <div class="modal-custom-inventory" role="dialog" aria-labelledby="modalTitle">
            <div class="modal-header">
                <div class="modal-user">
                    <div class="modal-avatar" id="modalAvatar">AO</div>
                    <div class="modal-user-info">
                        <div class="modal-name" id="modalName">Amara Okafor</div>
                        <div class="modal-role" id="modalRole">Senior Frontend Engineer</div>
                    </div>
                </div>
                <button class="modal-close" id="modalClose" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="inventory-summary d-flex justify-content-between align-items-center" id="inventorySummary" style="background: rgba(7, 59, 29, 0.04); border-radius: 16px; padding: 0.8rem 1.2rem; border: 1px solid rgba(7, 59, 29, 0.08); margin-bottom: 1.5rem;">
                    <div class="d-flex gap-3">
                        <div class="stat-item" style="font-size: 0.85rem; color: #4d6079;"><i class="fas fa-box" style="color: #073b1d; margin-right: 5px;"></i> Total items: <strong id="totalItems" style="color: #073b1d;">0</strong></div>
                        <div class="stat-item" style="font-size: 0.85rem; color: #4d6079;"><i class="fas fa-check-circle" style="color: #073b1d; margin-right: 5px;"></i> Active items: <strong id="inUseItems" style="color: #073b1d;">0</strong></div>
                    </div>
                    <button class="btn btn-sm" id="btnAssignItemToggle" style="font-size: 0.75rem; padding: 5px 12px; border-radius: 6px; border: none; background: #073b1d; color: white; font-weight: 600;">
                        <i class="fas fa-plus-circle me-1"></i> Assign Item
                    </button>
                </div>

                <!-- Assignment Form removed from here — now opens in Bootstrap modal (#addAssignModal) -->


                <div id="inventoryTableWrapper">
                    <table class="inventory-table" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Quantity / Assigned details</th>
                                <th style="text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryBody">
                            <!-- Injected by JS -->
                        </tbody>
                    </table>
                    <div class="empty-inventory hidden" id="emptyInventory">
                        <i class="fas fa-box-open"></i>
                        <p>No inventory items assigned to this employee.</p>
                    </div>
                </div>
            </div><!-- /.modal-body -->
        </div><!-- /.modal-custom-inventory -->
    </div><!-- /.modal-overlay #inventoryModal -->

    <!-- ══ VIEW ITEM MODAL ════════════════════════════════════════════════ -->
    <div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(7,59,29,0.18);">
                <div class="modal-header" style="background: linear-gradient(135deg, #073b1d, #0a5229); border-radius: 16px 16px 0 0; padding: 1.2rem 1.5rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1rem;" id="viewModalAvatar">--</div>
                        <div>
                            <h5 class="modal-title mb-0" style="color:white;font-weight:700;" id="viewItemModalLabel">Item Details</h5>
                            <small style="color:rgba(255,255,255,0.75);" id="viewModalEmployee"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 1.8rem;">
                    <div class="row g-3" id="viewModalBody">
                        <!-- Injected by JS -->
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eef2f7; padding: 0.9rem 1.5rem;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm" id="viewToEditBtn" style="background:#073b1d;color:white;border:none;font-weight:600;"><i class="fas fa-pen me-1"></i> Edit This Item</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ EDIT ITEM MODAL ════════════════════════════════════════════════ -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(7,59,29,0.18);">
                <div class="modal-header" style="background: linear-gradient(135deg, #073b1d, #0a5229); border-radius: 16px 16px 0 0; padding: 1.2rem 1.5rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:white;font-size:1.1rem;">
                            <i class="fas fa-pen"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" style="color:white;font-weight:700;" id="editItemModalLabel">Edit Inventory Item</h5>
                            <small style="color:rgba(255,255,255,0.75);" id="editModalEmployee"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editItemForm" method="POST" action="../actions/edit_employee_inventory.php">
                    <input type="hidden" name="emp_inv_id" id="editEmpInvId">
                    <input type="hidden" name="user_id"   id="editUserId">
                    <input type="hidden" name="office"    value="<?= htmlspecialchars($office) ?>">
                    <div class="modal-body" style="padding: 1.8rem;">

                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Item Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="item_type" id="editItemType" required style="border-radius:8px;">
                                    <option value="Supply">Supply (Office Supply)</option>
                                    <option value="Property">Property (Asset / Equipment)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="item_name" id="editItemName" required style="border-radius:8px;">
                            </div>
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Brand</label>
                                <input type="text" class="form-control form-control-sm" name="brand" id="editBrand" placeholder="Optional" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Size</label>
                                <input type="text" class="form-control form-control-sm" name="size" id="editSize" placeholder="Optional" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Color</label>
                                <input type="text" class="form-control form-control-sm" name="color" id="editColor" placeholder="Optional" style="border-radius:8px;">
                            </div>
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Type / Model</label>
                                <input type="text" class="form-control form-control-sm" name="type" id="editType" placeholder="Optional" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Category</label>
                                <input type="text" class="form-control form-control-sm" name="category" id="editCategory" placeholder="Optional" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Serial Number</label>
                                <input type="text" class="form-control form-control-sm" name="serial_number" id="editSerialNumber" placeholder="Optional" style="border-radius:8px;">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" name="quantity" id="editQuantity" min="1" required style="border-radius:8px;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Unit</label>
                                <input type="text" class="form-control form-control-sm" name="unit" id="editUnit" style="border-radius:8px;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Date Issued</label>
                                <input type="date" class="form-control form-control-sm" name="date_issued" id="editDateIssued" style="border-radius:8px;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Status</label>
                                <select class="form-select form-select-sm" name="status" id="editStatus" style="border-radius:8px;">
                                    <option value="Issued">Issued</option>
                                    <option value="Returned">Returned</option>
                                    <option value="Damaged">Damaged</option>
                                    <option value="Lost">Lost</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Remarks</label>
                            <textarea class="form-control form-control-sm" name="remarks" id="editRemarks" rows="2" placeholder="Notes, conditions, etc." style="border-radius:8px;"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #eef2f7; padding: 0.9rem 1.5rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm" style="background:#073b1d;color:white;border:none;font-weight:600;padding:6px 18px;border-radius:8px;"><i class="fas fa-save me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══ ASSIGN ITEM MODAL ══════════════════════════════════════════════ -->
    <div class="modal fade" id="assignItemModal" tabindex="-1" aria-labelledby="assignItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(7,59,29,0.18);">
                <div class="modal-header" style="background: linear-gradient(135deg, #073b1d, #0a5229); border-radius: 16px 16px 0 0; padding: 1.2rem 1.5rem;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:white;font-size:1.1rem;">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" style="color:white;font-weight:700;" id="assignItemModalLabel">Assign Inventory Item</h5>
                            <small style="color:rgba(255,255,255,0.75);" id="assignModalEmployee"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignItemForm" method="POST" action="../actions/add_employee_inventory.php">
                    <input type="hidden" name="user_id" id="assignUserId">
                    <input type="hidden" name="office" value="<?= htmlspecialchars($office) ?>">
                    <div class="modal-body" style="padding: 1.8rem;">

                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Item Type <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" name="item_type" required style="border-radius:8px;">
                                    <option value="Supply">Supply (Office Supply)</option>
                                    <option value="Property">Property (Asset / Equipment)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="item_name" placeholder="Laptop, Notebook, Pen, etc." required style="border-radius:8px;">
                            </div>
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Brand</label>
                                <input type="text" class="form-control form-control-sm" name="brand" placeholder="Optional" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Size</label>
                                <input type="text" class="form-control form-control-sm" name="size" placeholder="Optional" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Color</label>
                                <input type="text" class="form-control form-control-sm" name="color" placeholder="Optional" style="border-radius:8px;">
                            </div>
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Type / Model</label>
                                <input type="text" class="form-control form-control-sm" name="type" placeholder="Optional" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Category</label>
                                <input type="text" class="form-control form-control-sm" name="category" placeholder="Optional" style="border-radius:8px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Serial Number</label>
                                <input type="text" class="form-control form-control-sm" name="serial_number" placeholder="Optional" style="border-radius:8px;">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm" name="quantity" value="1" min="1" required style="border-radius:8px;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Unit</label>
                                <input type="text" class="form-control form-control-sm" name="unit" value="pcs" style="border-radius:8px;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Date Issued</label>
                                <input type="date" class="form-control form-control-sm" name="date_issued" value="<?= date('Y-m-d') ?>" style="border-radius:8px;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Status</label>
                                <select class="form-select form-select-sm" name="status" style="border-radius:8px;">
                                    <option value="Issued">Issued</option>
                                    <option value="Returned">Returned</option>
                                    <option value="Damaged">Damaged</option>
                                    <option value="Lost">Lost</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-semibold" style="font-size:0.8rem;color:#4d6079;">Remarks</label>
                            <textarea class="form-control form-control-sm" name="remarks" rows="2" placeholder="Notes, conditions, etc." style="border-radius:8px;"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #eef2f7; padding: 0.9rem 1.5rem;">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm" style="background:#073b1d;color:white;border:none;font-weight:600;padding:6px 18px;border-radius:8px;"><i class="fas fa-plus me-1"></i> Assign Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Data and Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const employees = <?php echo json_encode($employees_list); ?>;
            const canManage = <?php echo (in_array($_SESSION['user_type'] ?? '', ['Admin', 'Property Custodian'])) ? 'true' : 'false'; ?>;
            let currentEmp  = null;
            const grid = document.getElementById('employeeGrid');
            const searchInput = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearBtn');
            const filterTabs = document.getElementById('filterTabs');
            const totalCountEl = document.getElementById('totalCount');
            const visibleCountEl = document.getElementById('visibleCount');
            const viewButtons = document.querySelectorAll('.view-options button');
            
            let currentFilter = 'all';
            let currentSearch = '';
            let currentView = 'grid';

            // Populate filter buttons dynamically based on roles present
            const uniqueRoles = [...new Set(employees.map(e => e.role).filter(Boolean))];
            
            let filterHtml = `<button class="active" data-filter="all">All</button>`;
            uniqueRoles.forEach(role => {
                filterHtml += `<button data-filter="${role.toLowerCase()}">${role}</button>`;
            });
            filterTabs.innerHTML = filterHtml;

            // Render function
            function render() {
                const filtered = employees.filter(e => {
                    const nameMatch = e.fullname.toLowerCase().includes(currentSearch);
                    const emailMatch = e.email.toLowerCase().includes(currentSearch);
                    const userMatch = e.username.toLowerCase().includes(currentSearch);
                    const matchesSearch = nameMatch || emailMatch || userMatch;
                    
                    const matchesFilter = currentFilter === 'all' || (e.role && e.role.toLowerCase() === currentFilter);
                    return matchesSearch && matchesFilter;
                });

                visibleCountEl.textContent = filtered.length;
                
                if (filtered.length === 0) {
                    grid.className = 'no-results-container';
                    grid.innerHTML = `
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No employees found</h3>
                            <p>Try adjusting your search or filter keywords.</p>
                        </div>
                    `;
                    return;
                }

                grid.className = currentView === 'grid' ? 'employee-grid' : 'employee-list';
                
                grid.innerHTML = filtered.map(e => {
                    const initials = e.fullname.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                    return `
                        <div class="employee-card">
                            <div class="card-top">
                                <div class="avatar">${initials}</div>
                                <div class="card-info">
                                    <div class="name">${e.fullname}</div>
                                    <div class="role">${e.role || 'Employee'}</div>
                                    <span class="department-badge">${e.department}</span>
                                </div>
                            </div>
                            <div class="card-details">
                                <div class="detail-item"><i class="fas fa-envelope"></i> <a href="mailto:${e.email}">${e.email}</a></div>
                                <div class="detail-item"><i class="fas fa-user"></i> <span>@${e.username}</span></div>
                                <div class="detail-item"><i class="fas fa-boxes"></i> <span>${e.inventory.length} items assigned</span></div>
                            </div>
                            <div class="card-actions">
                                <button class="btn-inventory" onclick="showInventory(${e.id})"><i class="fas fa-clipboard-list"></i> View Inventory</button>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            // ── Lightweight modal helpers (no Bootstrap JS required) ────
            function showBsModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.style.cssText = 'display:flex;align-items:center;justify-content:center;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;background:rgba(0,0,0,0.55);';
                el.setAttribute('aria-hidden','false');
                el._bsClose = e => { if (e.target === el) hideBsModal(id); };
                el.addEventListener('click', el._bsClose);
                el.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
                    btn._bsDismiss = () => hideBsModal(id);
                    btn.addEventListener('click', btn._bsDismiss);
                });
            }
            function hideBsModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.style.display = 'none';
                el.setAttribute('aria-hidden','true');
                if (el._bsClose) el.removeEventListener('click', el._bsClose);
                el.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
                    if (btn._bsDismiss) btn.removeEventListener('click', btn._bsDismiss);
                });
            }

            // ── Assign Item button → open custom modal ──────────────────
            const toggleBtn = document.getElementById('btnAssignItemToggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    if (!currentEmp) return;
                    document.getElementById('assignUserId').value = currentEmp.id;
                    document.getElementById('assignModalEmployee').textContent = currentEmp.fullname;
                    showBsModal('assignItemModal');
                });
            }

            // Modal Display
            window.showInventory = function(id) {
                const emp = employees.find(e => e.id == id);
                if (!emp) return;

                currentEmp = emp;
                document.getElementById('modalName').textContent = emp.fullname;
                document.getElementById('modalRole').textContent = emp.role || 'Employee';
                document.getElementById('modalAvatar').textContent = emp.fullname.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                
                document.getElementById('totalItems').textContent = emp.inventory.length;
                document.getElementById('inUseItems').textContent = emp.inventory.length;

                const tbody = document.getElementById('inventoryBody');
                const emptyState = document.getElementById('emptyInventory');
                const table = document.getElementById('inventoryTable');

                if (emp.inventory.length === 0) {
                    tbody.innerHTML = '';
                    table.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                } else {
                    table.classList.remove('hidden');
                    emptyState.classList.add('hidden');
                    tbody.innerHTML = emp.inventory.map((i, idx) => {
                        const statusClass = i.status && i.status.toLowerCase() === 'borrowed' ? 'status-badge maintenance' : 'status-badge available';
                        const viewBtn = `<button class="btn btn-sm" onclick="openViewModal(${idx})" title="View" style="font-size:0.7rem;padding:3px 8px;border-radius:5px;background:#e8f5e9;color:#073b1d;border:1px solid #b2dfdb;"><i class="fas fa-eye"></i></button>`;
                        const editBtn = (canManage && i.editable)
                            ? `<button class="btn btn-sm ms-1" onclick="openEditModal(${idx})" title="Edit" style="font-size:0.7rem;padding:3px 8px;border-radius:5px;background:#073b1d;color:white;border:none;"><i class="fas fa-pen"></i></button>`
                            : '';
                        return `
                            <tr>
                                <td><strong>${i.item}</strong></td>
                                <td>${i.category}</td>
                                <td><span class="${statusClass}">${i.status}</span></td>
                                <td>${i.assigned}</td>
                                <td style="text-align:center;white-space:nowrap;">${viewBtn}${editBtn}</td>
                            </tr>
                        `;
                    }).join('');
                }

                document.getElementById('inventoryModal').classList.add('active');
            };

            // ── View Modal ───────────────────────────────────────────────
            window.openViewModal = function(idx) {
                const i = currentEmp.inventory[idx];
                if (!i) return;

                const emp = currentEmp;
                const initials = emp.fullname.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
                document.getElementById('viewModalAvatar').textContent    = initials;
                document.getElementById('viewModalEmployee').textContent  = emp.fullname + (emp.role ? ' · ' + emp.role : '');

                const fields = [
                    ['Item Name',     i.raw_item_name    || i.item || '—'],
                    ['Item Type',     i.raw_item_type    || '—'],
                    ['Category',      i.raw_category     || i.category || '—'],
                    ['Brand',         i.raw_brand        || '—'],
                    ['Size',          i.raw_size         || '—'],
                    ['Color',         i.raw_color        || '—'],
                    ['Type / Model',  i.raw_type         || '—'],
                    ['Serial Number', i.raw_serial_number|| '—'],
                    ['Quantity',      (i.raw_quantity || '—') + ' ' + (i.raw_unit || '')],
                    ['Date Issued',   i.raw_date_issued  || '—'],
                    ['Status',        i.raw_status       || i.status || '—'],
                    ['Remarks',       i.raw_remarks      || '—'],
                ];

                document.getElementById('viewModalBody').innerHTML = fields.map(([label, val]) => `
                    <div class="col-md-4">
                        <div style="background:#f8fafb;border-radius:10px;padding:0.75rem 1rem;height:100%;">
                            <div style="font-size:0.68rem;font-weight:700;color:#8a9bb5;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.25rem;">${label}</div>
                            <div style="font-size:0.88rem;color:#1a2634;font-weight:500;word-break:break-word;">${val}</div>
                        </div>
                    </div>
                `).join('');

                // Wire the "Edit This Item" button in view modal footer
                document.getElementById('viewToEditBtn').onclick = function() {
                    hideBsModal('viewItemModal');
                    setTimeout(() => openEditModal(idx), 350);
                };
                if (!canManage || !i.editable) {
                    document.getElementById('viewToEditBtn').classList.add('d-none');
                } else {
                    document.getElementById('viewToEditBtn').classList.remove('d-none');
                }

                showBsModal('viewItemModal');
            };

            // ── Edit Modal ───────────────────────────────────────────────
            window.openEditModal = function(idx) {
                const i = currentEmp.inventory[idx];
                if (!i || !i.editable) return;

                document.getElementById('editModalEmployee').textContent = currentEmp.fullname;
                document.getElementById('editEmpInvId').value   = i.emp_inv_id;
                document.getElementById('editUserId').value     = currentEmp.id;
                document.getElementById('editItemType').value   = i.raw_item_type    || 'Supply';
                document.getElementById('editItemName').value   = i.raw_item_name    || '';
                document.getElementById('editBrand').value      = i.raw_brand        || '';
                document.getElementById('editSize').value       = i.raw_size         || '';
                document.getElementById('editColor').value      = i.raw_color        || '';
                document.getElementById('editType').value       = i.raw_type         || '';
                document.getElementById('editCategory').value   = i.raw_category     || '';
                document.getElementById('editSerialNumber').value = i.raw_serial_number || '';
                document.getElementById('editQuantity').value   = i.raw_quantity     || 1;
                document.getElementById('editUnit').value       = i.raw_unit         || 'pcs';
                document.getElementById('editDateIssued').value = i.raw_date_issued  || '';
                document.getElementById('editStatus').value     = i.raw_status       || 'Issued';
                document.getElementById('editRemarks').value    = i.raw_remarks      || '';

                showBsModal('editItemModal');
            };

            // Close Modal Events
            document.getElementById('modalClose').addEventListener('click', () => {
                document.getElementById('inventoryModal').classList.remove('active');
            });
            
            document.getElementById('inventoryModal').addEventListener('click', (e) => {
                if (e.target.id === 'inventoryModal') {
                    document.getElementById('inventoryModal').classList.remove('active');
                }
            });



            // Search input handler
            searchInput.addEventListener('input', (e) => {
                currentSearch = e.target.value.toLowerCase();
                if (currentSearch) {
                    clearBtn.classList.add('visible');
                } else {
                    clearBtn.classList.remove('visible');
                }
                render();
            });

            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                currentSearch = '';
                clearBtn.classList.remove('visible');
                render();
            });

            // Filter tab click handler
            filterTabs.addEventListener('click', (e) => {
                const btn = e.target.closest('button');
                if (!btn) return;
                
                filterTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                currentFilter = btn.dataset.filter;
                render();
            });

            // View toggle (Grid / List)
            viewButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    viewButtons.forEach(b => b.classList.remove('active-view'));
                    btn.classList.add('active-view');
                    currentView = btn.dataset.view;
                    render();
                });
            });

            // Initial Setup
            totalCountEl.textContent = employees.length;
            render();
        });
    </script>
</body>

</html>