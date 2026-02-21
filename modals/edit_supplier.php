<!-- Hidden field for supplier ID -->
<form action="../actions/edit_supplier.php" method="post">
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
        <div class="col-md-12">
          <label for="edit-supplier-name" class="form-label">Supplier Name</label>
          <input type="text" class="form-control" name="supplier_name" id="edit-supplier-name" required>
        </div>
      </div>

      <div class="row g-2">
        <div class="col-md-8">
          <label for="edit-address" class="form-label">Full Address</label>
          <input type="text" class="form-control" name="address" id="edit-address">
        </div>
        <div class="col-md-4">
          <label for="edit-province" class="form-label">Province</label>
          <select name="province" id="edit-province" class="form-select">
            <option value="">-- Select Province --</option>
          </select>
        </div>
        <div class="col-md-4">
          <label for="edit-city" class="form-label">City / Municipality</label>
          <select name="city" id="edit-city" class="form-select">
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
        <div class="col-md-12">
          <label for="edit-contact-person" class="form-label">Contact Person</label>
          <input type="text" class="form-control" name="contact_person" id="edit-contact-person">
        </div>
        <div class="col-md-6">
          <label for="edit-contact-number" class="form-label">Mobile No.</label>
          <input type="text" class="form-control" name="contact_number" id="edit-contact-number">
        </div>
        <div class="col-md-6">
          <label for="edit-landline-number" class="form-label">Telephone No.</label>
          <input type="text" class="form-control" name="landline_number" id="edit-landline-number">
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
          <label for="edit-business-type-search" class="form-label">Business Type</label>
          <div class="business-type-dropdown-container">
            <input type="text" id="edit-business-type-search" class="form-control" placeholder="Search or Select Business Type..." onfocus="showEditBusinessTypeDropdown(this)" oninput="filterEditBusinessTypes(this)" onblur="hideEditBusinessTypeDropdown(this)">
            <input type="hidden" name="business_type" id="edit-business-type">
            <div class="business-type-list" id="edit-business-type-list">
              <!-- Dynamically populated from categoryMap -->
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <label for="edit-category" class="form-label">Category</label>
          <div class="category-dropdown-container">
            <input type="text" id="edit-category-search" class="form-control" placeholder="Search or type category..." onfocus="showEditCategoryDropdown(this)" oninput="filterEditCategories(this)" onblur="hideEditCategoryDropdown(this)" autocomplete="off">
            <input type="hidden" name="category" id="edit-category">
            <div id="edit-category-list" class="category-list">
              <!-- Dynamically populated -->
            </div>
          </div>
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
</form>

<style>
  /* Use common classes for both add and edit modas if possible, but keep scoped to avoid conflict */
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

  #edit-business-type-search:focus,
  #edit-category-search:focus {
    border-color: #073b1d;
    box-shadow: 0 0 0 0.25rem rgba(7, 59, 29, 0.25);
  }
</style>

<script>
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
</script>