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
          <select name="province" id="province" class="form-select">
            <option value="">-- Select Province --</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="city" class="form-label">City / Municipality <span style="color: red;">*</span></label>
          <select name="city" id="city" class="form-select">
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
          <label for="landline-number" class="form-label">Telephone No.</label>
          <input type="text" class="form-control" name="landline_number" id="landline-number">
        </div>
        <div class="col-md-4">
          <label for="contact-number" class="form-label">Mobile No.</label>
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
          <label for="business-type-search" class="form-label">Business Type <span style="color: red;">*</span></label>
          <div class="business-type-dropdown-container">
            <input type="text" id="business-type-search" class="form-control" placeholder="Search or Select Business Type..." onfocus="showBusinessTypeDropdown(this)" oninput="filterBusinessTypes(this)" onblur="hideBusinessTypeDropdown(this)">
            <input type="hidden" name="business_type" id="business-type">
            <div class="business-type-list" id="business-type-list">
              <!-- Dynamically populated from categoryMap -->
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <label for="product-category" class="form-label">Category <span style="color: red;">*</span></label>
          <div class="category-dropdown-container">
            <input type="text" id="category-search" class="form-control" placeholder="Search or type category..." onfocus="showCategoryDropdown(this)" oninput="filterCategories(this)" onblur="hideCategoryDropdown(this)" autocomplete="off">
            <input type="hidden" name="category" id="product-category" required>
            <div id="category-list" class="category-list">
              <!-- Dynamically populated -->
            </div>
          </div>
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

<style>
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
    max-height: 250px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 0 0 8px 8px;
    z-index: 1050;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: none;
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
</style>

<script>
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
</script>