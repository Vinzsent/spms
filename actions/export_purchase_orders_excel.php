<?php
// Excel export (HTML table) for purchase orders
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '1');
include '../includes/auth.php';
include '../includes/db.php';

// Fetch purchase order records with user information (same query as list page)
$po_query = "
    SELECT 
        p.po_id,
        p.po_number,
        p.po_date,
        p.supplier_name,
        p.supplier_address,
        p.total_amount,
        p.status,
        p.notes,
        p.created_at,
        CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
        COUNT(poi.poi_id) as item_count
    FROM purchase_orders p
    LEFT JOIN user u ON p.created_by = u.id
    LEFT JOIN purchase_order_items poi ON p.po_id = poi.po_id
    GROUP BY p.po_id
    ORDER BY p.created_at DESC
";

$result = $conn->query($po_query);

while (ob_get_level() > 0) {
    ob_end_clean();
}
$filename = 'purchase_orders_export_' . date('Ymd_His') . '.xls';
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

echo "\xEF\xBB\xBF"; // UTF-8 BOM

$esc = function ($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$fmt = function ($d) {
    if (!$d || $d === '0000-00-00' || $d === '0000-00-00 00:00:00') return '';
    $t = strtotime($d);
    return $t ? date('M d, Y', $t) : $d;
};

?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">

<head>
    <meta charset="UTF-8" />
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #073b1d;
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin: 0 0 20px 0;
        }

        table.export {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #073b1d;
            color: #ffffff;
            font-weight: bold;
            padding: 10px 5px;
            border: 1px solid #000;
        }

        td {
            padding: 8px 5px;
            border: 1px solid #ccc;
            vertical-align: middle;
        }

        .text {
            mso-number-format: "\@";
        }

        .currency {
            mso-number-format: "\#\,\#\#0\.00";
        }

        .nowrap {
            white-space: nowrap;
        }

        .status {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="title">Purchase Order List Report</div>
    <div class="subtitle">Generated on: <?= $esc(date('M d, Y H:i')) ?></div>

    <table class="export">
        <thead>
            <tr>
                <th>PO Number</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Address</th>
                <th>Total Amount</th>
                <th>Items</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text"><?= $esc($row['po_number']) ?></td>
                        <td class="nowrap"><?= $esc($fmt($row['po_date'])) ?></td>
                        <td class="text"><?= $esc($row['supplier_name']) ?></td>
                        <td class="text"><?= $esc($row['supplier_address']) ?></td>
                        <td class="currency">₱<?= number_format($row['total_amount'], 2) ?></td>
                        <td style="text-align: center;"><?= $esc($row['item_count']) ?></td>
                        <td class="status"><?= $esc($row['status']) ?></td>
                        <td><?= $esc($row['created_by_name']) ?></td>
                        <td class="nowrap"><?= $esc(date('M d, Y g:i A', strtotime($row['created_at']))) ?></td>
                    </tr>
                <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No purchase orders found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
<?php
$conn->close();
?>