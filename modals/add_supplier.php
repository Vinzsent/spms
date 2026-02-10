<form action="../actions/add_supplier.php" method="POST">
  <!-- Tab Navigation -->
  <ul class="nav nav-tabs" id="addSupplierTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab">Company Info</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Contact</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="business-tab" data-bs-toggle="tab" data-bs-target="#business" type="button" role="tab">Business</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="extra-tab" data-bs-toggle="tab" data-bs-target="#extra" type="button" role="tab">Additional Info</button>
    </li>
  </ul>

  <!-- Tab Content -->
  <div class="tab-content pt-3" id="addSupplierTabContent">

    <!-- Company Info -->
    <div class="tab-pane fade show active" id="company" role="tabpanel">
      <div class="row g-2">
        <div class="col-md-12">
          <label for="supplier-name" class="form-label">Supplier Name <span style="color: red">*</span></label>
          <input type="text" class="form-control" name="supplier_name" id="supplier-name" required>
        </div>
        <div class="col-md-12">
          <label for="address" class="form-label">Full Address <span style="color: red;">*</span></label>
          <input type="text" class="form-control" name="address" id="address">
        </div>
        <div class="col-md-6">
          <label for="province" class="form-label">Province <span style="color: red;">*</span></label>
          <select name="province" id="province" class="form-select" required>
            <option value="">-- Select Province --</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="city" class="form-label">City / Municipality <span style="color: red;">*</span></label>
          <select name="city" id="city" class="form-select" required>
            <option value="">-- Select City --</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="zip-code" class="form-label">ZIP Code <span style="color: red;">*</span></label>
          <input type="text" class="form-control" value="8000" name="zip_code" id="zip-code" readonly>
        </div>
        <div class="col-md-6">
          <label for="country" class="form-label">Country <span style="color: red;">*</span></label>
          <select name="country" id="country" class="form-select">
            <option value="">-- Select Country --</option>
            <option value="Philippines" selected>Philippines</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Contact Tab -->
    <div class="tab-pane fade" id="contact" role="tabpanel">
      <div class="row g-2">
        <div class="col-md-6">
          <label for="contact-person" class="form-label">Contact Person <span style="color: red;">*</span></label>
          <input type="text" class="form-control" name="contact_person" id="contact-person" required>
        </div>
        <div class="col-md-6">
          <label for="landline-number" class="form-label">Landline Number <span style="color: red;">*</span></label>
          <input type="text" class="form-control" name="landline_number" id="landline-number" required>
        </div>
        <div class="col-md-4">
          <label for="contact-number" class="form-label">Contact Number <span style="color: red;">*</span></label>
          <input type="text" class="form-control" name="contact_number" id="contact-number">
        </div>
        <div class="col-md-4">
          <label for="email-address" class="form-label">Email Address <span style="color: red;">*</span></label>
          <input type="email" class="form-control" name="email_address" id="email-address">
        </div>
        <div class="col-md-4">
          <label for="fax-number" class="form-label">Fax Number <span style="color: red;">*</span></label>
          <input type="text" class="form-control" name="fax_number" id="fax-number">
        </div>
        <div class="col-md-4">
          <label for="website" class="form-label">Website</label>
          <input type="text" class="form-control" name="website" id="website">
        </div>
      </div>
    </div>

    <!-- Business Tab -->
    <div class="tab-pane fade" id="business" role="tabpanel">
      <div class="row g-2">
        <div class="col-md-6">
          <label for="business-type" class="form-label">Business Type <span style="color: red;">*</span></label>
          <select name="business_type" id="business-type" class="form-select">
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
          <label for="product-category" class="form-label">Category <span style="color: red;">*</span></label>
          <select name="category" id="productcategory" class="form-select" required>
            <option value="">-- Select Category --</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="payment-terms" class="form-label">Payment Terms <span style="color: red;">*</span></label>
          <select name="payment_terms" id="payment-terms" class="form-select" required>
            <option value="">-- Select Payment Terms --</option>
            <option value="Cash">Cash</option>
            <option value="Credit">Credit</option>
            <option value="Credit Card">Credit Card</option>
            <option value="Debit Card">Debit Card</option>
            <option value="PayPal">PayPal</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Additional Info -->
    <div class="tab-pane fade" id="extra" role="tabpanel">
      <div class="row g-2">
        <div class="col-md-6">
          <label for="date-registered" class="form-label">Date Registered <span style="color: red;">*</span></label>
          <input type="date" class="form-control" name="date_registered" id="date-registered" required>
        </div>
        <div class="col-md-6">
          <label for="status" class="form-label">Status <span style="color: red;">*</span></label>
          <select name="status" id="status" class="form-select" required>
            <option value="">-- Status --</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
        <div class="col-12">
          <label for="notes" class="form-label">Notes (optional)</label>
          <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
        </div>
      </div>
    </div>

  </div>

  <!-- Submit Button -->
  <div class="mt-3">
    <button type="submit" class="btn btn-success w-100">💾 Save Supplier</button>
  </div>
</form>