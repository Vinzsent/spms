<?php
include '../includes/auth.php';
include '../includes/db.php';

$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));
if (!in_array($user_type, ['supplyincharge', 'admin'])) {
    echo "<p class='text-danger'>Access denied.</p>"; exit;
}

$report_type = $_GET['report_type'] ?? 'inventory';

function safe($conn, $v) { return $conn->real_escape_string(trim($v)); }

function renderPagination($total_rows, $limit, $page, $report_type) {
    $total_pages = ceil($total_rows / $limit);
    if ($total_pages <= 1) return '';

    ob_start();
    ?>
    <div class="d-flex justify-content-between align-items-center mt-3 no-print">
        <div class="text-secondary small">
            Showing <?= min($total_rows, ($page - 1) * $limit + 1) ?> to <?= min($total_rows, $page * $limit) ?> of <?= $total_rows ?> entries
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="javascript:void(0)" onclick="preview('<?= $report_type ?>', <?= $page - 1 ?>)">Previous</a>
                </li>
                <li class="page-item active">
                    <span class="page-link"><?= $page ?></span>
                </li>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="javascript:void(0)" onclick="preview('<?= $report_type ?>', <?= $page + 1 ?>)">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}


// ═══════════════════════════════════════════════════════════════════════════
// 1. SUPPLY INVENTORY
// ═══════════════════════════════════════════════════════════════════════════
if ($report_type === 'inventory') {
    $search       = $_GET['search'] ?? '';
    $stock_status = $_GET['stock_status'] ?? [];
    
    // We strictly select from inventory table
    $w = [];
    
    if (!empty($search)) {
         $w[] = "i.item_name LIKE '%" . safe($conn, $search) . "%'";
    }

    if (!empty($stock_status)) {
        $sc = [];
        if (in_array('normal', $stock_status)) $sc[] = "i.current_stock > i.reorder_level";
        if (in_array('low',    $stock_status)) $sc[] = "(i.current_stock <= i.reorder_level AND i.current_stock > 0)";
        if (in_array('out',    $stock_status)) $sc[] = "i.current_stock = 0";
        if ($sc) $w[] = "(" . implode(' OR ', $sc) . ")";
    }

    $where = !empty($w) ? "WHERE " . implode(' AND ', $w) : "";
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;

    $count_sql = "SELECT COUNT(*) as total FROM inventory i $where";
    $count_res = $conn->query($count_sql);
    $total_rows = ($count_res) ? (int)$count_res->fetch_assoc()['total'] : 0;

    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $limit;

    $sql   = "SELECT i.*, s.supplier_name FROM inventory i
              LEFT JOIN supply_supplier s ON i.supplier_id = s.supplier_id
              $where ORDER BY i.item_name ASC LIMIT $limit OFFSET $offset";
    $res   = $conn->query($sql);

    ob_start();
    ?>
    <h3 class="section-title"><i class="fas fa-boxes me-2"></i>Supply Items</h3>
    <table class="table table-bordered report-table w-100">
        <thead>
            <tr class="print-spacer-row">
                <th colspan="2"></th>
            </tr>
            <tr>
                <th style="width:62%; text-align:left;">ITEMS</th>
                <th style="width:38%; text-align:right;">Quantity available</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $cls = $row['current_stock'] == 0 ? 'stock-out' : ($row['current_stock'] <= $row['reorder_level'] ? 'stock-warn' : '');
                $unit = htmlspecialchars(trim($row['unit'] ?? ''));
                $qty_display = htmlspecialchars($row['current_stock']) . ($unit ? ' ' . $unit : '');
            ?>
            <tr>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td class="<?= $cls ?>" style="text-align:right;"><?= $qty_display ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2" class="text-center text-muted py-3">No items match the selected filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?= renderPagination($total_rows, $limit, $page, 'inventory') ?>
    <?php
    echo ob_get_clean();
}

// ═══════════════════════════════════════════════════════════════════════════
// 2. STOCK MOVEMENT LOGS
// ═══════════════════════════════════════════════════════════════════════════
if ($report_type === 'stocklogs') {
    $date_start   = $_GET['log_ds'] ?? '';
    $date_end     = $_GET['log_de'] ?? '';
    $move_types   = $_GET['move_type'] ?? [];
    $item_search  = $_GET['log_item'] ?? '';

    $w = []; // Supply logs where receiver is Supply In-charge
    $w[] = "sl.receiver = 'Supply In-charge'";

    if (!empty($date_start)) $w[] = "sl.date_created >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "sl.date_created <= '" . safe($conn, $date_end) . " 23:59:59'";

    if (!empty($move_types)) {
        $mt = array_map(fn($x) => "'" . safe($conn, $x) . "'", $move_types);
        $w[] = "sl.movement_type IN (" . implode(',', $mt) . ")";
    }

    if (!empty($item_search)) {
        $esc = safe($conn, $item_search);
        $w[] = "i.item_name LIKE '%$esc%'";
    }

    $where = !empty($w) ? "WHERE " . implode(' AND ', $w) : "";
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;

    $count_sql = "SELECT COUNT(*) as total FROM stock_logs sl LEFT JOIN inventory i ON sl.inventory_id = i.inventory_id $where";
    $count_res = $conn->query($count_sql);
    $total_rows = ($count_res) ? (int)$count_res->fetch_assoc()['total'] : 0;

    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $limit;

    $sql   = "SELECT sl.*, i.item_name
              FROM stock_logs sl
              LEFT JOIN inventory i ON sl.inventory_id = i.inventory_id
              $where ORDER BY sl.date_created DESC LIMIT $limit OFFSET $offset";
    $res   = $conn->query($sql);

    ob_start(); ?>
    <h3 class="section-title"><i class="fas fa-exchange-alt me-2"></i>Stock Movement Logs</h3>
    <table class="table table-bordered report-table w-100">
        <thead>
            <tr class="print-spacer-row">
                <th colspan="7"></th>
            </tr>
            <tr>
                <th>Date & Time</th>
                <th>Item Name</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Prev Stock</th>
                <th>New Stock</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($log = $res->fetch_assoc()):
                $badge = $log['movement_type'] === 'IN' ? 'badge-in' : ($log['movement_type'] === 'OUT' ? 'badge-out' : 'badge-adj');
                $label = $log['movement_type']; ?>
            <tr>
                <td><?= date('M d, Y H:i', strtotime($log['date_created'])) ?></td>
                <td><?= htmlspecialchars($log['item_name'] ?? '—') ?></td>
                <td><span class="<?= $badge ?>"><?= htmlspecialchars($label) ?></span></td>
                <td><?= htmlspecialchars($log['quantity']) ?></td>
                <td><?= htmlspecialchars($log['previous_stock']) ?></td>
                <td><?= htmlspecialchars($log['new_stock']) ?></td>
                <td><?= htmlspecialchars($log['notes'] ?? '') ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center text-muted py-3">No stock movement records match the filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?= renderPagination($total_rows, $limit, $page, 'stocklogs') ?>
    <?php
    echo ob_get_clean();
}

// ═══════════════════════════════════════════════════════════════════════════
// 3. ISSUANCE LOGS
// ═══════════════════════════════════════════════════════════════════════════
if ($report_type === 'issuance') {
    $statuses   = $_GET['iss_status'] ?? [];
    $date_start = $_GET['iss_ds'] ?? '';
    $date_end   = $_GET['iss_de'] ?? '';

    $w = ["LOWER(sr.request_type) = 'consumables'"]; // specific to supply in-charge

    if (!empty($statuses)) {
        // Find if they selected approved/issued etc
        // Mapping simple checkbox values to the actual db checking
        $st = [];
        if (in_array('Pending', $statuses)) $st[] = "sr.approved_by IS NULL AND sr.issued_by IS NULL";
        if (in_array('Approved', $statuses)) $st[] = "sr.approved_by IS NOT NULL AND sr.issued_by IS NULL";
        if (in_array('Issued', $statuses)) $st[] = "sr.issued_by IS NOT NULL";
        if ($st) {
            $w[] = "(" . implode(' OR ', $st) . ")";
        }
    }

    if (!empty($date_start)) $w[] = "sr.date_requested >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "sr.date_requested <= '" . safe($conn, $date_end) . " 23:59:59'";

    $where = "WHERE " . implode(' AND ', $w);
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;

    $count_sql = "SELECT COUNT(*) as total FROM supply_request sr LEFT JOIN user u ON u.id = sr.user_id $where";
    $count_res = $conn->query($count_sql);
    $total_rows = ($count_res) ? (int)$count_res->fetch_assoc()['total'] : 0;

    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $limit;

    $sql   = "SELECT sr.*, 
              CONCAT_WS(' ', u.first_name, u.last_name) AS requester_name 
              FROM supply_request sr
              LEFT JOIN user u ON u.id = sr.user_id
              $where ORDER BY sr.date_requested DESC LIMIT $limit OFFSET $offset";
    $res   = $conn->query($sql);

    ob_start(); ?>
    <h3 class="section-title"><i class="fas fa-hand-holding me-2"></i>Supply Issuance Logs</h3>
    <table class="table table-bordered report-table w-100">
        <thead>
            <tr class="print-spacer-row">
                <th colspan="7"></th>
            </tr>
            <tr>
                <th>Date Requested</th>
                <th>Requester</th>
                <th>Item Description</th>
                <th>Status</th>
                <th>Qty Needed</th>
                <th>Total Cost</th>
                <th>Issued Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                // Logic to match issuance page status Badges
                $status_label = 'Pending';
                $badge = 'badge-adj';
                if (!empty($row['issued_by'])) { 
                    $status_label = 'Issued'; $badge = 'badge-in'; 
                } elseif (!empty($row['approved_by'])) { 
                    $status_label = 'Approved'; $badge = 'badge-in'; 
                } elseif (!empty($row['verified_by'])) { 
                    $status_label = 'Verified'; $badge = 'badge-adj'; 
                } elseif (!empty($row['checked_by'])) { 
                    $status_label = 'Checked'; $badge = 'badge-decomm'; 
                } elseif (!empty($row['noted_by'])) { 
                    $status_label = 'Noted'; $badge = 'badge-out'; 
                }
            ?>
            <tr>
                <td><?= date('M d, Y', strtotime($row['date_requested'])) ?></td>
                <td><?= htmlspecialchars($row['requester_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['item_description'] ?? '—') ?></td>
                <td><span class="<?= $badge ?>"><?= $status_label ?></span></td>
                <td><?= htmlspecialchars($row['quantity'] ?? '—') ?></td>
                <td>₱<?= number_format((float)($row['total_cost'] ?? 0), 2) ?></td>
                <td><?= !empty($row['issued_date']) ? date('M d, Y', strtotime($row['issued_date'])) : '—' ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center text-muted py-3">No issuance records match the selected filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?= renderPagination($total_rows, $limit, $page, 'issuance') ?>
    <?php
    echo ob_get_clean();
}

// ═══════════════════════════════════════════════════════════════════════════
// 4. OFFICE REQUISITIONS REPORT
// ═══════════════════════════════════════════════════════════════════════════
if ($report_type === 'offices') {
    $office     = $_GET['office'] ?? '';
    $date_start = $_GET['off_ds'] ?? '';
    $date_end   = $_GET['off_de'] ?? '';

    $w = ["sr.request_type = 'office_supply'"];

    if (!empty($office)) {
        $w[] = "sr.department_unit = '" . safe($conn, $office) . "'";
    }

    if (!empty($date_start)) $w[] = "sr.date_requested >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "sr.date_requested <= '" . safe($conn, $date_end) . " 23:59:59'";

    $where = "WHERE " . implode(' AND ', $w);
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;

    $count_sql = "SELECT COUNT(*) as total FROM supply_request sr $where";
    $count_res = $conn->query($count_sql);
    $total_rows = ($count_res) ? (int)$count_res->fetch_assoc()['total'] : 0;

    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $limit;

    $sql   = "SELECT sr.* FROM supply_request sr $where ORDER BY sr.date_requested DESC LIMIT $limit OFFSET $offset";
    $res   = $conn->query($sql);

    $grand_total = 0;
    ob_start(); ?>
    <h3 class="section-title"><i class="fas fa-building me-2"></i>Office Requisitions Summary</h3>
    <table class="table table-bordered report-table w-100">
        <thead>
            <tr class="print-spacer-row">
                <th colspan="8"></th>
            </tr>
            <tr>
                <th>Date Requested</th>
                <th>Office / Dept</th>
                <th>Item Name</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Cost</th>
                <th>Total Cost</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $total = (float)($row['total_cost'] ?? 0);
                $grand_total += $total;
                $st = $row['status'] ?? 'Pending';
                $badge = ($st === 'Issued' || $st === 'Completed') ? 'badge-in' : 'badge-adj';
            ?>
            <tr>
                <td><?= date('M d, Y', strtotime($row['date_requested'])) ?></td>
                <td><?= htmlspecialchars($row['department_unit'] ?? '—') ?></td>
                <td class="fw-bold"><?= htmlspecialchars($row['item_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['request_description'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['quantity_requested'] ?? '0') ?> <?= htmlspecialchars($row['unit'] ?? '') ?></td>
                <td>₱<?= number_format((float)($row['unit_cost'] ?? 0), 2) ?></td>
                <td class="fw-bold">₱<?= number_format($total, 2) ?></td>
                <td><span class="<?= $badge ?>"><?= htmlspecialchars($st) ?></span></td>
            </tr>
        <?php endwhile; ?>
            <tr class="fw-bold bg-light">
                <td colspan="6" class="text-end">Grand Total Cost:</td>
                <td colspan="2">₱<?= number_format($grand_total, 2) ?></td>
            </tr>
        <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted py-3">No office requisition records match the selected filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?= renderPagination($total_rows, $limit, $page, 'offices') ?>
    <?php
    echo ob_get_clean();
}
?>

