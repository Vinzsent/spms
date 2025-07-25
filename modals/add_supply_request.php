
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
        <div class="col-md-8">
          <label for="supplier-name" class="form-label">Supplier Name </label><span style="color:red;">*</span>
          <input type="text" class="form-control" name="supplier_name" id="supplier-name" required>
        </div>
        <div class="col-md-4">
          <label for="tin" class="form-label">TIN </label><span style="color:red;">*</span>
          <input type="text" class="form-control" name="tax_identification_number" id="tin">
        </div>
        <div class="col-md-8">
          <label for="address" class="form-label">Full Address </label><span style="color:red;">*</span>
          <input type="text" class="form-control" name="address" id="address">
        </div>
        <div class="col-md-4">
          <label for="province" class="form-label">Province </label><span style="color:red;">*</span>
          <select name="province" id="province" class="form-select" required>
            <option value="">-- Select Province --</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="city" class="form-label">City / Municipality </label><span style="color:red;">*</span>
          <select name="city" id="city" class="form-select" required>
            <option value="">-- Select City --</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="zip-code" class="form-label">ZIP Code </label><span style="color:red;">*</span>
          <input type="text" class="form-control" value="8000" name="zip_code" id="zip-code" readonly>
        </div>
        <div class="col-md-4">
          <label for="country" class="form-label">Country </label><span style="color:red;">*</span>
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
        <div class="col-md-8">
          <label for="contact-person" class="form-label">Contact Person </label><span style="color:red;">*</span>
          <input type="text" class="form-control" name="contact_person" id="contact-person" required>
        </div>
        <div class="col-md-4">
          <label for="contact-number" class="form-label">Contact Number </label><span style="color:red;">*</span>
          <input type="text" class="form-control" name="contact_number" id="contact-number">
        </div>
        <div class="col-md-4">
          <label for="email-address" class="form-label">Email Address </label><span style="color:red;">*</span>
          <input type="email" class="form-control" name="email_address" id="email-address">
        </div>
        <div class="col-md-4">
          <label for="fax-number" class="form-label">Fax Number </label><span style="color:red;">*</span>
          <input type="text" class="form-control" name="fax_number" id="fax-number">
        </div>
        <div class="col-md-4">
          <label for="website" class="form-label">Website </label><span style="color:red;">*</span>
          <input type="url" class="form-control" name="website" id="website">
        </div>
      </div>
    </div>

    <!-- Business Tab -->
    <div class="tab-pane fade" id="business" role="tabpanel">
      <div class="row g-2">
        <div class="col-md-6">
          <label for="business-type" class="form-label">Business Type </label><span style="color:red;">*</span>
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
          <label for="category" class="form-label">Category </label><span style="color:red;">*</span>
          <select name="product_category" id="category" class="form-select">
            <option value="">-- Select Category --</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="payment-terms" class="form-label">Payment Terms</label>
          <input type="text" class="form-control" name="payment_terms" id="payment-terms">
        </div>
      </div>
    </div>

    <!-- Additional Info -->
    <div class="tab-pane fade" id="extra" role="tabpanel">
      <div class="row g-2">
        <div class="col-md-6">
          <label for="date-registered" class="form-label">Date Registered </label><span style="color:red;">*</span>
          <input type="date" class="form-control" name="date_registered" id="date-registered">
        </div>
        <div class="col-md-6">
          <label for="status" class="form-label">Status </label><span style="color:red;">*</span>
          <select name="status" id="status" class="form-select">
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
    <button type="submit" class="btn btn-success w-100">💾 Save Supplier Information</button>
  </div>
</form>

<script>
// Category mappings based on business type
const categoryMappings = {
  "IT Equipment Supplier": [
    "ICT Equipment and Devices",
    "Subcription, License and Software Services"
  ],
  "Office Equipment Vendor": [
    "Office Equipment",
    "Office Supplies and Materials"
  ],
  "Air Conditioning Equipment Supplier": [
    "Air Conditioning Units and Cooling Systems",
  ],
  "Equipment Maintenance Provider": [
    "Repairs and Maintenance – Equipment and Devices",
  ],
  "Furniture Supplier": [
    "Furniture and Fixtures"
  ],
  "Laboratory Equipment Supplier": [
    "Laboratory Equipment",
    "Lab Chemicals and Reagents"
  ],
  "Construction and Renovation Contractor": [
    "Contruction Materials",
    "Renovation Services"
  ],
  "Machinery and Equipment Supplier": [
    "Heavy Machinery",
    "Production Equipment"
  ],
  "Janitorial Services": [
    "Cleaning Supplies",
    "Janitorial Services"
  ],
  "Educational Materials Supplier": [
    "Books and Publications",
    "Teaching Aids"
  ],
  "Medical Supplies Provider": [
    "Medicines",
    "Medical Equipment"
  ],
  "Printing Services": [
    "Document Printing",
    "Custom Printing"
  ],
  "Logistics and Delivery Services": [
    "Freight Services",
    "Courier Services"
  ],
  "Electrical Supplies Provider": [
    "Electrical Components",
    "Wiring and Cabling"
  ]
};

// Add event listener for business type change
document.getElementById('business-type').addEventListener('change', function() {
  const categorySelect = document.getElementById('category');
  categorySelect.innerHTML = '<option value="">-- Select Category --</option>';
  
  const selectedBusinessType = this.value;
  const categories = categoryMappings[selectedBusinessType] || [];
  
  categories.forEach(category => {
    const option = document.createElement('option');
    option.value = category;
    option.textContent = category;
    categorySelect.appendChild(option);
  });
});
</script>

<script>
$(document).ready(function() {
  // Form submission handling
  $('#addSupplierForm').on('submit', function(e) {
    e.preventDefault();
    
    // Form validation
    if (!this.checkValidity()) {
      e.stopPropagation();
      $(this).addClass('was-validated');
      return;
    }
    
    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: $(this).serialize(),
      success: function(response) {
        // Close modal
        $('#addSupplierModal').modal('hide');
        
        // Show success message
        alert('Supplier added successfully!');
        
        // Reload page to show new supplier
        location.reload();
      },
      error: function(xhr, status, error) {
        alert('Error adding supplier: ' + error);
      }
    });
  });
  
  // Reset form when modal is closed
  $('#addSupplierModal').on('hidden.bs.modal', function() {
    $('#addSupplierForm')[0].reset();
    $('#addSupplierForm').removeClass('was-validated');
  });
});
</script>