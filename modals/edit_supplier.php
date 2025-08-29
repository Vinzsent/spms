<!-- Hidden field for supplier ID -->
<input type="hidden" name="supplier_id" id="edit-supplier-id">

<ul class="nav nav-tabs" id="editSupplierTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="edit-company-tab" data-bs-toggle="tab" data-bs-target="#edit-company" type="button" role="tab">Company Info</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="edit-contact-tab" data-bs-toggle="tab" data-bs-target="#edit-contact" type="button" role="tab">Contact</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="edit-business-tab" data-bs-toggle="tab" data-bs-target="#edit-business" type="button" role="tab">Business</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="edit-extra-tab" data-bs-toggle="tab" data-bs-target="#edit-extra" type="button" role="tab">Additional Info</button>
  </li>
</ul>

<div class="tab-content pt-3" id="editSupplierTabContent">

  <!-- Company Info Tab -->
  <div class="tab-pane fade show active" id="edit-company" role="tabpanel" aria-labelledby="edit-company-tab">
    <div class="row g-2">
      <div class="col-md-8">
        <label for="edit-supplier-name" class="form-label">Supplier Name</label>
        <input type="text" class="form-control" name="supplier_name" id="edit-supplier-name" required>
      </div>
      <div class="col-md-4">
        <label for="edit-tin" class="form-label">TIN</label>
        <input type="text" class="form-control" name="tax_identification_number" id="edit-tin">
      </div>
    </div>

    <div class="row g-2">
      <div class="col-md-8">
        <label for="edit-address" class="form-label">Full Address</label>
        <input type="text" class="form-control" name="address" id="edit-address">
      </div>
      <div class="col-md-4">
        <label for="edit-province" class="form-label">Province</label>
        <select name="province" id="edit-province" class="form-select" required>
          <option value="">-- Select Province --</option>
        </select>
      </div>
      <div class="col-md-4">
        <label for="edit-city" class="form-label">City / Municipality</label>
        <select name="city" id="edit-city" class="form-select" required>
          <option value="">-- Select City --</option>
        </select>
      </div>
      <div class="col-md-4">
        <label for="edit-zip-code" class="form-label">ZIP Code</label>
        <input type="text" class="form-control" name="zip_code" id="edit-zip-code">
      </div>
      <div class="col-md-4">
        <label for="edit-country" class="form-label">Country</label>
        <select name="country" id="edit-country" class="form-select">
          <option value="">-- Select Country --</option>
          <option value="Philippines" selected>Philippines</option>
        </select>
        
      </div>
    </div>
  </div>

  <!-- Contact Tab -->
  <div class="tab-pane fade" id="edit-contact" role="tabpanel" aria-labelledby="edit-contact-tab">
    <div class="row g-2">
      <div class="col-md-8">
        <label for="edit-contact-person" class="form-label">Contact Person</label>
        <input type="text" class="form-control" name="contact_person" id="edit-contact-person" required>
      </div>
      <div class="col-md-4">
        <label for="edit-contact-number" class="form-label">Contact Number</label>
        <input type="text" class="form-control" name="contact_number" id="edit-contact-number">
      </div>
      <div class="col-md-4">
        <label for="edit-email-address" class="form-label">Email Address</label>
        <input type="email" class="form-control" name="email_address" id="edit-email-address">
      </div>
      <div class="col-md-4">
        <label for="edit-fax-number" class="form-label">Fax Number</label>
        <input type="text" class="form-control" name="fax_number" id="edit-fax-number">
      </div>
      <div class="col-md-4">
        <label for="edit-website" class="form-label">Website</label>
        <input type="text" class="form-control" name="website" id="edit-website">
      </div>
    </div>
  </div>

  <!-- Business Tab -->
  <div class="tab-pane fade" id="edit-business" role="tabpanel" aria-labelledby="edit-business-tab">
    <div class="row g-2">
      <div class="col-md-6">
        <label for="edit-business-type" class="form-label">Business Type</label>
        <select id="edit-business-type" name="business_type" class="form-select">
          <option value="">-- Select Business Type --</option>
          <option value="IT Equipment Supplier">IT Equipment Supplier</option>
          <option value="Office Equipment Vendor">Office Equipment Vendor</option>
          <option value="Air Conditioning Equipment Supplier">Air Conditioning Equipment Supplier</option>
          <option value="Equipment Maintenance Provider">Equipment Maintenance Provider</option>
          <option value="Furniture Supplier">Furniture Supplier</option>
          <option value="Laboratory Equipment Supplier">Laboratory Equipment Supplier</option>
          <option value="Construction and Renovation Contractor">Construction and Renovation Contractor</option>
          <option value="Machinery and Equipment Supplier">Machinery and Equipment Supplier</option>
          <option value="Janitorial Services">Janitorial Services</option>
          <option value="Educational Materials Supplier">Educational Materials Supplier</option>
          <option value="Medical Supplies Provider">Medical Supplies Provider</option>
          <option value="Printing Services">Printing Services</option>
          <option value="Logistics and Delivery Services">Logistics and Delivery Services</option>
          <option value="Electrical Supplies Provider">Electrical Supplies Provider</option>
        </select>
      </div>

      <div class="col-md-6">
        <label for="edit-category" class="form-label">Category</label>
        <select id="edit-category" name="category" class="form-select">
          <option value="">-- Select Category --</option>
        </select>
      </div>

      <div class="col-md-6">
        <label for="edit-payment-terms" class="form-label">Payment Terms</label>
        <input type="text" class="form-control" name="payment_terms" id="edit-payment-terms">
      </div>
    </div>
  </div>

  <!-- Additional Info Tab -->
  <div class="tab-pane fade" id="edit-extra" role="tabpanel" aria-labelledby="edit-extra-tab">
    <div class="row g-2">
      <div class="col-md-6">
        <label for="edit-date-registered" class="form-label">Date Registered</label>
        <input type="date" class="form-control" name="date_registered" id="edit-date-registered">
      </div>
      <div class="col-md-6">
        <label for="edit-status" class="form-label">Status</label>
        <select name="status" id="edit-status" class="form-select">
          <option value="">-- Status --</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div class="col-12">
        <label for="edit-notes" class="form-label">Notes (optional)</label>
        <textarea class="form-control" name="notes" id="edit-notes" rows="3"></textarea>
      </div>
    </div>
  </div>

</div>