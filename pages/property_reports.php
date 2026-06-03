<?php
$pageTitle = 'Property Reports';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));
if (!in_array($user_type, ['propertycustodian', 'admin'])) {
    $_SESSION['error'] = 'Access denied.';
    header("Location: ../dashboard.php");
    exit;
}

$category_list = [];
$r = $conn->query("SELECT DISTINCT category FROM property_inventory WHERE receiver='Property Custodian' AND category IS NOT NULL AND category!='' ORDER BY category ASC");
if ($r) while ($c = $r->fetch_assoc()) $category_list[] = $c['category'];
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

@media print {
    .sidebar,.filter-col,.ph,.rpt-tabs,.ptbar,.exp-btns,.no-print{display:none!important;}
    body,.wrap{margin:0;background:#fff;}
    .wrap{margin-left:0!important;}
    .rpt-panel{display:none!important;}
    .rpt-panel.active{display:block!important;}
    .preview-col{padding:0;}
    .box{border:none;padding:0;}
    .report-table thead th{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
}
</style>

<?php include '../includes/sidebar.php'; ?>

<div class="wrap">
    <div class="ph no-print">
        <h1><i class="fas fa-chart-bar me-2"></i>Property Reports</h1>
        <p>Filter, preview, and export property data by report type</p>
    </div>

    <!-- ══ TABS ══ -->
    <div class="rpt-tabs no-print">
        <div class="rpt-tab active" onclick="switchTab('inventory',this)">
            <i class="fas fa-boxes"></i> Property Inventory
        </div>
        <div class="rpt-tab" onclick="switchTab('stocklogs',this)">
            <i class="fas fa-exchange-alt"></i> Stock Movement Logs
        </div>
        <div class="rpt-tab" onclick="switchTab('aircon',this)">
            <i class="fas fa-snowflake"></i> Aircon Report
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         PANEL 1 — PROPERTY INVENTORY
    ══════════════════════════════════════════ -->
    <div class="rpt-panel active" id="panel-inventory">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Category</label>
                <select id="inv_cat" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($category_list as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg">
                <label class="gl">Stock Status</label>
                <div class="ci"><input type="checkbox" id="inv_st_n" value="normal" class="inv-stock" checked><label for="inv_st_n">Normal</label></div>
                <div class="ci"><input type="checkbox" id="inv_st_l" value="low"    class="inv-stock"><label for="inv_st_l">Low Stock <span class="note-warn">(⚠ below reorder)</span></label></div>
                <div class="ci"><input type="checkbox" id="inv_st_o" value="out"    class="inv-stock"><label for="inv_st_o">Out of Stock <span class="note-danger">(⚠ critical)</span></label></div>
            </div>
            <div class="fg">
                <label class="gl">Item Status</label>
                <div class="ci"><input type="checkbox" id="inv_is_a" value="Active"        class="inv-item" checked><label for="inv_is_a">Active</label></div>
                <div class="ci"><input type="checkbox" id="inv_is_i" value="Inactive"      class="inv-item"><label for="inv_is_i">Inactive</label></div>
                <div class="ci"><input type="checkbox" id="inv_is_d" value="Discontinued"  class="inv-item"><label for="inv_is_d">Discontinued</label></div>
            </div>
            <button class="btn-prev" onclick="preview('inventory')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf"  id="inv-pdf"  onclick="doPDF()"        disabled><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" id="inv-xlsx" onclick="doCSV('panel-inventory','property_inventory')" disabled><i class="fas fa-file-csv"></i> CSV</button>
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
                    <button class="bx pdf"  id="log-pdf"  onclick="doPDF()"                disabled><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" id="log-xlsx" onclick="doCSV('panel-stocklogs','stock_movement_logs')" disabled><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="log-box">
                <div class="ph-msg"><i class="fas fa-exchange-alt"></i><p>Set filters and click <strong>Preview</strong>.</p></div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         PANEL 3 — AIRCON
    ══════════════════════════════════════════ -->
    <div class="rpt-panel" id="panel-aircon">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Campus</label>
                <select id="ac_campus" class="form-select form-select-sm">
                    <option value="">All Campuses</option>
                    <option value="BED">BED</option>
                    <option value="TED">TED</option>
                </select>
            </div>
            <div class="fg">
                <label class="gl">Condition / Status</label>
                <div class="ci"><input type="checkbox" id="ac_op"  value="Operational"       class="ac-st" checked><label for="ac_op">Operational</label></div>
                <div class="ci"><input type="checkbox" id="ac_wk"  value="Working"           class="ac-st" checked><label for="ac_wk">Working</label></div>
                <div class="ci"><input type="checkbox" id="ac_nr"  value="Needs Repair"      class="ac-st"><label for="ac_nr">Needs Repair <span class="note-warn">(⚠)</span></label></div>
                <div class="ci"><input type="checkbox" id="ac_um"  value="Under Maintenance" class="ac-st"><label for="ac_um">Under Maintenance <span class="note-warn">(⚠)</span></label></div>
                <div class="ci"><input type="checkbox" id="ac_dc"  value="Decommissioned"    class="ac-st"><label for="ac_dc">Decommissioned <span class="note-danger">(⚠)</span></label></div>
                <div class="ci"><input type="checkbox" id="ac_na"  value="N/A"               class="ac-st"><label for="ac_na">N/A</label></div>
            </div>
            <div class="fg">
                <label class="gl">Date Added Range</label>
                <small class="text-muted">From</small>
                <input type="date" id="ac_ds" class="form-control form-control-sm mb-1">
                <small class="text-muted">To</small>
                <input type="date" id="ac_de" class="form-control form-control-sm">
            </div>
            <button class="btn-prev" onclick="preview('aircon')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf"  id="ac-pdf"  onclick="doPDF()"            disabled><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" id="ac-xlsx" onclick="doCSV('panel-aircon','aircon_report')" disabled><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="ac-box">
                <div class="ph-msg"><i class="fas fa-snowflake"></i><p>Set filters and click <strong>Preview</strong>.</p></div>
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

// ── Build params per report type ─────────────────────────────
function buildParams(type) {
    const p = new URLSearchParams({ report_type: type });
    if (type === 'inventory') {
        const cat = document.getElementById('inv_cat').value;
        if (cat) p.append('category', cat);
        document.querySelectorAll('.inv-stock:checked').forEach(e => p.append('stock_status[]', e.value));
        document.querySelectorAll('.inv-item:checked').forEach(e  => p.append('item_status[]',  e.value));
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
    if (type === 'aircon') {
        const cam = document.getElementById('ac_campus').value;
        if (cam) p.append('ac_campus', cam);
        document.querySelectorAll('.ac-st:checked').forEach(e => p.append('ac_status[]', e.value));
        const ds = document.getElementById('ac_ds').value;
        const de = document.getElementById('ac_de').value;
        if (ds) p.append('ac_ds', ds);
        if (de) p.append('ac_de', de);
    }
    return p;
}

// ── Preview ──────────────────────────────────────────────────
const boxMap  = { inventory:'inv-box',  stocklogs:'log-box', aircon:'ac-box'  };
const pdfMap  = { inventory:'inv-pdf',  stocklogs:'log-pdf', aircon:'ac-pdf'  };
const xlsMap  = { inventory:'inv-xlsx', stocklogs:'log-xlsx',aircon:'ac-xlsx' };
const titleMap= { inventory:'Property Inventory Report', stocklogs:'Stock Movement Log Report', aircon:'Aircon Inventory Report' };

function preview(type) {
    const box  = document.getElementById(boxMap[type]);
    const bPdf = document.getElementById(pdfMap[type]);
    const bXls = document.getElementById(xlsMap[type]);
    box.innerHTML = '<div class="spinner"><i class="fas fa-spinner fa-spin fa-2x d-block mb-2"></i>Loading…</div>';
    bPdf.disabled = bXls.disabled = true;

    const now = new Date().toLocaleString('en-PH', {dateStyle:'long', timeStyle:'short'});

    fetch('../actions/get_filtered_reports.php?' + buildParams(type).toString())
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
function doPDF() { window.print(); }

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