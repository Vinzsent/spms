<!-- Hidden field for supplier ID -->
<form id="editSupplierForm" action="../actions/edit_supplier.php" method="post">
  <input type="hidden" name="supplier_id" id="edit-supplier-id">

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

  <!-- Company Info Tab -->
  <div class="step-content px-1 active" id="edit-step-1">
    <div class="row g-3">
      <div class="col-md-12">
        <label for="edit-supplier-name" class="form-label">Supplier Name</label>
        <input type="text" class="form-control" name="supplier_name" id="edit-supplier-name">
      </div>
      <div class="col-md-12">
        <label for="edit-address" class="form-label">Full Address</label>
        <input type="text" class="form-control" name="address" id="edit-address">
      </div>
      <div class="col-md-6">
        <label for="edit-province" class="form-label">Province</label>
        <select name="province" id="edit-province" class="form-select">
          <option value="">-- Select Province --</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="edit-city" class="form-label">City / Municipality</label>
        <select name="city" id="edit-city" class="form-select">
          <option value="">-- Select City --</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="edit-zip-code" class="form-label">ZIP Code</label>
        <input type="text" class="form-control" name="zip_code" id="edit-zip-code">
      </div>
      <div class="col-md-6">
        <label for="edit-country" class="form-label">Country</label>
        <select name="country" id="edit-country" class="form-select">
          <option value="">-- Select Country --</option>
          <option value="Philippines" selected>Philippines</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Contact Tab -->
  <div class="step-content px-1 d-none" id="edit-step-2">
    <div class="row g-3">
      <div class="col-md-12">
        <label for="edit-contact-person" class="form-label">Contact Person</label>
        <input type="text" class="form-control" name="contact_person" id="edit-contact-person">
      </div>
      <div class="col-md-6">
        <label for="edit-landline-number" class="form-label">Telephone No.</label>
        <input type="text" class="form-control" name="landline_number" id="edit-landline-number">
      </div>
      <div class="col-md-6">
        <label for="edit-contact-number" class="form-label">Mobile No.</label>
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
  <div class="step-content px-1 d-none" id="edit-step-3">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="edit-business-type-search" class="form-label">Business Type</label>
        <div class="business-type-dropdown-container">
          <input type="text" id="edit-business-type-search" class="form-control" placeholder="Search or Select..." onfocus="showEditBusinessTypeDropdown(this)" oninput="filterEditBusinessTypes(this)" onblur="hideEditBusinessTypeDropdown(this)">
          <input type="hidden" name="business_type" id="edit-business-type">
          <div class="business-type-list" id="edit-business-type-list"></div>
        </div>
      </div>
      <div class="col-md-6">
        <label for="edit-category-search" class="form-label">Category</label>
        <div class="category-dropdown-container">
          <input type="text" id="edit-category-search" class="form-control" placeholder="Search or type..." onfocus="showEditCategoryDropdown(this)" oninput="filterEditCategories(this)" onblur="hideEditCategoryDropdown(this)" autocomplete="off">
          <input type="hidden" name="category" id="edit-category">
          <div id="edit-category-list" class="category-list"></div>
        </div>
      </div>
      <div class="col-md-6">
        <label for="edit-tax-identification-number" class="form-label">TIN Number</label>
        <input type="text" class="form-control" name="tax_identification_number" id="edit-tax-identification-number" placeholder="000-000-000-000">
      </div>
      <div class="col-md-6">
        <label for="edit-payment-terms" class="form-label">Payment Terms</label>
        <select name="payment_terms" id="edit-payment-terms" class="form-select">
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

  <!-- Additional Info Tab -->
  <div class="step-content px-1 d-none" id="edit-step-4">
    <div class="row g-3">
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
        <textarea class="form-control" name="notes" id="edit-notes" rows="3" placeholder="Additional details..."></textarea>
      </div>
    </div>
  </div>

  <!-- Review Tab -->
  <div class="step-content px-1 d-none" id="edit-step-5">
    <div id="edit-review-summary-container">
      <!-- Generated by populateEditReviewTab() -->
    </div>
  </div>
  </div>

  <!-- Stepper Controls -->
  <div class="footer-controls d-flex justify-content-between mt-4 border-top pt-3">
    <button type="button" class="btn btn-secondary px-4 d-none" id="editPrevStepBtn" onclick="moveEditStep(-1)">
      <i class="fas fa-arrow-left me-2"></i> Previous
    </button>
    <div class="ms-auto">
      <button type="button" class="btn btn-primary px-4" id="editNextStepBtn" onclick="moveEditStep(1)">
        Next <i class="fas fa-arrow-right ms-2"></i>
      </button>
      <button type="submit" class="btn btn-success px-4 d-none" id="updateSupplierBtn">
        <i class="fas fa-save me-2"></i> Update Supplier
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

  #edit-business-type-search:focus,
  #edit-category-search:focus {
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
  let editCurrentStep = 1;
  const editTotalSteps = 5;

  function moveEditStep(direction) {
    document.getElementById(`edit-step-${editCurrentStep}`).classList.add('d-none');
    document.getElementById(`edit-step-${editCurrentStep}`).classList.remove('active');

    editCurrentStep += direction;

    if (editCurrentStep < 1) editCurrentStep = 1;
    if (editCurrentStep > editTotalSteps) editCurrentStep = editTotalSteps;

    document.getElementById(`edit-step-${editCurrentStep}`).classList.remove('d-none');
    setTimeout(() => {
      document.getElementById(`edit-step-${editCurrentStep}`).classList.add('active');
    }, 10);

    updateEditStepperUI();

    if (editCurrentStep === 5) {
      populateEditReviewTab();
    }
  }

  function goToEditStep(step) {
    if (step === editCurrentStep) return;

    document.getElementById(`edit-step-${editCurrentStep}`).classList.add('d-none');
    document.getElementById(`edit-step-${editCurrentStep}`).classList.remove('active');

    editCurrentStep = step;

    document.getElementById(`edit-step-${editCurrentStep}`).classList.remove('d-none');
    setTimeout(() => {
      document.getElementById(`edit-step-${editCurrentStep}`).classList.add('active');
    }, 10);

    updateEditStepperUI();

    if (editCurrentStep === 5) {
      populateEditReviewTab();
    }
  }

  function updateEditStepperUI() {
    const items = document.querySelectorAll('#editModal .stepper-item');
    items.forEach(item => {
      const step = parseInt(item.getAttribute('data-step'));
      item.classList.remove('active', 'completed');

      if (step === editCurrentStep) {
        item.classList.add('active');
      } else if (step < editCurrentStep) {
        item.classList.add('completed');
      }
    });

    const prevBtn = document.getElementById('editPrevStepBtn');
    const nextBtn = document.getElementById('editNextStepBtn');
    const submitBtn = document.getElementById('updateSupplierBtn');

    if (editCurrentStep === 1) {
      prevBtn.classList.add('d-none');
    } else {
      prevBtn.classList.remove('d-none');
    }

    if (editCurrentStep === editTotalSteps) {
      nextBtn.classList.add('d-none');
      submitBtn.classList.remove('d-none');
    } else {
      nextBtn.classList.remove('d-none');
      submitBtn.classList.add('d-none');
    }
  }

  // Allow clicking stepper items
  document.querySelectorAll('#editModal .stepper-item').forEach(item => {
    item.addEventListener('click', () => {
      const step = parseInt(item.getAttribute('data-step'));
      goToEditStep(step);
    });
  });

  // Reset stepper when modal opens
  document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    if (editModal) {
      editModal.addEventListener('show.bs.modal', function() {
        goToEditStep(1);
      });
    }
  });

  function showEditBusinessTypeDropdown(input) {
    const list = document.getElementById('edit-business-type-list');
    populateEditBusinessTypeList();
    list.style.display = 'block';
  }

  function hideEditBusinessTypeDropdown(input) {
    const list = document.getElementById('edit-business-type-list');
    setTimeout(() => {
      list.style.display = 'none';
    }, 250);
  }

  function filterEditBusinessTypes(input) {
    const filter = input.value.toLowerCase();
    const list = document.getElementById('edit-business-type-list');
    const items = list.getElementsByClassName('business-type-item');

    for (let i = 0; i < items.length; i++) {
      const txtValue = items[i].textContent || items[i].innerText;
      if (txtValue.toLowerCase().indexOf(filter) > -1) {
        items[i].style.display = "";
      } else {
        items[i].style.display = "none";
      }
    }

    const addBtn = list.querySelector('.add-business-type-item');
    if (addBtn) {
      const exactMatch = Array.from(items).some(item => (item.textContent || item.innerText).toLowerCase() === filter);
      addBtn.style.display = (filter && !exactMatch) ? 'block' : 'none';
      if (filter && !exactMatch) {
        addBtn.innerHTML = `<i class="fas fa-plus me-1"></i> Add "${input.value}"`;
      }
    }
  }

  function populateEditBusinessTypeList() {
    const list = document.getElementById('edit-business-type-list');
    const searchInput = document.getElementById('edit-business-type-search');
    const currentFilter = searchInput.value.toLowerCase();

    list.innerHTML = '';

    if (typeof categoryMap !== 'undefined') {
      Object.keys(categoryMap).sort().forEach(type => {
        const div = document.createElement('div');
        div.className = 'business-type-item';
        div.textContent = type;
        div.onmousedown = function() {
          selectEditBusinessType(type);
        };
        list.appendChild(div);
      });
    }

    const addBtn = document.createElement('a');
    addBtn.href = 'javascript:void(0)';
    addBtn.className = 'add-business-type-item';
    addBtn.style.display = 'none';
    addBtn.onmousedown = function() {
      const newVal = searchInput.value;
      if (newVal) addNewEditBusinessType(newVal);
    };
    list.appendChild(addBtn);

    if (currentFilter) filterEditBusinessTypes(searchInput);
  }

  // Listen for data loads
  document.addEventListener('businessTypesLoaded', function() {
    populateEditBusinessTypeList();
  });

  function selectEditBusinessType(name) {
    const searchInput = document.getElementById('edit-business-type-search');
    const hiddenInput = document.getElementById('edit-business-type');

    searchInput.value = name;
    hiddenInput.value = name;

    const event = new Event('change', {
      bubbles: true
    });
    hiddenInput.dispatchEvent(event);

    document.getElementById('edit-business-type-list').style.display = 'none';
  }

  function addNewEditBusinessType(name) {
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
            categoryMap[name] = [];
          }
          selectEditBusinessType(name);
          console.log("Business type saved:", name);
        } else {
          alert('Error saving business type: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (typeof categoryMap !== 'undefined' && !categoryMap[name]) {
          categoryMap[name] = [];
        }
        selectEditBusinessType(name);
      });
  }

  // --- Edit Category Searchable Dropdown Logic ---

  function showEditCategoryDropdown(input) {
    const list = document.getElementById('edit-category-list');
    populateEditCategoryList();
    list.style.display = 'block';
  }

  function hideEditCategoryDropdown(input) {
    const list = document.getElementById('edit-category-list');
    setTimeout(() => {
      list.style.display = 'none';
    }, 250);
  }

  function filterEditCategories(input) {
    const filter = input.value.toLowerCase();
    const list = document.getElementById('edit-category-list');
    const items = list.getElementsByClassName('category-item');

    for (let i = 0; i < items.length; i++) {
      const txtValue = items[i].textContent || items[i].innerText;
      if (txtValue.toLowerCase().indexOf(filter) > -1) {
        items[i].style.display = "";
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

  function populateEditCategoryList() {
    const list = document.getElementById('edit-category-list');
    const bType = document.getElementById('edit-business-type').value;
    const searchInput = document.getElementById('edit-category-search');
    const currentFilter = searchInput.value.toLowerCase();

    list.innerHTML = '';

    const naDiv = document.createElement('div');
    naDiv.className = 'category-item';
    naDiv.textContent = 'N/A';
    naDiv.onmousedown = function() {
      selectEditCategory('N/A');
    };
    list.appendChild(naDiv);

    if (bType && typeof categoryMap !== 'undefined' && categoryMap[bType]) {
      categoryMap[bType].sort().forEach(cat => {
        const div = document.createElement('div');
        div.className = 'category-item';
        div.textContent = cat;
        div.onmousedown = function() {
          selectEditCategory(cat);
        };
        list.appendChild(div);
      });
    }

    const addBtn = document.createElement('a');
    addBtn.href = 'javascript:void(0)';
    addBtn.className = 'add-category-item';
    addBtn.style.display = 'none';
    addBtn.onmousedown = function() {
      const newVal = searchInput.value;
      if (newVal) addNewEditCategory(newVal);
    };
    list.appendChild(addBtn);

    if (currentFilter) filterEditCategories(searchInput);
  }

  function selectEditCategory(name) {
    const searchInput = document.getElementById('edit-category-search');
    const hiddenInput = document.getElementById('edit-category');

    searchInput.value = name;
    hiddenInput.value = name;

    document.getElementById('edit-category-list').style.display = 'none';
  }

  function addNewEditCategory(name) {
    const bType = document.getElementById('edit-business-type').value;
    if (!bType) {
      alert('Please select a Business Type first!');
      return;
    }

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
          selectEditCategory(name);
        } else {
          alert('Error saving category: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (!categoryMap[bType].includes(name)) {
          categoryMap[bType].push(name);
        }
        selectEditCategory(name);
      });
  }

  // Clear category when business type changes
  document.getElementById('edit-business-type').addEventListener('change', function() {
    document.getElementById('edit-category-search').value = '';
    document.getElementById('edit-category').value = '';
    populateEditCategoryList();
  });

  // Handle initialization for Edit Modal
  document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    if (editModal) {
      editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        if (button) {
          const businessType = button.getAttribute('data-business-type') || '';
          const category = button.getAttribute('data-category') || '';

          const searchInput = document.getElementById('edit-business-type-search');
          const hiddenInput = document.getElementById('edit-business-type');
          const catSearchInput = document.getElementById('edit-category-search');
          const catHiddenInput = document.getElementById('edit-category');

          if (searchInput && hiddenInput) {
            searchInput.value = businessType;
            hiddenInput.value = businessType;
          }

          if (catSearchInput && catHiddenInput) {
            catSearchInput.value = category;
            catHiddenInput.value = category;
          }

          // Refresh lists
          populateEditBusinessTypeList();
          populateEditCategoryList();
        }
      });
    }
  });

  function populateEditReviewTab() {
    const container = document.getElementById('edit-review-summary-container');
    const form = document.getElementById('editSupplierForm');
    const formData = new FormData(form);

    const getVal = (name, label) => {
      let val = formData.get(name);

      if (name === 'business_type' && !val) val = document.getElementById('edit-business-type-search').value;
      if (name === 'category' && !val) val = document.getElementById('edit-category-search').value;

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
        <i class="fas fa-info-circle me-2"></i> Please review the updated supplier information before saving.
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