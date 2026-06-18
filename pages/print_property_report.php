<?php
include '../includes/auth.php';
include '../includes/db.php';

$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));
if (!in_array($user_type, ['propertycustodian', 'admin'])) {
    http_response_code(403); exit('Access denied.');
}

function safe($conn, $v) { return $conn->real_escape_string(trim($v)); }

$report_type = $_GET['report_type'] ?? 'inventory';

// Fetch letterhead
$header_query = $conn->query("SELECT * FROM printer_header_settings WHERE id = 1");
$print_header = $header_query ? $header_query->fetch_assoc() : null;

$months = ["Jan.", "Feb.", "Mar.", "Apr.", "May", "Jun.", "Jul.", "Aug.", "Sept.", "Oct.", "Nov.", "Dec."];
$as_of_default = 'As of ' . $months[date('n') - 1] . ' ' . date('Y');

// ─── Build title and date ──────────────────────────────────────
$title_map = [
    'inventory' => 'INVENTORY of PROPERTIES',
    'stocklogs' => 'STOCK MOVEMENT LOGS',
    'aircon'    => 'AIRCON INVENTORY REPORT',
];
$print_title = $title_map[$report_type] ?? 'PROPERTY REPORT';

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
    $category     = $_GET['category'] ?? '';
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
    $res = $conn->query("SELECT i.*, s.supplier_name FROM property_inventory i LEFT JOIN supplier s ON i.supplier_id = s.supplier_id $where ORDER BY i.item_name ASC");

    $grand_total = 0;
    ob_start(); ?>
    <table class="report-table">
        <thead>
            <tr>
                <th>Item Name</th><th>Category</th><th>Stock</th><th>Reorder Lvl</th>
                <th>Unit</th><th>Brand</th><th>Type</th><th>Status</th>
                <th>Unit Cost</th><th>Total Value</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $tv = $row['current_stock'] * ($row['unit_cost'] ?? 0);
                $grand_total += $tv; ?>
            <tr>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td style="text-align:center;"><?= $row['current_stock'] ?></td>
                <td style="text-align:center;"><?= $row['reorder_level'] ?></td>
                <td><?= htmlspecialchars($row['unit']) ?></td>
                <td><?= htmlspecialchars($row['brand'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['type'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td style="text-align:right;">₱<?= number_format($row['unit_cost'] ?? 0, 2) ?></td>
                <td style="text-align:right;">₱<?= number_format($tv, 2) ?></td>
            </tr>
        <?php endwhile; ?>
            <tr class="total-row">
                <td colspan="9" style="text-align:right;font-weight:700;">Total Valuation:</td>
                <td style="text-align:right;font-weight:700;">₱<?= number_format($grand_total, 2) ?></td>
            </tr>
        <?php else: ?>
            <tr><td colspan="10" class="empty-msg">No items match the selected filters.</td></tr>
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

    $w = ["sl.receiver = 'Property Custodian'"];
    if (!empty($date_start)) $w[] = "sl.date_created >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "sl.date_created <= '" . safe($conn, $date_end)   . " 23:59:59'";
    if (!empty($move_types)) {
        $mt = array_map(fn($x) => "'" . safe($conn, $x) . "'", $move_types);
        $w[] = "sl.movement_type IN (" . implode(',', $mt) . ")";
    }
    if (!empty($item_search)) {
        $esc = safe($conn, $item_search);
        $w[] = "pi.item_name LIKE '%$esc%'";
    }
    $where = "WHERE " . implode(' AND ', $w);
    $res = $conn->query("SELECT sl.*, pi.item_name, pi.unit FROM property_stock_logs sl LEFT JOIN property_inventory pi ON sl.inventory_id = pi.inventory_id $where ORDER BY sl.date_created DESC");

    ob_start(); ?>
    <table class="report-table">
        <thead>
            <tr>
                <th>Date & Time</th><th>Item Name</th><th>Unit</th><th>Type</th>
                <th>Qty</th><th>Prev Stock</th><th>New Stock</th><th>Requester</th><th>Notes</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($log = $res->fetch_assoc()):
                $label = $log['movement_type']; ?>
            <tr>
                <td><?= date('M d, Y H:i', strtotime($log['date_created'])) ?></td>
                <td><?= htmlspecialchars($log['item_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($log['unit'] ?? '—') ?></td>
                <td><?= htmlspecialchars($label) ?></td>
                <td style="text-align:center;"><?= $log['quantity'] ?></td>
                <td style="text-align:center;"><?= $log['previous_stock'] ?></td>
                <td style="text-align:center;"><?= $log['new_stock'] ?></td>
                <td><?= htmlspecialchars($log['requester_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($log['notes'] ?? '') ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9" class="empty-msg">No stock movement records match the filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php $table_html = ob_get_clean();
}

if ($report_type === 'aircon') {
    $campus     = strtoupper(trim($_GET['ac_campus'] ?? ''));
    $statuses   = $_GET['ac_status'] ?? [];
    $date_start = $_GET['ac_ds'] ?? '';
    $date_end   = $_GET['ac_de'] ?? '';
    $as_of_default = fmt_date_range($date_start, $date_end) ?: $as_of_default;

    $w = [];
    if ($campus === 'BED' || $campus === 'TED')
        $w[] = "TRIM(UPPER(a.campus)) = '" . safe($conn, $campus) . "'";
    if (!empty($statuses)) {
        $st = array_map(fn($x) => "'" . safe($conn, $x) . "'", $statuses);
        $w[] = "a.status IN (" . implode(',', $st) . ")";
    }
    if (!empty($date_start)) $w[] = "a.date_created >= '" . safe($conn, $date_start) . " 00:00:00'";
    if (!empty($date_end))   $w[] = "a.date_created <= '" . safe($conn, $date_end)   . " 23:59:59'";
    $where = !empty($w) ? "WHERE " . implode(' AND ', $w) : '';
    $res = $conn->query("SELECT a.*, s.supplier_name FROM aircons a LEFT JOIN supplier s ON a.supplier_id = s.supplier_id $where ORDER BY a.campus, a.location, a.brand ASC");

    ob_start(); ?>
    <table class="report-table">
        <thead>
            <tr>
                <th>Campus</th><th>Brand</th><th>Model</th><th>Type</th>
                <th>Capacity</th><th>Serial No.</th><th>Location</th><th>Status</th>
                <th>Purchase Date</th><th>Last Service</th><th>Warranty Expiry</th><th>Purchase Price</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0):
            while ($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['campus'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['brand'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['model'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['type'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['capacity'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['serial_number'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['location'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= $row['purchase_date'] ? date('M d, Y', strtotime($row['purchase_date'])) : '—' ?></td>
                <td><?= $row['last_service_date'] ? date('M d, Y', strtotime($row['last_service_date'])) : '—' ?></td>
                <td><?= $row['warranty_expiry'] ? date('M d, Y', strtotime($row['warranty_expiry'])) : '—' ?></td>
                <td style="text-align:right;">₱<?= number_format($row['purchase_price'] ?? 0, 2) ?></td>
            </tr>
        <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="12" class="empty-msg">No aircon records match the selected filters.</td></tr>
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

        /* ── Tables ── */
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
        .stock-out  { color: #842029; }
        .stock-warn { color: #c67c00; }
        .empty-msg  { text-align: center; padding: 14px; color: #666; }

        @media print {
            body { padding: 0; }
            @page { margin: 1.2cm 1.5cm 1.5cm 1.5cm; }
            .report-table { page-break-inside: auto; }
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
    <?= $table_html ?>

    <script>
        window.onload = function () {
            window.print();
            window.onafterprint = function () { window.close(); };
        };
    </script>
</body>
</html>
