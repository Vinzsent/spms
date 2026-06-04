<?php
$pageTitle = 'Service Forms';
include '../includes/auth.php';
include '../includes/db.php';

// Access Control: Only Admin and GSO
$raw_user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_type = str_replace([' ', '-'], '', strtolower($raw_user_type));

if (!in_array($user_type, ['admin', 'administrator', 'gsogeneralserviceofficer'])) {
    $_SESSION['error'] = 'Access denied. You do not have permission to view this page.';
    header("Location: ../dashboard.php");
    exit;
}

include '../includes/header.php';
?>

<style>
    :root {
        --primary-green: #073b1d;
        --accent-gold: #EACA26;
        --bg-glass: rgba(255, 255, 255, 0.9);
        --transition-speed: 0.4s;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', sans-serif;
    }


    .main-container {
        margin-left: 280px;
        padding: 2rem;
        margin-top: 0;
        min-height: 100vh;
    }

    /* Animated Tabs Wrapper */
    .tabs-wrapper {
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .tabs-header {
        display: flex;
        background: #f1f3f5;
        position: relative;
        padding: 5px;
    }

    .tab-btn {
        flex: 1;
        padding: 15px 20px;
        text-align: center;
        cursor: pointer;
        font-weight: 600;
        color: #6c757d;
        z-index: 1;
        transition: color var(--transition-speed);
        border: none;
        background: transparent;
        font-size: 0.95rem;
    }

    .tab-btn.active {
        color: white;
    }

    /* The sliding background for active tab */
    .tab-indicator {
        position: absolute;
        height: calc(100% - 10px);
        width: calc(33.33% - 10px);
        background: var(--primary-green);
        border-radius: 15px;
        top: 5px;
        left: 5px;
        transition: transform var(--transition-speed) cubic-bezier(0.645, 0.045, 0.355, 1);
        z-index: 0;
        box-shadow: 0 4px 15px rgba(7, 59, 29, 0.3);
    }

    /* Tab Content Animation */
    .tab-content-item {
        display: none;
        padding: 30px;
        animation: fadeIn 0.5s ease-out;
    }

    .tab-content-item.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Form Styles */
    .form-section {
        background: #fff;
        padding: 25px;
        border-radius: 15px;
        border: 1px solid #e9ecef;
    }

    .form-title {
        color: var(--primary-green);
        border-bottom: 2px solid var(--accent-gold);
        display: inline-block;
        margin-bottom: 1.5rem;
        padding-bottom: 5px;
        font-weight: 700;
    }

    .btn-submit {
        background: var(--primary-green);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-submit:hover {
        background: #0a4f28;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(7, 59, 29, 0.3);
        color: white;
    }

    /* Custom Input Styles */
    .form-control:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.25rem rgba(7, 59, 29, 0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .tab-btn {
            font-size: 0.8rem;
            padding: 12px 10px;
        }

        .main-container {
            padding: 1rem;
        }
    }

    [data-bs-theme="dark"] .tabs-wrapper {
        background: #2c3034;
    }

    [data-bs-theme="dark"] .tabs-header {
        background: #212529;
    }

    [data-bs-theme="dark"] .form-section {
        background: #212529;
        border-color: #373b3e;
    }

    [data-bs-theme="dark"] .tab-btn {
        color: #adb5bd;
    }
</style>


<?php include '../includes/sidebar.php'; ?>

<div class="main-container">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="fas fa-file-signature me-2 text-success"></i>Service Management</h2>
                <p class="text-muted mb-0">Fill out and submit service-related forms.</p>
            </div>
            <a href="../dashboard.php" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <div class="tabs-wrapper">
            <div class="tabs-header">
                <div class="tab-indicator" id="indicator"></div>
                <button class="tab-btn active" onclick="switchTab(0, this)">
                    <i class="fas fa-check-circle me-1 d-none d-md-inline"></i> Service Completion
                </button>
                <button class="tab-btn" onclick="switchTab(1, this)">
                    <i class="fas fa-clock me-1 d-none d-md-inline"></i> Unresolved Unit
                </button>
                <button class="tab-btn" onclick="switchTab(2, this)">
                    <i class="fas fa-exclamation-triangle me-1 d-none d-md-inline"></i> Problem Report
                </button>
            </div>

            <!-- Tab 1: Service Completion Slip -->
            <div class="tab-content-item active" id="tab-0">
                <div class="form-section">
                    <h4 class="form-title">Service Completion Slip</h4>
                    <form id="serviceCompletionForm" class="row g-4">
                        <input type="hidden" name="action" value="submit_service_completion">

                        <!-- Unit Information & Service Details -->
                        <div class="col-12">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-info-circle me-2"></i>Unit Information & Service Details
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Unit Code / ID</label>
                                    <input type="text" class="form-control bg-light" name="unit_code" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Building</label>
                                    <input type="text" class="form-control bg-light" name="building" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Room / Office</label>
                                    <input type="text" class="form-control bg-light" name="room_office" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Date Serviced</label>
                                    <input type="date" class="form-control bg-light" name="date_serviced" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Serviced by (Technician/Co)</label>
                                    <input type="text" class="form-control bg-light" name="serviced_by" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Contact No. (optional)</label>
                                    <input type="text" class="form-control bg-light" name="contact_no">
                                </div>
                            </div>
                        </div>

                        <!-- Work Done -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-tools me-2"></i>Work Done
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="work_done[]" value="Cleaning" id="work1"><label class="form-check-label" for="work1">Cleaning</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="work_done[]" value="Repair" id="work2"><label class="form-check-label" for="work2">Repair</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="work_done[]" value="Freon recharge" id="work3"><label class="form-check-label" for="work3">Freon recharge</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="work_done[]" value="Electrical correction" id="work4"><label class="form-check-label" for="work4">Electrical correction</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="work_done[]" value="Parts replacement" id="work5"><label class="form-check-label" for="work5">Parts replacement</label></div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="form-check mb-0"><input class="form-check-input mt-0" type="checkbox" name="work_done[]" value="Other" id="work6"><label class="form-check-label text-nowrap" for="work6">Other:</label></div>
                                        <input type="text" class="form-control form-control-sm bg-light" name="other_work_details" placeholder="Specify...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details & Parts -->
                        <div class="col-12 mt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Details of Work</label>
                                    <textarea class="form-control bg-light" name="details_of_work" rows="3" required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Parts Replaced</label>
                                    <textarea class="form-control bg-light" name="parts_replaced" rows="3" placeholder="(Leave blank if none)"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Final Status -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-clipboard-check me-2"></i>Final Status
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="radio" name="final_status" value="Fully operational" id="stat1" required><label class="form-check-label" for="stat1">Fully operational</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="radio" name="final_status" value="Operational but for monitoring" id="stat2"><label class="form-check-label" for="stat2">Operational but for monitoring</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="radio" name="final_status" value="Needs follow-up repair" id="stat3"><label class="form-check-label" for="stat3">Needs follow-up repair</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="radio" name="final_status" value="Recommended for replacement" id="stat4"><label class="form-check-label" for="stat4">Recommended for replacement</label></div>
                                </div>
                            </div>
                            <div class="mt-3 col-md-4">
                                <label class="form-label small fw-bold text-muted">Next Recommended Service Date</label>
                                <input type="date" class="form-control bg-light" name="next_service_date">
                            </div>
                        </div>

                        <!-- Verification -->
                        <div class="col-12 mt-4">
                            <div class="row g-3 bg-light p-3 rounded border">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Verified by (GSO/Admin A)</label>
                                    <input type="text" class="form-control" name="verified_by" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Date Verified</label>
                                    <input type="date" class="form-control" name="date_verified" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 text-end pt-3 border-top">
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-paper-plane me-2"></i>Submit Completion Slip
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tab 2: Request For Unresolved Unit -->
            <div class="tab-content-item" id="tab-1">
                <div class="form-section">
                    <h4 class="form-title">Request For Unresolved Unit</h4>
                    <form id="unresolvedUnitForm" class="row g-4">
                        <input type="hidden" name="action" value="submit_unresolved_unit">

                        <!-- Unit Information & Original Report Details -->
                        <div class="col-12">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-info-circle me-2"></i>Unit & Original Report Details
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Unit Code / ID</label>
                                    <input type="text" class="form-control bg-light" name="unit_code" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Building</label>
                                    <input type="text" class="form-control bg-light" name="building" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Room / Office</label>
                                    <input type="text" class="form-control bg-light" name="room_office" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Date Problem First Reported</label>
                                    <input type="date" class="form-control bg-light" name="date_first_reported" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Problem Report Slip No.</label>
                                    <input type="text" class="form-control bg-light" name="report_slip_no" placeholder="(if used)">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Reported Issue</label>
                                    <input type="text" class="form-control bg-light" name="reported_issue" required>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Already Taken -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-tools me-2"></i>Actions Already Taken
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="actions_taken[]" value="Initial inspection completed" id="act1"><label class="form-check-label" for="act1">Initial inspection completed</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="actions_taken[]" value="Cleaning attempted" id="act2"><label class="form-check-label" for="act2">Cleaning attempted</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="actions_taken[]" value="Minor repair attempted" id="act3"><label class="form-check-label" for="act3">Minor repair attempted</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="actions_taken[]" value="Referred to technician" id="act4"><label class="form-check-label" for="act4">Referred to technician</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="actions_taken[]" value="Waiting for quotation" id="act5"><label class="form-check-label" for="act5">Waiting for quotation</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="actions_taken[]" value="Waiting for parts" id="act6"><label class="form-check-label" for="act6">Waiting for parts</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="actions_taken[]" value="Budget approval pending" id="act7"><label class="form-check-label" for="act7">Budget approval pending</label></div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="form-check mb-0"><input class="form-check-input mt-0" type="checkbox" name="actions_taken[]" value="Other" id="act8"><label class="form-check-label text-nowrap" for="act8">Other:</label></div>
                                        <input type="text" class="form-control form-control-sm bg-light" name="other_action_details" placeholder="Specify...">
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label small fw-bold text-muted">Details / Notes</label>
                                    <textarea class="form-control bg-light" name="actions_details_notes" rows="2" placeholder="Additional details on actions taken..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Requested Action From Admin -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-hand-holding-usd me-2"></i>Requested Action From Admin
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="requested_action[]" value="Approve technician servicing" id="req1"><label class="form-check-label" for="req1">Approve technician servicing</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="requested_action[]" value="Approve purchase of parts" id="req2"><label class="form-check-label" for="req2">Approve purchase of parts</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="requested_action[]" value="Approve replacement of unit" id="req3"><label class="form-check-label" for="req3">Approve replacement of unit</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="requested_action[]" value="Prioritize scheduling due to classroom disruption" id="req4"><label class="form-check-label" for="req4">Prioritize scheduling due to classroom disruption</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="requested_action[]" value="Request inspection by external provider" id="req5"><label class="form-check-label" for="req5">Request inspection by external provider</label></div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="form-check mb-0"><input class="form-check-input mt-0" type="checkbox" name="requested_action[]" value="Other" id="req6"><label class="form-check-label text-nowrap" for="req6">Other:</label></div>
                                        <input type="text" class="form-control form-control-sm bg-light" name="other_requested_action_details" placeholder="Specify...">
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label small fw-bold text-muted">Justification</label>
                                    <textarea class="form-control bg-light" name="request_justification" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Urgency Level -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-exclamation-triangle me-2"></i>Urgency Level
                            </h6>
                            <div class="row text-center g-3">
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="urgency_level" value="LOW" id="urg_low" autocomplete="off" required>
                                    <label class="btn btn-outline-success w-100 d-flex flex-column align-items-center p-3 h-100 rounded-3 shadow-sm" for="urg_low">
                                        <span class="fw-bold mb-1">LOW</span>
                                        <span class="small opacity-75" style="font-size: 0.75rem;">preventive action needed only</span>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="urgency_level" value="MEDIUM" id="urg_med" autocomplete="off">
                                    <label class="btn btn-outline-warning w-100 d-flex flex-column align-items-center p-3 h-100 rounded-3 shadow-sm" for="urg_med">
                                        <span class="fw-bold mb-1">MEDIUM</span>
                                        <span class="small opacity-75" style="font-size: 0.75rem;">cooling poor but usable</span>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="urgency_level" value="HIGH" id="urg_high" autocomplete="off">
                                    <label class="btn btn-outline-danger w-100 d-flex flex-column align-items-center p-3 h-100 rounded-3 shadow-sm" for="urg_high">
                                        <span class="fw-bold mb-1">HIGH</span>
                                        <span class="small opacity-75" style="font-size: 0.75rem;">classroom/office cannot function properly</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Endorsement & Admin Action -->
                        <div class="col-12 mt-4">
                            <div class="row g-4">
                                <div class="col-md-6 border-end">
                                    <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green);"><i class="fas fa-file-signature me-2"></i>Endorsement</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Requested by (GSO / Maintenance)</label>
                                        <input type="text" class="form-control bg-light" name="requested_by" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Endorsed to (Admin/Principal/Finance)</label>
                                        <input type="text" class="form-control bg-light" name="endorsed_to" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Date</label>
                                        <input type="date" class="form-control bg-light" name="date_endorsed" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green);"><i class="fas fa-clipboard-check me-2"></i>Admin Action <small class="text-muted text-lowercase font-monospace">(Office Use)</small></h6>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="admin_action[]" value="Approved" id="adm1"><label class="form-check-label text-muted" for="adm1">Approved</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="admin_action[]" value="For review" id="adm2"><label class="form-check-label text-muted" for="adm2">For review</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="admin_action[]" value="Need more quotation" id="adm3"><label class="form-check-label text-muted" for="adm3">Need more quotation</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="admin_action[]" value="Deferred" id="adm4"><label class="form-check-label text-muted" for="adm4">Deferred</label></div>
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="admin_action[]" value="For replacement planning" id="adm5"><label class="form-check-label text-muted" for="adm5">For replacement planning</label></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label small fw-bold text-muted">Remarks</label>
                            <textarea class="form-control bg-light" name="remarks" rows="2"></textarea>
                        </div>

                        <div class="col-12 mt-4 text-end pt-3 border-top">
                            <button type="submit" class="btn btn-submit" style="background-color: #6f42c1; color: white;">
                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tab 3: Problem Report Slip -->
            <div class="tab-content-item" id="tab-2">
                <div class="form-section">
                    <h4 class="form-title">Problem Report Slip</h4>
                    <form id="problemReportForm" class="row g-4">
                        <input type="hidden" name="action" value="submit_problem_report">

                        <!-- Unit Information & Report Details -->
                        <div class="col-12">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-info-circle me-2"></i>Unit Information & Report Details
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Unit Code / ID</label>
                                    <input type="text" class="form-control bg-light" name="unit_code" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Building</label>
                                    <input type="text" class="form-control bg-light" name="building" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Room / Office</label>
                                    <input type="text" class="form-control bg-light" name="room_office" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Date Reported</label>
                                    <input type="date" class="form-control bg-light" name="date_reported" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Reported by (Name/Office)</label>
                                    <input type="text" class="form-control bg-light" name="reported_by" value="<?= htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Contact No. (optional)</label>
                                    <input type="text" class="form-control bg-light" name="contact_no">
                                </div>
                            </div>
                        </div>

                        <!-- Problem Observed -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-eye me-2"></i>Problem Observed
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="problem_observed[]" value="Not cooling" id="obs1"><label class="form-check-label" for="obs1">Not cooling</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="problem_observed[]" value="Weak airflow" id="obs2"><label class="form-check-label" for="obs2">Weak airflow</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="problem_observed[]" value="Water leaking" id="obs3"><label class="form-check-label" for="obs3">Water leaking</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="problem_observed[]" value="Electrical smell/safety concern" id="obs4"><label class="form-check-label" for="obs4">Electrical smell/safety concern</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="problem_observed[]" value="Loud noise/vibration" id="obs5"><label class="form-check-label" for="obs5">Loud noise/vibration</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="problem_observed[]" value="Won't turn on" id="obs6"><label class="form-check-label" for="obs6">Won't turn on</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="problem_observed[]" value="Remote/control not working" id="obs7"><label class="form-check-label" for="obs7">Remote/control not working</label></div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="form-check mb-0"><input class="form-check-input mt-0" type="checkbox" name="problem_observed[]" value="Other" id="obs8"><label class="form-check-label text-nowrap" for="obs8">Other:</label></div>
                                        <input type="text" class="form-control form-control-sm bg-light" name="other_problem_details" placeholder="Specify...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Initial Check (GSO Use) -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-search me-2"></i>Initial Check <small class="text-muted text-lowercase font-monospace">(GSO Use)</small>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Date Checked</label>
                                        <input type="date" class="form-control bg-light" name="date_checked">
                                    </div>
                                    <div>
                                        <label class="form-label small fw-bold text-muted">Checked by</label>
                                        <input type="text" class="form-control bg-light" name="checked_by">
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex flex-column">
                                    <label class="form-label small fw-bold text-muted">Findings</label>
                                    <textarea class="form-control bg-light flex-grow-1" name="findings" style="min-height: 100px;"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Initial Action -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-uppercase mb-3" style="color: var(--primary-green); border-bottom: 1px solid #e9ecef; padding-bottom: 8px;">
                                <i class="fas fa-wrench me-2"></i>Initial Action <small class="text-muted text-lowercase font-monospace">(GSO Use)</small>
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="initial_action[]" value="Cleaned" id="iact1"><label class="form-check-label text-muted" for="iact1">Cleaned</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="initial_action[]" value="Minor adjustment" id="iact2"><label class="form-check-label text-muted" for="iact2">Minor adjustment</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="initial_action[]" value="Referred to technician" id="iact3"><label class="form-check-label text-muted" for="iact3">Referred to technician</label></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="initial_action[]" value="Parts to be ordered" id="iact4"><label class="form-check-label text-muted" for="iact4">Parts to be ordered</label></div>
                                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="initial_action[]" value="Further evaluation needed" id="iact5"><label class="form-check-label text-muted" for="iact5">Further evaluation needed</label></div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="col-12 mt-3">
                            <label class="form-label small fw-bold text-muted">Remarks</label>
                            <textarea class="form-control bg-light" name="remarks" rows="2" placeholder="Additional notes..."></textarea>
                        </div>

                        <!-- Receiving -->
                        <div class="col-12 mt-4">
                            <div class="row g-3 bg-light p-3 rounded border">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Received by GSO</label>
                                    <input type="text" class="form-control" name="received_by_gso">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Date Received</label>
                                    <input type="date" class="form-control" name="date_received">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4 text-end pt-3 border-top">
                            <button type="submit" class="btn btn-submit" style="background-color: #dc3545; color: white;">
                                <i class="fas fa-bug me-2"></i>Submit Problem Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function switchTab(index, btn) {
        // Update indicator
        const indicator = document.getElementById('indicator');
        indicator.style.transform = `translateX(${index * 100}%)`;

        // Update buttons
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Update content
        document.querySelectorAll('.tab-content-item').forEach(c => c.classList.remove('active'));
        document.getElementById(`tab-${index}`).classList.add('active');
    }

    // Form submission handlers
    document.querySelectorAll('form').forEach(form => {
        form.onsubmit = async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;

            const formData = new FormData(this);
            const action = formData.get('action');

            try {
                const response = await fetch(`../actions/${action}.php`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();

                if (data.success) {
                    // Small toast card on the right side
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Successfully recorded.'
                    });
                    this.reset();
                    
                    // Button Countdown
                    let secondsLeft = 10;
                    submitBtn.innerHTML = `<i class="fas fa-clock me-2"></i>Wait ${secondsLeft}s...`;
                    
                    const timerInterval = setInterval(() => {
                        secondsLeft--;
                        if (secondsLeft <= 0) {
                            clearInterval(timerInterval);
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        } else {
                            submitBtn.innerHTML = `<i class="fas fa-clock me-2"></i>Wait ${secondsLeft}s...`;
                        }
                    }, 1000);
                    
                } else {
                    // Check if it's the rate limit error
                    if (data.message && data.message.includes("wait")) {
                        let waitMatch = data.message.match(/wait (\d+) seconds/);
                        let secondsLeft = waitMatch ? parseInt(waitMatch[1]) : 10;
                        
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'warning',
                            title: data.message
                        });
                        
                        submitBtn.innerHTML = `<i class="fas fa-clock me-2"></i>Wait ${secondsLeft}s...`;
                        const timerInterval = setInterval(() => {
                            secondsLeft--;
                            if (secondsLeft <= 0) {
                                clearInterval(timerInterval);
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                            } else {
                                submitBtn.innerHTML = `<i class="fas fa-clock me-2"></i>Wait ${secondsLeft}s...`;
                            }
                        }, 1000);
                    } else {
                        // Standard error
                        Swal.fire({
                            icon: 'error',
                            title: 'Submission Failed',
                            text: data.message || 'An error occurred while submitting the form.',
                            confirmButtonColor: '#dc3545'
                        });
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Unable to connect to the server. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        };
    });
</script>

<?php include '../includes/footer.php'; ?>