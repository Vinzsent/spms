<?php
include '../includes/auth.php';
include '../includes/db.php';

$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));
if (!in_array($user_type, ['propertycustodian', 'admin'])) {
    echo "<p class='text-danger'>Access denied.</p>"; exit;
}

$report_type = $_GET['report_type'] ?? 'inventory';

// ─── HELPER ───────────────────────────────────────────────────────────────
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
// 1. PROPERTY INVENTORY
// ═══════════════════════════════════════════════════════════════════════════
if ($report_type === 'inventory') {
    $category    = $_GET['category'] ?? '';
    $stock_status = $_GET['stock_status'] ?? [];
    $item_status  = $_GET['item_status']  ?? [];

    $w = ["i.receiver = 'Property Custodian'"];

    if (!empty($category))
        $w[] = "i.category = '" . safe($conn, $category) . "'";

    if (!empty($item_status)) {
        $s = array_map(fn($x) => "'" . safe($conn, $x) . "'", $item_status);
        $w[] = "i.status IN (" . implode(',', $s) . ")";
    }

    if (!empty($stock_status)) {
        $sc = [];
        if (in_array('normal', $stock_status)) $sc[] = "i.current_stock > i.reorder_level";
        if (in_array('low',    $stock_status)) $sc[] = "(i.current_stock <= i.reorder_level AND i.current_stock > 0)";
        if (in_array('out',    $stock_status)) $sc[] = "i.current_stock = 0";
        if ($sc) $w[] = "(" . implode(' OR ', $sc) . ")";
    }

    $where = "WHERE " . implode(' AND ', $w);
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;

    $count_sql = "SELECT COUNT(*) as total FROM property_inventory i $where";
    $count_res = $conn->query($count_sql);
    $total_rows = ($count_res) ? (int)$count_res->fetch_assoc()['total'] : 0;

    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $limit;

    $sql   = "SELECT i.*, s.supplier_name FROM property_inventory i
              LEFT JOIN supplier s ON i.supplier_id = s.supplier_id
              $where ORDER BY i.item_name ASC LIMIT $limit OFFSET $offset";
    $res   = $conn->query($sql);

    $grand_total = 0;
    ob_start();
    ?>
    <h3 class="section-title"><i class="fas fa-boxes me-2"></i>Property Items</h3>
    <table class="table table-bordered report-table w-100">
        <thead>
            <tr class="print-spacer-row">
                <th colspan="10"></th>
            </tr>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Reorder Lvl</th>
                <th>Unit</th>
                <th>Brand</th>
                <th>Type</th>
                <th>Status</th>
                <th>Unit Cost</th>
                <th>Total Value</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $tv = $row['current_stock'] * ($row['unit_cost'] ?? 0);
                $grand_total += $tv;
                $cls = $row['current_stock'] == 0 ? 'stock-out' : ($row['current_stock'] <= $row['reorder_level'] ? 'stock-warn' : ''); ?>
            <tr>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td class="<?= $cls ?>"><?= $row['current_stock'] ?></td>
                <td><?= $row['reorder_level'] ?></td>
                <td><?= htmlspecialchars($row['unit']) ?></td>
                <td><?= htmlspecialchars($row['brand'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['type'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>₱<?= number_format($row['unit_cost'] ?? 0, 2) ?></td>
                <td>₱<?= number_format($tv, 2) ?></td>
            </tr>
        <?php endwhile; ?>
            <tr class="fw-bold bg-light">
                <td colspan="9" class="text-end">Total Valuation:</td>
                <td>₱<?= number_format($grand_total, 2) ?></td>
            </tr>
        <?php else: ?>
            <tr><td colspan="10" class="text-center text-muted py-3">No items match the selected filters.</td></tr>
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

    $w = ["sl.receiver = 'Property Custodian'"];

    if (!empty($date_start)) $w[] = "sl.date_created >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "sl.date_created <= '" . safe($conn, $date_end) . " 23:59:59'";

    if (!empty($move_types)) {
        $mt = array_map(fn($x) => "'" . safe($conn, $x) . "'", $move_types);
        $w[] = "sl.movement_type IN (" . implode(',', $mt) . ")";
    }

    if (!empty($item_search)) {
        $esc = safe($conn, $item_search);
        $w[] = "pi.item_name LIKE '%$esc%'";
    }

    $where = "WHERE " . implode(' AND ', $w);
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;

    $count_sql = "SELECT COUNT(*) as total FROM property_stock_logs sl LEFT JOIN property_inventory pi ON sl.inventory_id = pi.inventory_id $where";
    $count_res = $conn->query($count_sql);
    $total_rows = ($count_res) ? (int)$count_res->fetch_assoc()['total'] : 0;

    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $limit;

    $sql   = "SELECT sl.*, pi.item_name, pi.unit
              FROM property_stock_logs sl
              LEFT JOIN property_inventory pi ON sl.inventory_id = pi.inventory_id
              $where ORDER BY sl.date_created DESC LIMIT $limit OFFSET $offset";
    $res   = $conn->query($sql);

    ob_start(); ?>
    <h3 class="section-title"><i class="fas fa-exchange-alt me-2"></i>Stock Movement Logs</h3>
    <table class="table table-bordered report-table w-100">
        <thead>
            <tr class="print-spacer-row">
                <th colspan="9"></th>
            </tr>
            <tr>
                <th>Date & Time</th>
                <th>Item Name</th>
                <th>Unit</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Prev Stock</th>
                <th>New Stock</th>
                <th>Requester</th>
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
                <td><?= htmlspecialchars($log['unit'] ?? '—') ?></td>
                <td><span class="<?= $badge ?>"><?= $label ?></span></td>
                <td><?= $log['quantity'] ?></td>
                <td><?= $log['previous_stock'] ?></td>
                <td><?= $log['new_stock'] ?></td>
                <td><?= htmlspecialchars($log['requester_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($log['notes'] ?? '') ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9" class="text-center text-muted py-3">No stock movement records match the filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?= renderPagination($total_rows, $limit, $page, 'stocklogs') ?>
    <?php
    echo ob_get_clean();
}

// ═══════════════════════════════════════════════════════════════════════════
// 3. AIRCON REPORT
// ═══════════════════════════════════════════════════════════════════════════
if ($report_type === 'aircon') {
    $campus     = strtoupper(trim($_GET['ac_campus'] ?? ''));
    $statuses   = $_GET['ac_status'] ?? [];
    $date_start = $_GET['ac_ds'] ?? '';
    $date_end   = $_GET['ac_de'] ?? '';

    $w = [];

    if ($campus === 'BED' || $campus === 'TED')
        $w[] = "TRIM(UPPER(a.campus)) = '" . safe($conn, $campus) . "'";

    if (!empty($statuses)) {
        $st = array_map(fn($x) => "'" . safe($conn, $x) . "'", $statuses);
        $w[] = "a.status IN (" . implode(',', $st) . ")";
    }

    if (!empty($date_start)) $w[] = "a.date_created >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "a.date_created <= '" . safe($conn, $date_end) . " 23:59:59'";

    $where = !empty($w) ? "WHERE " . implode(' AND ', $w) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;

    $count_sql = "SELECT COUNT(*) as total FROM aircons a $where";
    $count_res = $conn->query($count_sql);
    $total_rows = ($count_res) ? (int)$count_res->fetch_assoc()['total'] : 0;

    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $limit;

    $sql   = "SELECT a.*, s.supplier_name FROM aircons a
              LEFT JOIN supplier s ON a.supplier_id = s.supplier_id
              $where ORDER BY a.campus, a.location, a.brand ASC LIMIT $limit OFFSET $offset";
    $res   = $conn->query($sql);

    $status_colors = [
        'Operational'       => 'badge-in',
        'Working'           => 'badge-in',
        'Needs Repair'      => 'badge-out',
        'Under Maintenance' => 'badge-adj',
        'Decommissioned'    => 'badge-decomm',
        'N/A'               => 'badge-adj',
    ];

    ob_start(); ?>
    <h3 class="section-title"><i class="fas fa-snowflake me-2"></i>Aircon Inventory</h3>
    <table class="table table-bordered report-table w-100">
        <thead>
            <tr class="print-spacer-row">
                <th colspan="12"></th>
            </tr>
            <tr>
                <th>Campus</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Type</th>
                <th>Capacity</th>
                <th>Serial No.</th>
                <th>Location</th>
                <th>Status</th>
                <th>Purchase Date</th>
                <th>Last Service</th>
                <th>Warranty Expiry</th>
                <th>Purchase Price</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $badge = $status_colors[$row['status']] ?? 'badge-adj'; ?>
            <tr>
                <td><?= htmlspecialchars($row['campus'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['brand'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['model'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['type'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['capacity'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['serial_number'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['location'] ?? '—') ?></td>
                <td><span class="<?= $badge ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                <td><?= $row['purchase_date'] ? date('M d, Y', strtotime($row['purchase_date'])) : '—' ?></td>
                <td><?= $row['last_service_date'] ? date('M d, Y', strtotime($row['last_service_date'])) : '—' ?></td>
                <td><?= $row['warranty_expiry'] ? date('M d, Y', strtotime($row['warranty_expiry'])) : '—' ?></td>
                <td>₱<?= number_format($row['purchase_price'] ?? 0, 2) ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="12" class="text-center text-muted py-3">No aircon records match the selected filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?= renderPagination($total_rows, $limit, $page, 'aircon') ?>
    <?php
    echo ob_get_clean();
}

// Cleanup temp file
@unlink(__DIR__ . '/../scratch_schema.php');
