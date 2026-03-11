<!-- View Supplier Modal Structure -->
<div class="view-supplier-container p-2">
    <!-- Company Section -->
    <div class="view-section mb-4">
        <h6 class="view-section-title"><i class="fas fa-building me-2"></i> Company Information</h6>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label text-muted small uppercase fw-bold">Supplier Name</label>
                <div class="view-data" id="view-supplier-name"></div>
            </div>
            <div class="col-md-12">
                <label class="form-label text-muted small uppercase fw-bold">Full Address</label>
                <div class="view-data" id="view-address"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Province / City</label>
                <div class="view-data"><span id="view-province"></span>, <span id="view-city"></span></div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small uppercase fw-bold">ZIP Code</label>
                <div class="view-data" id="view-zip-code"></div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small uppercase fw-bold">Country</label>
                <div class="view-data" id="view-country"></div>
            </div>
        </div>
    </div>

    <hr class="my-4 opacity-50">

    <!-- Contact Section -->
    <div class="view-section mb-4">
        <h6 class="view-section-title"><i class="fas fa-address-book me-2"></i> Contact Details</h6>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label text-muted small uppercase fw-bold">Contact Person</label>
                <div class="view-data" id="view-contact-person"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Mobile No.</label>
                <div class="view-data" id="view-contact-number"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Telephone No.</label>
                <div class="view-data" id="view-landline-number"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Email Address</label>
                <div class="view-data" id="view-email-address"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Website / Fax</label>
                <div class="view-data"><span id="view-website"></span> <span id="view-fax-number" class="ms-2"></span></div>
            </div>
        </div>
    </div>

    <hr class="my-4 opacity-50">

    <!-- Business Section -->
    <div class="view-section mb-4">
        <h6 class="view-section-title"><i class="fas fa-briefcase me-2"></i> Business & Status</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Business Type</label>
                <div class="view-data" id="view-business-type"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Category</label>
                <div class="view-data" id="view-category"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">TIN Number</label>
                <div class="view-data fw-bold text-dark" id="view-tin"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Payment Terms</label>
                <div class="view-data" id="view-payment-terms"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Date Registered</label>
                <div class="view-data" id="view-date-registered"></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small uppercase fw-bold">Status</label>
                <div id="view-status-badge"></div>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small uppercase fw-bold">Notes</label>
                <div class="view-data bg-light p-3 rounded" id="view-notes" style="min-height: 60px;"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .view-section-title {
        color: #1a5f3c;
        font-weight: 700;
        margin-bottom: 20px;
        font-size: 1rem;
        display: flex;
        align-items: center;
    }

    .view-data {
        font-size: 0.95rem;
        color: #1e293b;
        min-height: 24px;
        padding: 5px 0;
    }

    .form-label.small {
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        margin-bottom: 2px;
    }

    #view-status-badge .badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
    }
</style>