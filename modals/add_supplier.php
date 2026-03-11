<form id="addSupplierForm" action="../actions/add_supplier.php" method="POST">
  <!-- Stepper Navigation (Breadcrumbs) -->
  <div class="stepper-wrapper mb-4">
    <div class="stepper-item active" data-step="1">
      <div class="step-counter">1</div>
      <div class="step-name">Company</div>
    </div>
    <div class="stepper-item" data-step="2">
      <div class="step-counter">2</div>
      <div class="step-name">Contact</div>
    </div>
    <div class="stepper-item" data-step="3">
      <div class="step-counter">3</div>
      <div class="step-name">Business</div>
    </div>
    <div class="stepper-item" data-step="4">
      <div class="step-counter">4</div>
      <div class="step-name">Status & Notes</div>
    </div>
    <div class="stepper-item" data-step="5">
      <div class="step-counter">5</div>
      <div class="step-name">Review</div>
    </div>
  </div>

  <!-- Company Info -->
  <div class="step-content px-1 active" id="step-1">
    <div class="row g-3">
      <div class="col-md-12">
        <label for="supplier-name" class="form-label">Supplier Name <span class="text-danger">*</span> </label>
        <input type="text" class="form-control" name="supplier_name" id="supplier-name" required>
      </div>
      <div class="col-md-12">
        <label for="address" class="form-label">Full Address</label>
        <input type="text" class="form-control" name="address" id="address">
      </div>
      <div class="col-md-6">
        <label for="province" class="form-label">Province</label>
        <select name="province" id="province" class="form-select">
          <option value="">-- Select Province --</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="city" class="form-label">City / Municipality</label>
        <select name="city" id="city" class="form-select">
          <option value="">-- Select City --</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="zip-code" class="form-label">ZIP Code</label>
        <input type="text" class="form-control" value="8000" name="zip_code" id="zip-code">
      </div>
      <div class="col-md-6">
        <label for="country" class="form-label">Country</label>
        <select name="country" id="country" class="form-select">
          <option value="">-- Select Country --</option>
          <option value="Philippines" selected>Philippines</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Contact Tab -->
  <div class="step-content px-1 d-none" id="step-2">
    <div class="row g-3">
      <div class="col-md-12">
        <label for="contact-person" class="form-label">Contact Person</label>
        <input type="text" class="form-control" name="contact_person" id="contact-person">
      </div>
      <div class="col-md-6">
        <label for="landline-number" class="form-label">Telephone No.</label>
        <input type="text" class="form-control" name="landline_number" id="landline-number">
      </div>
      <div class="col-md-6">
        <label for="contact-number" class="form-label">Mobile No.</label>
        <input type="text" class="form-control" name="contact_number" id="contact-number">
      </div>
      <div class="col-md-4">
        <label for="email-address" class="form-label">Email Address</label>
        <input type="email" class="form-control" name="email_address" id="email-address">
      </div>
      <div class="col-md-4">
        <label for="fax-number" class="form-label">Fax Number</label>
        <input type="text" class="form-control" name="fax_number" id="fax-number">
      </div>
      <div class="col-md-4">
        <label for="website" class="form-label">Website</label>
        <input type="text" class="form-control" name="website" id="website">
      </div>
    </div>
  </div>

  <!-- Business Tab -->
  <div class="step-content px-1 d-none" id="step-3">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="business-type-search" class="form-label">Business Type</label>
        <div class="business-type-dropdown-container">
          <input type="text" id="business-type-search" class="form-control" placeholder="Search or Select..." onfocus="showBusinessTypeDropdown(this)" oninput="filterBusinessTypes(this)" onblur="hideBusinessTypeDropdown(this)">
          <input type="hidden" name="business_type" id="business-type">
          <div class="business-type-list" id="business-type-list"></div>
        </div>
      </div>
      <div class="col-md-6">
        <label for="category-search" class="form-label">Category</label>
        <div class="category-dropdown-container">
          <input type="text" id="category-search" class="form-control" placeholder="Search or type..." onfocus="showCategoryDropdown(this)" oninput="filterCategories(this)" onblur="hideCategoryDropdown(this)">
          <input type="hidden" name="category" id="product-category">
          <div id="category-list" class="category-list"></div>
        </div>
      </div>
      <div class="col-md-6">
        <label for="tax-identification-number" class="form-label">TIN Number</label>
        <input type="text" class="form-control" name="tax_identification_number" id="tax-identification-number" placeholder="000-000-000-000">
      </div>
      <div class="col-md-6">
        <label for="payment-terms" class="form-label">Payment Terms</label>
        <select name="payment_terms" id="payment-terms" class="form-select">
          <option value="">-- Select --</option>
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

  <!-- Status & Notes Tab -->
  <div class="step-content px-1 d-none" id="step-4">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="date-registered" class="form-label">Date Registered</label>
        <input type="date" class="form-control" name="date_registered" id="date-registered">
      </div>
      <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select">
          <option value="">-- Select Status --</option>
          <option value="ACTIVE" selected>Active</option>
          <option value="INACTIVE">Inactive</option>
        </select>
      </div>
      <div class="col-12">
        <label for="notes" class="form-label">Notes (optional)</label>
        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Additional details about the supplier..."></textarea>
      </div>
    </div>
  </div>

  <!-- Review Tab -->
  <div class="step-content px-1 d-none" id="step-5">
    <div id="review-summary-container">
      <!-- Generated by populateReviewTab() -->
    </div>
  </div>
  </div>

  <!-- Stepper Controls -->
  <div class="footer-controls d-flex justify-content-between mt-4 border-top pt-3">
    <button type="button" class="btn btn-secondary px-4 d-none" id="prevStepBtn" onclick="moveStep(-1)">
      <i class="fas fa-arrow-left me-2"></i> Previous
    </button>
    <div class="ms-auto">
      <button type="button" class="btn btn-primary px-4" id="nextStepBtn" onclick="moveStep(1)">
        Next <i class="fas fa-arrow-right ms-2"></i>
      </button>
      <button type="submit" class="btn btn-success px-4 d-none" id="submitSupplierBtn">
        <i class="fas fa-save me-2"></i> Save Supplier
      </button>
    </div>
  </div>
</form>

<style>
  /* Stepper UI Styles */
  .stepper-wrapper {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding-bottom: 20px;
  }

  .stepper-wrapper::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e2e8f0;
    z-index: 1;
  }

  .stepper-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    z-index: 2;
    cursor: pointer;
  }

  .step-counter {
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    margin-bottom: 8px;
    font-weight: 700;
    color: #64748b;
    transition: all 0.3s ease;
  }

  .step-name {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .stepper-item.active .step-counter {
    background: #1a5f3c;
    border-color: #1a5f3c;
    color: white;
    box-shadow: 0 0 0 4px rgba(26, 95, 60, 0.1);
  }

  .stepper-item.active .step-name {
    color: #1a5f3c;
  }

  .stepper-item.completed .step-counter {
    background: #10b981;
    border-color: #10b981;
    color: white;
  }

  .stepper-item.completed::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: -5px;
    right: 25%;
    font-size: 0.8rem;
    background: #10b981;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 2px solid white;
  }

  /* Form Content Fade */
  .step-content {
    animation: fadeIn 0.3s ease-in-out;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(5px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .business-type-dropdown-container,
  .category-dropdown-container {
    position: relative;
    width: 100%;
  }

  .business-type-list,
  .category-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    max-height: 200px;
    overflow-y: auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    z-index: 1050;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    display: none;
    margin-top: 5px;
  }

  .business-type-item,
  .category-item {
    padding: 10px 15px;
    cursor: pointer;
    transition: all 0.2s;
    color: #333;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.9rem;
  }

  .business-type-item:hover,
  .category-item:hover {
    background: rgba(7, 59, 29, 0.08);
    color: #073b1d;
  }

  .add-business-type-item,
  .add-category-item {
    display: block;
    padding: 12px;
    background: #073b1d;
    color: white !important;
    text-decoration: none;
    font-weight: 600;
    text-align: center;
    position: sticky;
    bottom: 0;
    border-top: 1px solid #052c16;
    font-size: 0.85rem;
  }

  .add-business-type-item:hover,
  .add-category-item:hover {
    background: #0d4a2a;
    color: #EACA26 !important;
  }

  #business-type-search:focus,
  #category-search:focus {
    border-color: #073b1d;
    box-shadow: 0 0 0 0.25rem rgba(7, 59, 29, 0.25);
  }

  /* Review Summary Styles */
  .review-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
  }

  .review-section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1a5f3c;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid rgba(26, 95, 60, 0.1);
    display: flex;
    align-items: center;
  }

  .review-section-title i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
  }

  .review-item {
    margin-bottom: 12px;
  }

  .review-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    margin-bottom: 2px;
  }

  .review-value {
    font-size: 0.95rem;
    font-weight: 500;
    color: #1e293b;
    word-break: break-all;
  }

  .review-value.empty {
    color: #94a3b8;
    font-style: italic;
  }
</style>

<script>
  let currentStep = 1;
  const totalSteps = 5;

  function moveStep(direction) {
    // Hide current step content
    document.getElementById(`step-${currentStep}`).classList.add('d-none');
    document.getElementById(`step-${currentStep}`).classList.remove('active');

    // Update step index
    currentStep += direction;

    // Safety check
    if (currentStep < 1) currentStep = 1;
    if (currentStep > totalSteps) currentStep = totalSteps;

    updateStepperUI();

    if (currentStep === 5) {
      populateReviewTab();
    }
  }

  function goToStep(step) {
    if (step === currentStep) return;

    document.getElementById(`step-${currentStep}`).classList.add('d-none');
    document.getElementById(`step-${currentStep}`).classList.remove('active');

    currentStep = step;

    document.getElementById(`step-${currentStep}`).classList.remove('d-none');
    setTimeout(() => {
      document.getElementById(`step-${currentStep}`).classList.add('active');
    }, 10);

    updateStepperUI();

    if (currentStep === 5) {
      populateReviewTab();
    }
  }

  function updateStepperUI() {
    // Update Stepper markers
    const items = document.querySelectorAll('.stepper-item');
    items.forEach(item => {
      const step = parseInt(item.getAttribute('data-step'));
      item.classList.remove('active', 'completed');

      if (step === currentStep) {
        item.classList.add('active');
      } else if (step < currentStep) {
        item.classList.add('completed');
      }
    });

    // Update Buttons
    const prevBtn = document.getElementById('prevStepBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const submitBtn = document.getElementById('submitSupplierBtn');

    if (currentStep === 1) {
      prevBtn.classList.add('d-none');
    } else {
      prevBtn.classList.remove('d-none');
    }

    if (currentStep === totalSteps) {
      nextBtn.classList.add('d-none');
      submitBtn.classList.remove('d-none');
    } else {
      nextBtn.classList.remove('d-none');
      submitBtn.classList.add('d-none');
    }
  }

  // Allow clicking stepper items
  document.querySelectorAll('.stepper-item').forEach(item => {
    item.addEventListener('click', () => {
      const step = parseInt(item.getAttribute('data-step'));
      goToStep(step);
    });
  });

  function showBusinessTypeDropdown(input) {
    const list = document.getElementById('business-type-list');
    populateBusinessTypeList(); // Ensure list is up to date
    list.style.display = 'block';
  }

  function hideBusinessTypeDropdown(input) {
    const list = document.getElementById('business-type-list');
    setTimeout(() => {
      list.style.display = 'none';
    }, 250);
  }

  function filterBusinessTypes(input) {
    const filter = input.value.toLowerCase();
    const list = document.getElementById('business-type-list');
    const items = list.getElementsByClassName('business-type-item');
    let hasMatch = false;

    for (let i = 0; i < items.length; i++) {
      const txtValue = items[i].textContent || items[i].innerText;
      if (txtValue.toLowerCase().indexOf(filter) > -1) {
        items[i].style.display = "";
        hasMatch = true;
      } else {
        items[i].style.display = "none";
      }
    }

    // Show/hide "Add New" button based on whether there's an exact match
    const addBtn = list.querySelector('.add-business-type-item');
    if (addBtn) {
      const exactMatch = Array.from(items).some(item => (item.textContent || item.innerText).toLowerCase() === filter);
      addBtn.style.display = (filter && !exactMatch) ? 'block' : 'none';
      if (filter && !exactMatch) {
        addBtn.innerHTML = `<i class="fas fa-plus me-1"></i> Add "${input.value}"`;
      }
    }
  }

  function populateBusinessTypeList() {
    const list = document.getElementById('business-type-list');
    // Save the current search term if any
    const searchInput = document.getElementById('business-type-search');
    const currentFilter = searchInput.value.toLowerCase();

    // Clear existing
    list.innerHTML = '';

    // Get types from categoryMap (global from category-mapping.js)
    if (typeof categoryMap !== 'undefined') {
      Object.keys(categoryMap).sort().forEach(type => {
        const div = document.createElement('div');
        div.className = 'business-type-item';
        div.textContent = type;
        div.onmousedown = function() {
          selectBusinessType(type);
        };
        list.appendChild(div);
      });
    }

    // Add the "Add New" button
    const addBtn = document.createElement('a');
    addBtn.href = 'javascript:void(0)';
    addBtn.className = 'add-business-type-item';
    addBtn.style.display = 'none';
    addBtn.onmousedown = function() {
      const newVal = searchInput.value;
      if (newVal) addNewBusinessType(newVal);
    };
    list.appendChild(addBtn);

    // Re-apply filter if needed
    if (currentFilter) filterBusinessTypes(searchInput);
  }

  // Listen for data loads
  document.addEventListener('businessTypesLoaded', function() {
    populateBusinessTypeList();
  });

  function selectBusinessType(name) {
    const searchInput = document.getElementById('business-type-search');
    const hiddenInput = document.getElementById('business-type');

    searchInput.value = name;
    hiddenInput.value = name;

    // Trigger change event for category-mapping.js to pick up
    const event = new Event('change', {
      bubbles: true
    });
    hiddenInput.dispatchEvent(event);

    document.getElementById('business-type-list').style.display = 'none';
  }

  function addNewBusinessType(name) {
    if (!name) return;

    // Call backend to save
    fetch('../actions/save_business_type.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'type_name=' + encodeURIComponent(name)
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          if (typeof categoryMap !== 'undefined' && !categoryMap[name]) {
            categoryMap[name] = []; // Add to local map temporarily
          }
          selectBusinessType(name);
          console.log("Business type saved:", name);
        } else {
          alert('Error saving business type: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        // Fallback for offline or error: still select it so user can proceed
        if (typeof categoryMap !== 'undefined' && !categoryMap[name]) {
          categoryMap[name] = [];
        }
        selectBusinessType(name);
      });
  }

  // --- Category Searchable Dropdown Logic ---

  function showCategoryDropdown(input) {
    const list = document.getElementById('category-list');
    populateCategoryList();
    list.style.display = 'block';
  }

  function hideCategoryDropdown(input) {
    const list = document.getElementById('category-list');
    setTimeout(() => {
      list.style.display = 'none';
    }, 250);
  }

  function filterCategories(input) {
    const filter = input.value.toLowerCase();
    const list = document.getElementById('category-list');
    const items = list.getElementsByClassName('category-item');
    let hasMatch = false;

    for (let i = 0; i < items.length; i++) {
      const txtValue = items[i].textContent || items[i].innerText;
      if (txtValue.toLowerCase().indexOf(filter) > -1) {
        items[i].style.display = "";
        hasMatch = true;
      } else {
        items[i].style.display = "none";
      }
    }

    const addBtn = list.querySelector('.add-category-item');
    if (addBtn) {
      const exactMatch = Array.from(items).some(item => (item.textContent || item.innerText).toLowerCase() === filter);
      addBtn.style.display = (filter && !exactMatch) ? 'block' : 'none';
      if (filter && !exactMatch) {
        addBtn.innerHTML = `<i class="fas fa-plus me-1"></i> Add "${input.value}"`;
      }
    }
  }

  function populateCategoryList() {
    const list = document.getElementById('category-list');
    const bType = document.getElementById('business-type').value;
    const searchInput = document.getElementById('category-search');
    const currentFilter = searchInput.value.toLowerCase();

    list.innerHTML = '';

    // Standard options (N/A)
    const naDiv = document.createElement('div');
    naDiv.className = 'category-item';
    naDiv.textContent = 'N/A';
    naDiv.onmousedown = function() {
      selectCategory('N/A');
    };
    list.appendChild(naDiv);

    if (bType && typeof categoryMap !== 'undefined' && categoryMap[bType]) {
      categoryMap[bType].sort().forEach(cat => {
        const div = document.createElement('div');
        div.className = 'category-item';
        div.textContent = cat;
        div.onmousedown = function() {
          selectCategory(cat);
        };
        list.appendChild(div);
      });
    }

    // Add "Add New" button
    const addBtn = document.createElement('a');
    addBtn.href = 'javascript:void(0)';
    addBtn.className = 'add-category-item';
    addBtn.style.display = 'none';
    addBtn.onmousedown = function() {
      const newVal = searchInput.value;
      if (newVal) addNewCategory(newVal);
    };
    list.appendChild(addBtn);

    if (currentFilter) filterCategories(searchInput);
  }

  function selectCategory(name) {
    const searchInput = document.getElementById('category-search');
    const hiddenInput = document.getElementById('product-category');

    searchInput.value = name;
    hiddenInput.value = name;

    document.getElementById('category-list').style.display = 'none';
  }

  function addNewCategory(name) {
    const bType = document.getElementById('business-type').value;
    if (!bType) {
      alert('Please select a Business Type first!');
      return;
    }

    // Call backend
    fetch('../actions/save_category.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `business_type_name=${encodeURIComponent(bType)}&category_name=${encodeURIComponent(name)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          if (!categoryMap[bType].includes(name)) {
            categoryMap[bType].push(name);
          }
          selectCategory(name);
        } else {
          alert('Error saving category: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (!categoryMap[bType].includes(name)) {
          categoryMap[bType].push(name);
        }
        selectCategory(name);
      });
  }

  // Clear category when business type changes
  document.getElementById('business-type').addEventListener('change', function() {
    document.getElementById('category-search').value = '';
    document.getElementById('product-category').value = '';
    populateCategoryList();
  });

  function populateReviewTab() {
    const container = document.getElementById('review-summary-container');
    const form = document.getElementById('addSupplierForm');
    const formData = new FormData(form);

    const getVal = (name, label) => {
      let val = formData.get(name);

      // Handle specific fields that might need better display
      if (name === 'business_type' && !val) val = document.getElementById('business-type-search').value;
      if (name === 'category' && !val) val = document.getElementById('category-search').value;

      if (!val || val.trim() === '') {
        return `<div class="review-item col-md-4">
                  <div class="review-label">${label}</div>
                  <div class="review-value empty">N/A</div>
                </div>`;
      }
      return `<div class="review-item col-md-4">
                <div class="review-label">${label}</div>
                <div class="review-value">${val}</div>
              </div>`;
    };

    let html = `
      <div class="alert alert-info mb-4" style="background-color: rgba(26, 95, 60, 0.05); border-color: rgba(26, 95, 60, 0.2); color: #1a5f3c; border-radius: 12px;">
        <i class="fas fa-info-circle me-2"></i> Please review the supplier information before saving.
      </div>
      
      <div class="review-section">
        <div class="review-section-title"><i class="fas fa-building"></i> Company Information</div>
        <div class="row">
          ${getVal('supplier_name', 'Supplier Name')}
          ${getVal('address', 'Full Address')}
          <div class="review-item col-md-4">
            <div class="review-label">Province/City</div>
            <div class="review-value">${formData.get('province') || 'N/A'} / ${formData.get('city') || 'N/A'}</div>
          </div>
          ${getVal('zip_code', 'ZIP Code')}
          ${getVal('country', 'Country')}
        </div>
      </div>

      <div class="review-section">
        <div class="review-section-title"><i class="fas fa-address-book"></i> Contact Details</div>
        <div class="row">
          ${getVal('contact_person', 'Contact Person')}
          ${getVal('landline_number', 'Telephone No.')}
          ${getVal('contact_number', 'Mobile No.')}
          ${getVal('email_address', 'Email Address')}
          ${getVal('fax_number', 'Fax Number')}
          ${getVal('website', 'Website')}
        </div>
      </div>

      <div class="review-section">
        <div class="review-section-title"><i class="fas fa-briefcase"></i> Business Info</div>
        <div class="row">
          ${getVal('business_type', 'Business Type')}
          ${getVal('category', 'Category')}
          ${getVal('tax_identification_number', 'TIN Number')}
          ${getVal('payment_terms', 'Payment Terms')}
        </div>
      </div>

      <div class="review-section">
        <div class="review-section-title"><i class="fas fa-cog"></i> Status & Registration</div>
        <div class="row">
          ${getVal('date_registered', 'Date Registered')}
          ${getVal('status', 'Status')}
          <div class="review-item col-md-12">
            <div class="review-label">Notes</div>
            <div class="review-value">${formData.get('notes') || '<span class="empty">No notes provided</span>'}</div>
          </div>
        </div>
      </div>
    `;

    container.innerHTML = html;
  }
</script>