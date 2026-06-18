<?php
include '../includes/auth.php';
include '../includes/db.php';

$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));
if (!in_array($user_type, ['supplyincharge', 'admin'])) {
    http_response_code(403); exit('Access denied.');
}

function safe($conn, $v) { return $conn->real_escape_string(trim($v)); }

$report_type = $_GET['report_type'] ?? 'inventory';

// Fetch letterhead
$header_query = $conn->query("SELECT * FROM printer_header_settings WHERE id = 1");
$print_header = $header_query ? $header_query->fetch_assoc() : null;

$months = ["Jan.", "Feb.", "Mar.", "Apr.", "May", "Jun.", "Jul.", "Aug.", "Sept.", "Oct.", "Nov.", "Dec."];
$as_of_default = 'As of ' . $months[date('n') - 1] . ' ' . date('Y');

$title_map = [
    'inventory' => 'INVENTORY of SUPPLIES',
    'stocklogs' => 'STOCK MOVEMENT LOGS',
    'issuance'  => 'SUPPLY ISSUANCE LOGS',
    'offices'   => 'OFFICE REQUISITIONS SUMMARY',
];
$print_title = $title_map[$report_type] ?? 'SUPPLY REPORT';

function fmt_date_range($ds, $de) {
    $months = ["Jan.", "Feb.", "Mar.", "Apr.", "May", "Jun.", "Jul.", "Aug.", "Sept.", "Oct.", "Nov.", "Dec."];
    $fmt = function($d) use ($months) {
        if (!$d) return '';
        $dt = date_create($d);
        return $dt ? $months[(int)date_format($dt, 'n') - 1] . ' ' . date_format($dt, 'j, Y') : '';
    };
    $s = $fmt($ds); $e = $fmt($de);
    if ($s && $e) return "As of $s – $e";
    if ($s)      return "As of $s – Present";
    if ($e)      return "As of up to $e";
    return '';
}

// ─── Queries (no LIMIT / OFFSET) ──────────────────────────────
$table_html = '';

if ($report_type === 'inventory') {
    $search       = $_GET['search'] ?? '';
    $stock_status = $_GET['stock_status'] ?? [];
    $w = [];
    if (!empty($search))
        $w[] = "i.item_name LIKE '%" . safe($conn, $search) . "%'";
    if (!empty($stock_status)) {
        $sc = [];
        if (in_array('normal', $stock_status)) $sc[] = "i.current_stock > i.reorder_level";
        if (in_array('low',    $stock_status)) $sc[] = "(i.current_stock <= i.reorder_level AND i.current_stock > 0)";
        if (in_array('out',    $stock_status)) $sc[] = "i.current_stock = 0";
        if ($sc) $w[] = "(" . implode(' OR ', $sc) . ")";
    }
    $where = !empty($w) ? "WHERE " . implode(' AND ', $w) : "";
    $res = $conn->query("SELECT i.*, s.supplier_name FROM inventory i LEFT JOIN supply_supplier s ON i.supplier_id = s.supplier_id $where ORDER BY i.item_name ASC");

    ob_start(); ?>
    <table class="report-table inv-table">
        <thead>
            <tr>
                <th style="width:62%;text-align:left;">ITEMS</th>
                <th style="width:38%;text-align:right;">Quantity Available</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $unit = htmlspecialchars(trim($row['unit'] ?? ''));
                $qty_display = htmlspecialchars($row['current_stock']) . ($unit ? ' ' . $unit : '');
                $cls = $row['current_stock'] == 0 ? 'stock-out' : ($row['current_stock'] <= $row['reorder_level'] ? 'stock-warn' : ''); ?>
            <tr>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td class="<?= $cls ?>" style="text-align:right;"><?= $qty_display ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2" class="empty-msg">No items match the selected filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php $table_html = ob_get_clean();
}

if ($report_type === 'stocklogs') {
    $date_start  = $_GET['log_ds'] ?? '';
    $date_end    = $_GET['log_de'] ?? '';
    $move_types  = $_GET['move_type'] ?? [];
    $item_search = $_GET['log_item'] ?? '';
    $as_of_default = fmt_date_range($date_start, $date_end) ?: $as_of_default;

    $w = ["sl.receiver = 'Supply In-charge'"];
    if (!empty($date_start)) $w[] = "sl.date_created >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "sl.date_created <= '" . safe($conn, $date_end)   . " 23:59:59'";
    if (!empty($move_types)) {
        $mt = array_map(fn($x) => "'" . safe($conn, $x) . "'", $move_types);
        $w[] = "sl.movement_type IN (" . implode(',', $mt) . ")";
    }
    if (!empty($item_search)) {
        $esc = safe($conn, $item_search);
        $w[] = "i.item_name LIKE '%$esc%'";
    }
    $where = "WHERE " . implode(' AND ', $w);
    $res = $conn->query("SELECT sl.*, i.item_name FROM stock_logs sl LEFT JOIN inventory i ON sl.inventory_id = i.inventory_id $where ORDER BY sl.date_created DESC");

    ob_start(); ?>
    <table class="report-table">
        <thead>
            <tr>
                <th>Date & Time</th><th>Item Name</th><th>Type</th>
                <th>Qty</th><th>Prev Stock</th><th>New Stock</th><th>Notes</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($log = $res->fetch_assoc()): ?>
            <tr>
                <td><?= date('M d, Y H:i', strtotime($log['date_created'])) ?></td>
                <td><?= htmlspecialchars($log['item_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($log['movement_type']) ?></td>
                <td style="text-align:center;"><?= htmlspecialchars($log['quantity']) ?></td>
                <td style="text-align:center;"><?= htmlspecialchars($log['previous_stock']) ?></td>
                <td style="text-align:center;"><?= htmlspecialchars($log['new_stock']) ?></td>
                <td><?= htmlspecialchars($log['notes'] ?? '') ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="empty-msg">No stock movement records match the filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php $table_html = ob_get_clean();
}

if ($report_type === 'issuance') {
    $statuses   = $_GET['iss_status'] ?? [];
    $date_start = $_GET['iss_ds'] ?? '';
    $date_end   = $_GET['iss_de'] ?? '';
    $as_of_default = fmt_date_range($date_start, $date_end) ?: $as_of_default;

    $w = ["LOWER(sr.request_type) = 'consumables'"];
    if (!empty($statuses)) {
        $st = [];
        if (in_array('Pending',  $statuses)) $st[] = "sr.approved_by IS NULL AND sr.issued_by IS NULL";
        if (in_array('Approved', $statuses)) $st[] = "sr.approved_by IS NOT NULL AND sr.issued_by IS NULL";
        if (in_array('Issued',   $statuses)) $st[] = "sr.issued_by IS NOT NULL";
        if ($st) $w[] = "(" . implode(' OR ', $st) . ")";
    }
    if (!empty($date_start)) $w[] = "sr.date_requested >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "sr.date_requested <= '" . safe($conn, $date_end)   . " 23:59:59'";
    $where = "WHERE " . implode(' AND ', $w);
    $res = $conn->query("SELECT sr.*, CONCAT_WS(' ', u.first_name, u.last_name) AS requester_name FROM supply_request sr LEFT JOIN user u ON u.id = sr.user_id $where ORDER BY sr.date_requested DESC");

    ob_start(); ?>
    <table class="report-table">
        <thead>
            <tr>
                <th>Date Requested</th><th>Requester</th><th>Item Description</th>
                <th>Status</th><th>Qty Needed</th><th>Total Cost</th><th>Issued Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $status_label = 'Pending';
                if (!empty($row['issued_by']))   { $status_label = 'Issued'; }
                elseif (!empty($row['approved_by'])) { $status_label = 'Approved'; }
                elseif (!empty($row['verified_by'])) { $status_label = 'Verified'; }
                elseif (!empty($row['checked_by']))  { $status_label = 'Checked'; }
                elseif (!empty($row['noted_by']))    { $status_label = 'Noted'; }
            ?>
            <tr>
                <td><?= date('M d, Y', strtotime($row['date_requested'])) ?></td>
                <td><?= htmlspecialchars($row['requester_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['item_description'] ?? '—') ?></td>
                <td><?= $status_label ?></td>
                <td style="text-align:center;"><?= htmlspecialchars($row['quantity'] ?? '—') ?></td>
                <td style="text-align:right;">₱<?= number_format((float)($row['total_cost'] ?? 0), 2) ?></td>
                <td><?= !empty($row['issued_date']) ? date('M d, Y', strtotime($row['issued_date'])) : '—' ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="empty-msg">No issuance records match the selected filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php $table_html = ob_get_clean();
}

if ($report_type === 'offices') {
    $office     = $_GET['office'] ?? '';
    $date_start = $_GET['off_ds'] ?? '';
    $date_end   = $_GET['off_de'] ?? '';
    $as_of_default = fmt_date_range($date_start, $date_end) ?: $as_of_default;

    $w = ["sr.request_type = 'office_supply'"];
    if (!empty($office))
        $w[] = "sr.department_unit = '" . safe($conn, $office) . "'";
    if (!empty($date_start)) $w[] = "sr.date_requested >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "sr.date_requested <= '" . safe($conn, $date_end)   . " 23:59:59'";
    $where = "WHERE " . implode(' AND ', $w);
    $res = $conn->query("SELECT sr.* FROM supply_request sr $where ORDER BY sr.date_requested DESC");

    $grand_total = 0;
    ob_start(); ?>
    <table class="report-table">
        <thead>
            <tr>
                <th>Date Requested</th><th>Office / Dept</th><th>Item Name</th><th>Description</th>
                <th>Qty</th><th>Unit Cost</th><th>Total Cost</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $total = (float)($row['total_cost'] ?? 0);
                $grand_total += $total;
                $st = $row['status'] ?? 'Pending';
            ?>
            <tr>
                <td><?= date('M d, Y', strtotime($row['date_requested'])) ?></td>
                <td><?= htmlspecialchars($row['department_unit'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['item_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['request_description'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['quantity_requested'] ?? '0') ?> <?= htmlspecialchars($row['unit'] ?? '') ?></td>
                <td style="text-align:right;">₱<?= number_format((float)($row['unit_cost'] ?? 0), 2) ?></td>
                <td style="text-align:right;">₱<?= number_format($total, 2) ?></td>
                <td><?= htmlspecialchars($st) ?></td>
            </tr>
        <?php endwhile; ?>
            <tr class="total-row">
                <td colspan="6" style="text-align:right;font-weight:700;">Grand Total Cost:</td>
                <td colspan="2" style="font-weight:700;">₱<?= number_format($grand_total, 2) ?></td>
            </tr>
        <?php else: ?>
            <tr><td colspan="8" class="empty-msg">No office requisition records match the selected filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php $table_html = ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($print_title) ?> – Print</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
            padding: 1.2cm 1.5cm 1.5cm 1.5cm;
        }

        /* ── Letterhead ── */
        .letterhead {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 0;
        }
        .letterhead img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .letterhead-text { flex: 1; }
        .letterhead-text .school-name {
            font-size: 22pt;
            font-weight: 700;
            color: #073b1d;
            text-transform: uppercase;
            line-height: 1.05;
            letter-spacing: 0.5px;
        }
        .letterhead-text .school-addr {
            font-size: 9pt;
            color: #333;
            margin-top: 4px;
            line-height: 1.5;
        }
        .letterhead-text .school-contact {
            font-size: 8.5pt;
            color: #444;
            margin-top: 1px;
            line-height: 1.5;
        }

        /* ── Green banner ── */
        .lh-banner {
            background: #4a7c59;
            color: #fff;
            font-size: 8pt;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 10px;
            margin-top: 7px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Report Title ── */
        .report-title {
            text-align: center;
            margin: 20px 0 16px;
        }
        .report-title .rpt-main {
            font-family: Arial, sans-serif;
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #000;
        }
        .report-title .rpt-asof {
            font-size: 11pt;
            font-style: italic;
            color: #000;
            margin-top: 3px;
        }

        /* ── Inventory Table (2-col, ~70% centered) ── */
        .inv-wrap {
            display: flex;
            justify-content: center;
            margin-top: 8px;
        }
        .inv-table {
            width: 72%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .inv-table thead th {
            border: 1px solid #000;
            padding: 6px 10px;
            background: #fff;
        }
        .inv-table thead th:first-child {
            font-style: italic;
            font-weight: normal;
            text-align: left;
        }
        .inv-table thead th:last-child {
            font-weight: normal;
            text-align: right;
        }
        .inv-table tbody td {
            border: 1px solid #000;
            padding: 5px 10px;
            vertical-align: middle;
        }
        .inv-table tbody td:first-child { text-align: left; }
        .inv-table tbody td:last-child  { text-align: right; }

        /* ── Multi-column Tables (stocklogs, issuance, offices) ── */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 8px;
        }
        .report-table thead th {
            border: 1px solid #000;
            padding: 6px 8px;
            background: #fff;
            font-weight: 700;
            text-align: center;
        }
        .report-table thead th:first-child { text-align: left; }
        .report-table tbody td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: middle;
        }
        .report-table tbody tr:nth-child(even) { background: #f9f9f9; }
        .total-row td { background: #f0f0f0 !important; }

        /* ── Stock Colors ── */
        .stock-out  { color: #842029; }
        .stock-warn { color: #c67c00; }

        .empty-msg { text-align: center; padding: 14px; color: #666; }

        @media print {
            body { padding: 0; }
            @page { margin: 1.2cm 1.5cm 1.5cm 1.5cm; }
            .inv-table, .report-table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <!-- ── Letterhead ── -->
    <?php if ($print_header): ?>
    <div class="letterhead">
        <img src="../<?= htmlspecialchars($print_header['logo_path']) ?>"
             alt="School Logo"
             onerror="this.src='../assets/images/logo.png'">
        <div class="letterhead-text">
            <div class="school-name"><?= htmlspecialchars(strtoupper($print_header['school_name'])) ?></div>
            <div class="school-addr"><?= htmlspecialchars($print_header['address']) ?></div>
            <div class="school-contact"><?php
                $parts = [];
                if (!empty($print_header['telephone_number'])) $parts[] = 'Tel. No. ' . $print_header['telephone_number'];
                if (!empty($print_header['fax_number']))       $parts[] = 'Fax No. ' . $print_header['fax_number'];
                if (!empty($print_header['mobile_number']))    $parts[] = 'Mobile: '  . $print_header['mobile_number'];
                echo htmlspecialchars(implode(' / ', $parts));
            ?></div>
        </div>
    </div>
    <div class="lh-banner">
        <span>Email Address: <?= htmlspecialchars($print_header['email_address']) ?></span>
        <span>Website: <?= htmlspecialchars($print_header['website']) ?></span>
    </div>
    <?php endif; ?>

    <!-- ── Report Title ── -->
    <div class="report-title">
        <div class="rpt-main"><?= htmlspecialchars($print_title) ?></div>
        <div class="rpt-asof"><?= htmlspecialchars($as_of_default) ?></div>
    </div>

    <!-- ── Table ── -->
    <?php if ($report_type === 'inventory'): ?>
    <div class="inv-wrap"><?= $table_html ?></div>
    <?php else: ?>
    <?= $table_html ?>
    <?php endif; ?>

    <script>
        window.onload = function () {
            window.print();
            window.onafterprint = function () { window.close(); };
        };
    </script>
</body>
</html>
