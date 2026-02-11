<!-- View Supplier Modal Structure -->
<ul class="nav nav-tabs" id="viewSupplierTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="view-company-tab" data-bs-toggle="tab" data-bs-target="#view-company" type="button" role="tab">Company Info</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="view-contact-tab" data-bs-toggle="tab" data-bs-target="#view-contact" type="button" role="tab">Contact</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="view-business-tab" data-bs-toggle="tab" data-bs-target="#view-business" type="button" role="tab">Business</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="view-extra-tab" data-bs-toggle="tab" data-bs-target="#view-extra" type="button" role="tab">Additional Info</button>
    </li>
</ul>

<div class="tab-content pt-3" id="viewSupplierTabContent">
    <!-- Company Info -->
    <div class="tab-pane fade show active" id="view-company" role="tabpanel">
        <div class="row g-2">
            <div class="col-md-12">
                <label class="form-label">Supplier Name</label>
                <input type="text" class="form-control" id="view-supplier-name" readonly>
            </div>
            <div class="col-md-12">
                <label class="form-label">Full Address</label>
                <input type="text" class="form-control" id="view-address" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Province</label>
                <input type="text" class="form-control" id="view-province" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">City / Municipality</label>
                <input type="text" class="form-control" id="view-city" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">ZIP Code</label>
                <input type="text" class="form-control" id="view-zip-code" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Country</label>
                <input type="text" class="form-control" id="view-country" readonly>
            </div>
        </div>
    </div>

    <!-- Contact Tab -->
    <div class="tab-pane fade" id="view-contact" role="tabpanel">
        <div class="row g-2">
            <div class="col-md-12">
                <label class="form-label">Contact Person</label>
                <input type="text" class="form-control" id="view-contact-person" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Mobile No.</label>
                <input type="text" class="form-control" id="view-contact-number" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Telephone No.</label>
                <input type="text" class="form-control" id="view-landline-number" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" id="view-email-address" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fax Number</label>
                <input type="text" class="form-control" id="view-fax-number" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Website</label>
                <input type="text" class="form-control" id="view-website" readonly>
            </div>
        </div>
    </div>

    <!-- Business Tab -->
    <div class="tab-pane fade" id="view-business" role="tabpanel">
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Business Type</label>
                <input type="text" class="form-control" id="view-business-type" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <input type="text" class="form-control" id="view-category" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment Terms</label>
                <input type="text" class="form-control" id="view-payment-terms" readonly>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="tab-pane fade" id="view-extra" role="tabpanel">
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Date Registered</label>
                <input type="text" class="form-control" id="view-date-registered" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <input type="text" class="form-control" id="view-status" readonly>
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea class="form-control" id="view-notes" rows="3" readonly></textarea>
            </div>
        </div>
    </div>
</div>