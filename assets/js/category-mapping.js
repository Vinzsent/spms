// category-mapping.js

// Function to populate category options based on business type
function populateCategoryOptions(businessType, categorySelectId) {
  const categorySelect = document.getElementById(categorySelectId);
  if (!categorySelect) return;

  // Clear existing options
  categorySelect.innerHTML = '<option value="">-- Select Category --</option>';

  // Get categories for the selected business type
  const categories = categoryMap[businessType] || [];
  
  // Add new options
  categories.forEach(category => {
    const option = document.createElement('option');
    option.value = category;
    option.textContent = category;
    categorySelect.appendChild(option);
  });
}

// Category mapping object
const categoryMap = {
  "IT Equipment Supplier": [
    "ICT Equipment and Devices",
    "Subscription, License, and Software Services"
  ],
  "Office Equipment Vendor": [
    "Office Equipment",
    "Office Supplies and Materials"
  ],
  "Air Conditioning Equipment Supplier": [
    "Air Conditioning Units and Cooling Systems"
  ],
  "Equipment Maintenance Provider": [
    "Repairs and Maintenance – Equipment and Devices"
  ],
  "Furniture Supplier": [
    "Furniture and Fixtures"
  ],
  "Laboratory Equipment Supplier": [
    "Lab Equipment",
    "Lab Chemicals and Reagents"
  ],
  "Construction and Renovation Contractor": [
    "Construction Materials",
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

document.addEventListener('DOMContentLoaded', () => {
  // Function to initialize business type change handlers for a specific form
  function initBusinessTypeHandlers(prefix = '') {
    const businessTypeId = prefix ? `${prefix}-business-type` : 'business-type';
    const categoryId = prefix === 'edit' ? 'edit-product-category' : 'productcategory';
    
    const businessTypeSelect = document.getElementById(businessTypeId);
    const categorySelect = document.getElementById(categoryId);

    if (businessTypeSelect && categorySelect) {
      businessTypeSelect.addEventListener('change', function() {
        populateCategoryOptions(this.value, categoryId);
      });
      
      // Trigger change event if business type is already selected
      if (businessTypeSelect.value) {
        businessTypeSelect.dispatchEvent(new Event('change'));
      }
    }
  }

  // Initialize handlers for add modal
  initBusinessTypeHandlers('add');
  
  // Initialize handlers for edit modal
  initBusinessTypeHandlers('edit');
});
