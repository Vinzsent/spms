<?php
$pageTitle = 'Supply Reports';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));
if (!in_array($user_type, ['supplyincharge', 'admin'])) {
    $_SESSION['error'] = 'Access denied. You do not have permission to view Supply Reports.';
    header("Location: ../dashboard.php");
    exit;
}

// Fetch printer header settings for print letterhead
$header_query = $conn->query("SELECT * FROM printer_header_settings WHERE id = 1");
$print_header = $header_query->fetch_assoc();
?>
<style>
:root {
    --pg: #073b1d;
    --pg2: #0a4f28;
    --acc: #EACA26;
    --bg: #f4f6f9;
    --bd: #dee2e6;
}
body { font-family:'Segoe UI',sans-serif; background:var(--bg); }


/* ─── Page Layout ─── */
.wrap { margin-left: 280px; min-height: 100vh; padding: 20px; }
.ph {
    background:linear-gradient(135deg,var(--pg),var(--pg2));
    color:#fff; padding:20px 28px;
}
.ph h1 { font-size:1.35rem; margin:0; }
.ph p  { margin:3px 0 0; font-size:.85rem; opacity:.85; }

/* ─── Tabs ─── */
.rpt-tabs {
    display:flex; background:#fff; border-bottom:2px solid var(--bd);
    padding:0 20px;
}
.rpt-tab {
    padding:13px 20px; cursor:pointer; font-weight:600; font-size:.875rem;
    color:#666; border-bottom:3px solid transparent; margin-bottom:-2px;
    transition:.2s; display:flex; align-items:center; gap:7px;
}
.rpt-tab:hover { color:var(--pg); }
.rpt-tab.active { color:var(--pg); border-bottom-color:var(--pg); }

/* ─── Report Panel ─── */
.rpt-panel { display:none; }
.rpt-panel.active { display:flex; }

.filter-col {
    width:260px; min-width:260px; background:#fff;
    border-right:1px solid var(--bd); padding:20px;
    min-height:calc(100vh - 130px);
}
.filter-col h5 { font-size:.88rem; font-weight:700; color:var(--pg); margin-bottom:16px; }
.fg { margin-bottom:16px; }
.fg .gl {
    display:block; font-size:.73rem; font-weight:700;
    color:#666; text-transform:uppercase; letter-spacing:.4px; margin-bottom:6px;
}
.fg .form-control, .fg .form-select { font-size:.83rem; }
.ci { display:flex; align-items:flex-start; gap:8px; margin-bottom:6px; font-size:.84rem; }
.ci input[type=checkbox] { accent-color:var(--pg); width:14px; height:14px; flex-shrink:0; margin-top:2px; }
.btn-prev {
    width:100%; padding:9px; border:none; border-radius:6px;
    background:var(--pg); color:#fff; font-weight:600;
    font-size:.875rem; cursor:pointer; transition:.2s; margin-top:6px;
}
.btn-prev:hover { background:var(--pg2); }
.note-warn  { color:#856404; font-size:.75rem; }
.note-danger{ color:#842029; font-size:.75rem; }

/* ─── Preview ─── */
.preview-col { flex:1; padding:20px 24px; overflow-x:auto; }
.ptbar {
    display:flex; justify-content:space-between; align-items:center;
    margin-bottom:14px; flex-wrap:wrap; gap:8px;
}
.ptbar h5 { font-size:.9rem; font-weight:700; color:var(--pg); margin:0; }
.exp-btns { display:flex; gap:7px; }
.bx {
    display:flex; align-items:center; gap:5px; padding:6px 13px;
    border-radius:5px; font-size:.82rem; font-weight:600; border:none; cursor:pointer;
    transition:.2s;
}
.bx.pdf  { background:#c0392b; color:#fff; }
.bx.xlsx { background:#1a7431; color:#fff; }
.bx:hover:not(:disabled) { filter:brightness(1.12); }
.bx:disabled { opacity:.4; cursor:default; }

.box {
    background:#fff; border:1px solid var(--bd); border-radius:8px;
    padding:26px; min-height:260px;
}
.ph-msg { text-align:center; padding:60px 0; color:#aaa; }
.ph-msg i { font-size:2.5rem; display:block; margin-bottom:10px; }
.spinner { text-align:center; padding:60px; color:#888; }

.rmeta { border-bottom:2px solid var(--pg); padding-bottom:10px; margin-bottom:18px; }
.rmeta h2 { color:var(--pg); font-size:1.15rem; margin:0; }
.rmeta p  { font-size:.76rem; color:#666; margin:3px 0 0; }

.section-title {
    font-size:.9rem; font-weight:700; color:var(--pg);
    border-left:4px solid var(--acc); padding-left:9px; margin:18px 0 10px;
}

.report-table { font-size:.79rem; border-collapse:collapse; }
.report-table thead th {
    background:var(--pg); color:#fff; padding:7px 9px; white-space:nowrap;
}
.report-table tbody td { padding:6px 9px; vertical-align:middle; border-bottom:1px solid #f0f0f0; }
.report-table tbody tr:nth-child(even) { background:#f8f9fa; }
.badge-in    { background:#28a745; color:#fff; padding:2px 8px; border-radius:4px; font-size:.75rem; }
.badge-out   { background:#dc3545; color:#fff; padding:2px 8px; border-radius:4px; font-size:.75rem; }
.badge-adj   { background:#ffc107; color:#333; padding:2px 8px; border-radius:4px; font-size:.75rem; }
.badge-decomm{ background:#6c757d; color:#fff; padding:2px 8px; border-radius:4px; font-size:.75rem; }
.stock-warn  { color:#856404; font-weight:700; }
.stock-out   { color:#842029; font-weight:700; }
.print-spacer-row { display: none; }

/* ─── Print Styles ─── */
.print-header-container { display: none; }
.print-title-section    { display: none; }

@media print {
    @page {
        margin: 0;
    }

    /* Hide all UI chrome */
    .sidebar, .filter-col, .ph, .rpt-tabs, .ptbar,
    .exp-btns, .no-print, .section-title, .rmeta { display: none !important; }

    body {
        margin: 0 !important;
        background: #fff !important;
    }
    .wrap        { margin: 0 !important; padding: 0 1.5cm 1.5cm 1.5cm !important; }
    tr           { break-inside: avoid !important; }
    .print-spacer-row {
        display: table-row !important;
    }
    .report-table thead .print-spacer-row th,
    .report-table thead .print-spacer-row td {
        border: none !important;
        background: transparent !important;
        height: 1.5cm !important;
        padding: 0 !important;
    }
    .rpt-panel   { display: none !important; }
    .rpt-panel.active { display: block !important; }
    .preview-col { padding: 0 !important; }
    .box         { border: none !important; padding: 0 !important; box-shadow: none !important; }

    /* ── Letterhead Header ── */
    .print-header-container {
        display: block !important;
        width: 100% !important;
        margin-top: 1.5cm !important;
        margin-bottom: 18px !important;
        padding-bottom: 0 !important;
    }
    .letterhead-container {
        display: flex !important;
        align-items: center !important;
        gap: 18px !important;
        margin-bottom: 10px !important;
    }
    .letterhead-logo {
        width: 85px !important;
        height: 85px !important;
        object-fit: contain !important;
        display: block !important;
    }
    .letterhead-details { flex: 1 !important; }
    .letterhead-title {
        font-family: Arial, sans-serif !important;
        font-weight: 700 !important;
        font-size: 22pt !important;
        color: #073b1d !important;
        margin: 0 !important;
        text-transform: uppercase !important;
        line-height: 1.1 !important;
    }
    .letterhead-text {
        font-size: 9pt !important;
        color: #333 !important;
        margin: 3px 0 0 0 !important;
    }
    .letterhead-subtext {
        font-size: 8pt !important;
        color: #444 !important;
        margin: 2px 0 0 0 !important;
    }
    .letterhead-banner {
        background-color: #4a7c59 !important;
        color: white !important;
        padding: 5px 12px !important;
        font-size: 8pt !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-radius: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* ── Report Title Block ── */
    .print-title-section {
        display: block !important;
        text-align: center !important;
        margin: 18px 0 10px !important;
    }
    .print-title-section .rpt-main-title {
        font-family: Arial, sans-serif !important;
        font-size: 13pt !important;
        font-weight: 700 !important;
        color: #000 !important;
        margin: 0 !important;
        letter-spacing: 0.5px !important;
    }
    .print-title-section .rpt-as-of {
        font-size: 11pt !important;
        color: #000 !important;
        margin: 2px 0 0 0 !important;
        font-weight: 400 !important;
    }

    /* ── Inventory Table — image-faithful design ── */
    .report-table {
        border-collapse: collapse !important;
        font-family: Arial, sans-serif !important;
        font-size: 10pt !important;
        margin-top: 0 !important;
    }
    #panel-inventory .report-table {
        width: 80% !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }
    #panel-stocklogs .report-table,
    #panel-issuance .report-table,
    #panel-offices .report-table {
        width: 100% !important;
    }
    .report-table thead th {
        background: #fff !important;
        color: #000 !important;
        border: 1px solid #000 !important;
        padding: 6px 8px !important;
        font-weight: 700 !important;
        font-size: 10pt !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .report-table thead th:first-child {
        text-align: center !important;
        font-style: italic !important;
        font-weight: normal !important;
        width: 55% !important;
    }
    .report-table thead th:not(:first-child) {
        text-align: center !important;
        font-weight: normal !important;
    }
    .report-table tbody td {
        border: 1px solid #000 !important;
        padding: 5px 8px !important;
        color: #000 !important;
        background: #fff !important;
        vertical-align: middle !important;
    }
    #panel-inventory .report-table tbody td:first-child {
        text-align: left !important;
    }
    #panel-inventory .report-table tbody td:not(:first-child) {
        text-align: center !important;
    }
    #panel-stocklogs .report-table tbody td,
    #panel-issuance .report-table tbody td,
    #panel-offices .report-table tbody td {
        text-align: left !important;
    }
    #panel-stocklogs .report-table tbody td:nth-child(4),
    #panel-stocklogs .report-table tbody td:nth-child(5),
    #panel-stocklogs .report-table tbody td:nth-child(6),
    #panel-issuance .report-table tbody td:nth-child(5),
    #panel-issuance .report-table tbody td:nth-child(6),
    #panel-offices .report-table tbody td:nth-child(5),
    #panel-offices .report-table tbody td:nth-child(6),
    #panel-offices .report-table tbody td:nth-child(7) {
        text-align: right !important;
        padding-right: 12px !important;
    }
    .report-table tbody tr:nth-child(even) {
        background: #fff !important;
    }
    /* Remove coloured stock badges in print */
    .stock-warn, .stock-out {
        color: #000 !important;
        font-weight: 400 !important;
    }
    /* Remove badge pills */
    .badge-in, .badge-out, .badge-adj, .badge-decomm {
        background: none !important;
        color: #000 !important;
        padding: 0 !important;
        border-radius: 0 !important;
        font-size: 10pt !important;
    }
}
</style>

<?php include '../includes/sidebar.php'; ?>

<div class="wrap">
    <!-- Print Header (Hidden on screen, visible only in print) -->
    <?php if ($print_header): ?>
    <div class="print-header-container">
        <div class="letterhead-container">
            <img class="letterhead-logo" src="../<?= htmlspecialchars($print_header['logo_path']) ?>" alt="Logo" onerror="this.src='../assets/images/logo.png'">
            <div class="letterhead-details">
                <h2 class="letterhead-title"><?= htmlspecialchars(strtoupper($print_header['school_name'])) ?></h2>
                <p class="letterhead-text"><?= htmlspecialchars($print_header['address']) ?></p>
                <p class="letterhead-subtext">
                    <?php 
                    $tel_fax = [];
                    if (!empty($print_header['telephone_number'])) $tel_fax[] = 'Tel. No. ' . $print_header['telephone_number'];
                    if (!empty($print_header['fax_number'])) $tel_fax[] = 'Fax No. ' . $print_header['fax_number'];
                    if (!empty($print_header['mobile_number'])) $tel_fax[] = 'Mobile: ' . $print_header['mobile_number'];
                    echo htmlspecialchars(implode(' / ', $tel_fax));
                    ?>
                </p>
            </div>
        </div>
        <div class="letterhead-banner">
            <span>Email Address: <?= htmlspecialchars($print_header['email_address']) ?></span>
            <span>Website: <?= htmlspecialchars($print_header['website']) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Print Title (Hidden on screen, shown only when printing) -->
    <div class="print-title-section" id="print-title-section">
        <p class="rpt-main-title" id="print-rpt-title">INVENTORY of SUPPLIES</p>
        <p class="rpt-as-of" id="print-rpt-asof">As of <?php $m = ["Jan.", "Feb.", "Mar.", "Apr.", "May", "Jun.", "Jul.", "Aug.", "Sept.", "Oct.", "Nov.", "Dec."]; echo $m[date('n') - 1] . ' ' . date('Y'); ?></p>
    </div>

    <div class="ph no-print">
        <h1><i class="fas fa-chart-bar me-2"></i>Supply Reports & Analytics</h1>
        <p>Filter, preview, and export supply inventory and movement logs</p>
    </div>

    <!-- ══ TABS ══ -->
    <div class="rpt-tabs no-print">
        <div class="rpt-tab active" onclick="switchTab('inventory',this)">
            <i class="fas fa-boxes"></i> Supply Inventory
        </div>
        <div class="rpt-tab" onclick="switchTab('stocklogs',this)">
            <i class="fas fa-exchange-alt"></i> Stock Movement Logs
        </div>
        <div class="rpt-tab" onclick="switchTab('issuance',this)">
            <i class="fas fa-hand-holding"></i> Issuance Logs
        </div>
        <div class="rpt-tab" onclick="switchTab('offices',this)">
            <i class="fas fa-building"></i> Office Requisitions
        </div>
    </div>


    <!-- ══════════════════════════════════════════
         PANEL 1 — SUPPLY INVENTORY
    ══════════════════════════════════════════ -->
    <div class="rpt-panel active" id="panel-inventory">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Item Search</label>
                <input type="text" id="inv_search" class="form-control form-control-sm" placeholder="Enter item name...">
            </div>
            <div class="fg">
                <label class="gl">Stock Status</label>
                <div class="ci"><input type="checkbox" id="inv_st_n" value="normal" class="inv-stock" checked><label for="inv_st_n">Normal</label></div>
                <div class="ci"><input type="checkbox" id="inv_st_l" value="low"    class="inv-stock"><label for="inv_st_l">Low Stock <span class="note-warn">(⚠ below reorder)</span></label></div>
                <div class="ci"><input type="checkbox" id="inv_st_o" value="out"    class="inv-stock"><label for="inv_st_o">Out of Stock <span class="note-danger">(⚠ critical)</span></label></div>
            </div>
            <button class="btn-prev" onclick="preview('inventory')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf"  id="inv-pdf"  onclick="doPDF()"        disabled><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" id="inv-xlsx" onclick="doCSV('panel-inventory','supply_inventory')" disabled><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="inv-box">
                <div class="ph-msg"><i class="fas fa-boxes"></i><p>Set filters and click <strong>Preview</strong>.</p></div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         PANEL 2 — STOCK MOVEMENT LOGS
    ══════════════════════════════════════════ -->
    <div class="rpt-panel" id="panel-stocklogs">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Item Search</label>
                <input type="text" id="log_item" class="form-control form-control-sm" placeholder="Item name…">
            </div>
            <div class="fg">
                <label class="gl">Movement Type</label>
                <div class="ci"><input type="checkbox" id="mt_in"  value="IN"         class="log-mt" checked><label for="mt_in">Stock IN</label></div>
                <div class="ci"><input type="checkbox" id="mt_out" value="OUT"        class="log-mt" checked><label for="mt_out">Stock OUT</label></div>
                <div class="ci"><input type="checkbox" id="mt_adj" value="ADJUSTMENT" class="log-mt"><label for="mt_adj">Adjustment</label></div>
            </div>
            <div class="fg">
                <label class="gl">Date Range</label>
                <small class="text-muted">From</small>
                <input type="date" id="log_ds" class="form-control form-control-sm mb-1">
                <small class="text-muted">To</small>
                <input type="date" id="log_de" class="form-control form-control-sm">
            </div>
            <button class="btn-prev" onclick="preview('stocklogs')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf"  id="log-pdf"  onclick="doPDF()" disabled><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" id="log-xlsx" onclick="doCSV('panel-stocklogs','supply_stock_movement_logs')" disabled><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="log-box">
                <div class="ph-msg"><i class="fas fa-exchange-alt"></i><p>Set filters and click <strong>Preview</strong>.</p></div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         PANEL 3 — ISSUANCE LOGS
    ══════════════════════════════════════════ -->
    <div class="rpt-panel" id="panel-issuance">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Status</label>
                <div class="ci"><input type="checkbox" id="iss_p"  value="Pending"  class="iss-st"><label for="iss_p">Pending / Unapproved</label></div>
                <div class="ci"><input type="checkbox" id="iss_a"  value="Approved" class="iss-st" checked><label for="iss_a">Approved (Not Issued)</label></div>
                <div class="ci"><input type="checkbox" id="iss_i"  value="Issued"   class="iss-st" checked><label for="iss_i">Issued / Completed</label></div>
            </div>
            <div class="fg">
                <label class="gl">Date Range (Requested)</label>
                <small class="text-muted">From</small>
                <input type="date" id="iss_ds" class="form-control form-control-sm mb-1">
                <small class="text-muted">To</small>
                <input type="date" id="iss_de" class="form-control form-control-sm">
            </div>
            <button class="btn-prev" onclick="preview('issuance')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf"  id="iss-pdf"  onclick="doPDF()" disabled><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" id="iss-xlsx" onclick="doCSV('panel-issuance','supply_issuance_logs')" disabled><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="iss-box">
                <div class="ph-msg"><i class="fas fa-hand-holding"></i><p>Set filters and click <strong>Preview</strong>.</p></div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         PANEL 4 — OFFICE REQUISITIONS
    ══════════════════════════════════════════ -->
    <div class="rpt-panel" id="panel-offices">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Select Office</label>
                <select id="off_select" class="form-select form-select-sm">
                    <option value="">-- All Offices --</option>
                    <?php
                    $offices = ["Admin Office", "BSBA", "BSHM", "ELEMENTARY DEPT. BASIC EDUCATION", "JHS/ BASIC EDUCATION", "CELA OFFICE", "CES", "CJE", "CLINIC", "FINANCE/ ACCOUNTING", "GSO/ Security officer", "GUIDANCE/ Chaplain", "HUMAN RESOURCE MANAGEMENT", "ITE Program", "LIBRARY", "MIS", "NSTP", "OSAS", "PHOTOCOPY ROOM", "PRESIDENT'S OFFICE", "Property Custodian", "REGISTRAR'S OFFICE", "SENIOR HIGH SCHOOL PROGRAM", "SUPPLY ROOM", "VPAA OFFICE", "OSSD", "MAIN LIBRARY", "BED LIBRARY"];
                    sort($offices);
                    foreach($offices as $o) echo "<option value=\"$o\">$o</option>";
                    ?>
                </select>
            </div>
            <div class="fg">
                <label class="gl">Date Range</label>
                <small class="text-muted">From</small>
                <input type="date" id="off_ds" class="form-control form-control-sm mb-1">
                <small class="text-muted">To</small>
                <input type="date" id="off_de" class="form-control form-control-sm">
            </div>
            <button class="btn-prev" onclick="preview('offices')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf"  id="off-pdf"  onclick="doPDF()" disabled><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" id="off-xlsx" onclick="doCSV('panel-offices','office_requisitions_report')" disabled><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="off-box">
                <div class="ph-msg"><i class="fas fa-building"></i><p>Select an office and click <strong>Preview</strong>.</p></div>
            </div>
        </div>
    </div>

</div><!-- .wrap -->

<script>
// ── Tab switching ────────────────────────────────────────────
function switchTab(type, el) {
    document.querySelectorAll('.rpt-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.rpt-panel').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('panel-' + type).classList.add('active');
}

// ── Pagination state ─────────────────────────────────────────
const currentPages = { inventory: 1, stocklogs: 1, issuance: 1, offices: 1 };

// ── Build params per report type ─────────────────────────────
function buildParams(type) {
    const p = new URLSearchParams({ report_type: type });
    p.append('page', currentPages[type] || 1);
    if (type === 'inventory') {
        const srch = document.getElementById('inv_search').value;
        if (srch) p.append('search', srch);
        document.querySelectorAll('.inv-stock:checked').forEach(e => p.append('stock_status[]', e.value));
    }
    if (type === 'stocklogs') {
        const it = document.getElementById('log_item').value;
        if (it) p.append('log_item', it);
        document.querySelectorAll('.log-mt:checked').forEach(e => p.append('move_type[]', e.value));
        const ds = document.getElementById('log_ds').value;
        const de = document.getElementById('log_de').value;
        if (ds) p.append('log_ds', ds);
        if (de) p.append('log_de', de);
    }
    if (type === 'issuance') {
        document.querySelectorAll('.iss-st:checked').forEach(e => p.append('iss_status[]', e.value));
        const ds = document.getElementById('iss_ds').value;
        const de = document.getElementById('iss_de').value;
        if (ds) p.append('iss_ds', ds);
        if (de) p.append('iss_de', de);
    }
    if (type === 'offices') {
        const off = document.getElementById('off_select').value;
        if (off) p.append('office', off);
        const ds = document.getElementById('off_ds').value;
        const de = document.getElementById('off_de').value;
        if (ds) p.append('off_ds', ds);
        if (de) p.append('off_de', de);
    }
    return p;
}


// ── Preview ──────────────────────────────────────────────────
const boxMap  = { inventory:'inv-box',  stocklogs:'log-box', issuance:'iss-box',  offices:'off-box' };
const pdfMap  = { inventory:'inv-pdf',  stocklogs:'log-pdf', issuance:'iss-pdf',  offices:'off-pdf' };
const xlsMap  = { inventory:'inv-xlsx', stocklogs:'log-xlsx',issuance:'iss-xlsx', offices:'off-xlsx' };
const titleMap= { inventory:'Supply Inventory Report', stocklogs:'Supply Stock Movement Logs', issuance:'Supply Issuance Logs', offices:'Office Requisitions Summary' };


function preview(type, page = 1) {
    currentPages[type] = page;
    const box  = document.getElementById(boxMap[type]);
    const bPdf = document.getElementById(pdfMap[type]);
    const bXls = document.getElementById(xlsMap[type]);
    box.innerHTML = '<div class="spinner"><i class="fas fa-spinner fa-spin fa-2x d-block mb-2"></i>Loading…</div>';
    bPdf.disabled = bXls.disabled = true;

    const now = new Date().toLocaleString('en-PH', {dateStyle:'long', timeStyle:'short'});

    fetch('../actions/get_filtered_supply_reports.php?' + buildParams(type).toString())
        .then(r => r.text())
        .then(html => {
            box.innerHTML = `<div class="rmeta"><h2>${titleMap[type]}</h2><p>Generated: ${now}</p></div>` + html;
            bPdf.disabled = bXls.disabled = false;
        })
        .catch(() => {
            box.innerHTML = '<div class="ph-msg"><i class="fas fa-exclamation-circle text-danger"></i><p>Failed to load. Please retry.</p></div>';
        });
}

// ── PDF ──────────────────────────────────────────────────────
function doPDF() {
    // Determine active tab
    const activeTab = document.querySelector('.rpt-tab.active');
    let activeType = 'inventory';
    if (activeTab) {
        const onclick = activeTab.getAttribute('onclick') || '';
        const match = onclick.match(/switchTab\('(\w+)'/);
        if (match) activeType = match[1];
    }
    // Build params but exclude the pagination page param
    const params = buildParams(activeType);
    params.delete('page');
    window.open('../pages/print_supply_report.php?' + params.toString(), '_blank');
}


// ── CSV ──────────────────────────────────────────────────────
function doCSV(panelId, filename) {
    const tables = document.querySelectorAll('#' + panelId + ' .report-table');
    if (!tables.length) { alert('Generate a preview first.'); return; }

    function escCell(val) {
        val = String(val).replace(/"/g, '""');
        return '"' + val + '"';
    }

    let csv = '';
    tables.forEach((t, i) => {
        if (i > 0) csv += '\n';
        t.querySelectorAll('tr').forEach(row => {
            const cells = [...row.querySelectorAll('th,td')].map(c => escCell(c.innerText.trim()));
            csv += cells.join(',') + '\n';
        });
    });

    // UTF-8 BOM so Excel reads encoding correctly
    const bom  = '\uFEFF';
    const blob = new Blob([bom + csv], { type: 'text/csv;charset=utf-8;' });
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(blob);
    a.download = (filename || 'report') + '_' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(a.href);
}
</script>
<?php include '../includes/footer.php'; ?>
