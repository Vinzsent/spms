<?php
$pageTitle = 'Service Form Reports';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Access Control: Only Admin and GSO
$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));

if (!in_array($user_type, ['admin', 'administrator', 'gsogeneralserviceofficer'])) {
    $_SESSION['error'] = 'Access denied. You do not have permission to view Service Reports.';
    header("Location: ../dashboard.php");
    exit;
}
?>

<style>
:root {
    --pg: #073b1d;
    --pg2: #0a4f28;
    --acc: #EACA26;
    --bg: #f4f6f9;
    --bd: #dee2e6;
}
body { font-family: 'Segoe UI', sans-serif; background: var(--bg); }


/* ─── Page Layout ─── */
.wrap { margin-left: 280px; min-height: 100vh; padding: 20px; }
.ph {
    background: linear-gradient(135deg, var(--pg), var(--pg2));
    color: #fff; padding: 20px 28px; margin-top: 0;
}
.ph h1 { font-size: 1.35rem; margin: 0; }
.ph p { margin: 3px 0 0; font-size: .85rem; opacity: .85; }

/* ─── Tabs ─── */
.rpt-tabs {
    display: flex; background: #fff; border-bottom: 2px solid var(--bd);
    padding: 0 20px;
}
.rpt-tab {
    padding: 13px 20px; cursor: pointer; font-weight: 600; font-size: .875rem;
    color: #666; border-bottom: 3px solid transparent; margin-bottom: -2px;
    transition: .2s; display: flex; align-items: center; gap: 7px;
}
.rpt-tab:hover { color: var(--pg); }
.rpt-tab.active { color: var(--pg); border-bottom-color: var(--pg); }

/* ─── Report Panel ─── */
.rpt-panel { display: none; }
.rpt-panel.active { display: flex; }

.filter-col {
    width: 280px; min-width: 280px; background: #fff;
    border-right: 1px solid var(--bd); padding: 20px;
    min-height: calc(100vh - 130px);
}
.filter-col h5 { font-size: .88rem; font-weight: 700; color: var(--pg); margin-bottom: 16px; }
.fg { margin-bottom: 16px; }
.fg .gl {
    display: block; font-size: .73rem; font-weight: 700;
    color: #666; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px;
}
.fg .form-control, .fg .form-select { font-size: .83rem; }
.ci { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 6px; font-size: .84rem; }
.ci input[type=checkbox] { accent-color: var(--pg); width: 14px; height: 14px; flex-shrink: 0; margin-top: 2px; }
.btn-prev {
    width: 100%; padding: 9px; border: none; border-radius: 6px;
    background: var(--pg); color: #fff; font-weight: 600;
    font-size: .875rem; cursor: pointer; transition: .2s; margin-top: 6px;
}
.btn-prev:hover { background: var(--pg2); }

/* ─── Preview ─── */
.preview-col { flex: 1; padding: 20px 24px; overflow-x: auto; }
.ptbar {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 14px; flex-wrap: wrap; gap: 8px;
}
.ptbar h5 { font-size: .9rem; font-weight: 700; color: var(--pg); margin: 0; }
.exp-btns { display: flex; gap: 7px; }
.bx {
    display: flex; align-items: center; gap: 5px; padding: 6px 13px;
    border-radius: 5px; font-size: .82rem; font-weight: 600; border: none; cursor: pointer;
    transition: .2s;
}
.bx.pdf { background: #c0392b; color: #fff; }
.bx.xlsx { background: #1a7431; color: #fff; }
.bx:hover:not(:disabled) { filter: brightness(1.12); }
.bx:disabled { opacity: .4; cursor: default; }

.box {
    background: #fff; border: 1px solid var(--bd); border-radius: 8px;
    padding: 26px; min-height: 260px;
}
.ph-msg { text-align: center; padding: 60px 0; color: #aaa; }
.ph-msg i { font-size: 2.5rem; display: block; margin-bottom: 10px; }
.spinner { text-align: center; padding: 60px; color: #888; }

.rmeta { border-bottom: 2px solid var(--pg); padding-bottom: 10px; margin-bottom: 18px; }
.rmeta h2 { color: var(--pg); font-size: 1.15rem; margin: 0; }
.rmeta p { font-size: .76rem; color: #666; margin: 3px 0 0; }

.report-table { width: 100%; font-size: .79rem; border-collapse: collapse; }
.report-table thead th {
    background: var(--pg); color: #fff; padding: 8px 10px; white-space: nowrap; text-align: left;
}
.report-table tbody td { padding: 8px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
.report-table tbody tr:nth-child(even) { background: #f8f9fa; }

@media print {
    .sidebar, .filter-col, .ph, .rpt-tabs, .ptbar, .exp-btns, .no-print, .navbar { display: none !important; }
    body, .wrap { margin: 0; background: #fff; }
    .wrap { margin-left: 0 !important; }
    .rpt-panel { display: none !important; }
    .rpt-panel.active { display: block !important; }
    .preview-col { padding: 0; }
    .box { border: none; padding: 0; }
}
</style>

<?php include '../includes/sidebar.php'; ?>

<div class="wrap">

    <div class="ph no-print">
        <h1><i class="fas fa-file-invoice me-2 text-warning"></i>Service Form Reports</h1>
        <p>Preview and export records of service completions, unresolved units, and problem reports.</p>
    </div>

    <!-- ══ TABS ══ -->
    <div class="rpt-tabs no-print">
        <div class="rpt-tab active" onclick="switchTab('completion', this)">
            <i class="fas fa-check-circle"></i> Completion Slips
        </div>
        <div class="rpt-tab" onclick="switchTab('unresolved', this)">
            <i class="fas fa-clock"></i> Unresolved Units
        </div>
        <div class="rpt-tab" onclick="switchTab('problem', this)">
            <i class="fas fa-exclamation-triangle"></i> Problem Reports
        </div>
    </div>

    <!-- Panel 1: Service Completion Slips -->
    <div class="rpt-panel active" id="panel-completion">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Search</label>
                <input type="text" id="comp_search" class="form-control" placeholder="Search Unit Code / ID...">
            </div>
            <div class="fg">
                <label class="gl">Date Serviced</label>
                <input type="date" id="comp_ds" class="form-control mb-1">
                <input type="date" id="comp_de" class="form-control">
            </div>
            <div class="fg">
                <label class="gl">Final Status</label>
                <select id="comp_status" class="form-select">
                    <option value="">-- All Status --</option>
                    <option value="Fully operational">Fully operational</option>
                    <option value="Operational but for monitoring">Operational but for monitoring</option>
                    <option value="Needs follow-up repair">Needs follow-up repair</option>
                    <option value="Recommended for replacement">Recommended for replacement</option>
                </select>
            </div>
            <div class="fg">
                <label class="gl">Work Done</label>
                <select id="comp_work" class="form-select">
                    <option value="">-- All --</option>
                    <option value="Cleaning">Cleaning</option>
                    <option value="Repair">Repair</option>
                    <option value="Freon recharge">Freon recharge</option>
                    <option value="Electrical correction">Electrical correction</option>
                    <option value="Parts replacement">Parts replacement</option>
                </select>
            </div>
            <button class="btn-prev" onclick="preview('completion')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf" onclick="window.print()" disabled id="comp-pdf"><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" onclick="doCSV('panel-completion', 'service_completions')" disabled id="comp-csv"><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="comp-box">
                <div class="ph-msg"><i class="fas fa-check-circle"></i><p>Set filters and click <strong>Preview</strong>.</p></div>
            </div>
        </div>
    </div>

    <!-- Panel 2: Unresolved Units -->
    <div class="rpt-panel" id="panel-unresolved">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Search</label>
                <input type="text" id="unres_search" class="form-control" placeholder="Search Unit Code / ID...">
            </div>
            <div class="fg">
                <label class="gl">Urgency Level</label>
                <select id="unres_priority" class="form-select">
                    <option value="">-- All --</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div class="fg">
                <label class="gl">Admin Action</label>
                <select id="unres_admin" class="form-select">
                    <option value="">-- All Status --</option>
                    <option value="Approved">Approved</option>
                    <option value="For review">For review</option>
                    <option value="Need more quotation">Need more quotation</option>
                    <option value="Deferred">Deferred</option>
                    <option value="For replacement planning">For replacement planning</option>
                </select>
            </div>
            <button class="btn-prev" onclick="preview('unresolved')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf" onclick="window.print()" disabled id="unres-pdf"><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" onclick="doCSV('panel-unresolved', 'unresolved_units')" disabled id="unres-csv"><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="unres-box">
                <div class="ph-msg"><i class="fas fa-clock"></i><p>Set filters and click <strong>Preview</strong>.</p></div>
            </div>
        </div>
    </div>

    <!-- Panel 3: Problem Reports -->
    <div class="rpt-panel" id="panel-problem">
        <div class="filter-col no-print">
            <h5><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="fg">
                <label class="gl">Search</label>
                <input type="text" id="prob_search" class="form-control" placeholder="Search Unit / Location...">
            </div>
            <div class="fg">
                <label class="gl">Problem Observed</label>
                <select id="prob_obs" class="form-select">
                    <option value="">-- All --</option>
                    <option value="Not cooling">Not cooling</option>
                    <option value="Weak airflow">Weak airflow</option>
                    <option value="Water leaking">Water leaking</option>
                    <option value="Electrical smell/safety concern">Electrical smell/safety concern</option>
                    <option value="Loud noise/vibration">Loud noise/vibration</option>
                    <option value="Won't turn on">Won't turn on</option>
                    <option value="Remote/control not working">Remote/control not working</option>
                </select>
            </div>
            <div class="fg">
                <label class="gl">Initial Action</label>
                <select id="prob_act" class="form-select">
                    <option value="">-- All --</option>
                    <option value="Cleaned">Cleaned</option>
                    <option value="Minor adjustment">Minor adjustment</option>
                    <option value="Referred to technician">Referred to technician</option>
                    <option value="Parts to be ordered">Parts to be ordered</option>
                    <option value="Further evaluation needed">Further evaluation needed</option>
                </select>
            </div>
            <button class="btn-prev" onclick="preview('problem')"><i class="fas fa-eye me-2"></i>Preview</button>
        </div>
        <div class="preview-col">
            <div class="ptbar no-print">
                <h5><i class="fas fa-file-alt me-2"></i>Preview</h5>
                <div class="exp-btns">
                    <button class="bx pdf" onclick="window.print()" disabled id="prob-pdf"><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="bx xlsx" onclick="doCSV('panel-problem', 'problem_reports')" disabled id="prob-csv"><i class="fas fa-file-csv"></i> CSV</button>
                </div>
            </div>
            <div class="box" id="prob-box">
                <div class="ph-msg"><i class="fas fa-exclamation-triangle"></i><p>Set filters and click <strong>Preview</strong>.</p></div>
            </div>
        </div>
    </div>

</div>

<script>
function switchTab(type, el) {
    document.querySelectorAll('.rpt-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.rpt-panel').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('panel-' + type).classList.add('active');
}

function preview(type) {
    const box = document.getElementById(type + '-box');
    const bPdf = document.getElementById(type + '-pdf');
    const bCsv = document.getElementById(type + '-csv');
    
    box.innerHTML = '<div class="spinner"><i class="fas fa-spinner fa-spin fa-2x d-block mb-2"></i>Loading Preview...</div>';
    bPdf.disabled = true;
    bCsv.disabled = true;

    let url = '';
    
    if(type === 'completion') {
        const search = document.getElementById('comp_search').value;
        const ds = document.getElementById('comp_ds').value;
        const de = document.getElementById('comp_de').value;
        const status = document.getElementById('comp_status').value;
        const work = document.getElementById('comp_work').value;
        url = `../actions/fetch_service_completions.php?search=${encodeURIComponent(search)}&ds=${encodeURIComponent(ds)}&de=${encodeURIComponent(de)}&status=${encodeURIComponent(status)}&work=${encodeURIComponent(work)}`;
    } else if(type === 'unresolved') {
        const search = document.getElementById('unres_search').value;
        const priority = document.getElementById('unres_priority').value;
        const admin = document.getElementById('unres_admin').value;
        url = `../actions/fetch_unresolved_units.php?search=${encodeURIComponent(search)}&priority=${encodeURIComponent(priority)}&admin=${encodeURIComponent(admin)}`;
    } else if(type === 'problem') {
        const search = document.getElementById('prob_search').value;
        const obs = document.getElementById('prob_obs').value;
        const act = document.getElementById('prob_act').value;
        url = `../actions/fetch_problem_reports.php?search=${encodeURIComponent(search)}&obs=${encodeURIComponent(obs)}&act=${encodeURIComponent(act)}`;
    }

    fetch(url)
        .then(response => response.text())
        .then(htmlRows => {
            const now = new Date().toLocaleString();
            let tableHeader = "";
            
            if(type === 'completion') {
                tableHeader = `
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Location (Bldg/Room)</th>
                                <th>Unit Code</th>
                                <th>Action Taken</th>
                                <th>Personnel</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${htmlRows}
                        </tbody>
                    </table>`;
            } else if(type === 'unresolved') {
                tableHeader = `
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Ref No.</th>
                                <th>Original Date</th>
                                <th>Problem</th>
                                <th>Action Taken</th>
                                <th>Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${htmlRows}
                        </tbody>
                    </table>`;
            } else {
                tableHeader = `
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Location (Bldg/Room)</th>
                                <th>Problem Observed</th>
                                <th>Unit Code</th>
                                <th>Reported By</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${htmlRows}
                        </tbody>
                    </table>`;
            }

            box.innerHTML = `
                <div class="rmeta">
                    <h2>Service Report: ${type.toUpperCase()}</h2>
                    <p>Generated on: ${now}</p>
                </div>
                ${tableHeader}`;
                
            bPdf.disabled = false;
            bCsv.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            box.innerHTML = '<div class="ph-msg text-danger"><i class="fas fa-exclamation-triangle"></i><p>Error loading data. Please try again.</p></div>';
        });
}

function doCSV(panelId, filename) {
    const table = document.querySelector('#' + panelId + ' .report-table');
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) 
            row.push('"' + cols[j].innerText.trim() + '"');
        csv.push(row.join(','));
    }

    const blob = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '_' + new Date().toISOString().slice(0, 10) + '.csv';
    link.click();
}
</script>

<?php include '../includes/footer.php'; ?>
