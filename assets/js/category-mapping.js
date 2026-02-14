// Category mapping for both add and edit supplier modals
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

// Function to populate category options based on business type
function populateCategoryOptions(businessType, categorySelectId = 'product-category', selectedCategory = '') {
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
    if (category === selectedCategory) option.selected = true;
    categorySelect.appendChild(option);
  });
}

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', () => {
  // Handle Add Supplier Modal
  const addBusinessTypeSelect = document.getElementById('business-type');
  const addCategorySelect = document.getElementById('product-category');
  const addModal = document.getElementById('addModal');
  
  if (addBusinessTypeSelect && addCategorySelect) {
    // Add change event listener for Add modal
    addBusinessTypeSelect.addEventListener('change', function() {
      populateCategoryOptions(this.value, 'product-category');
    });
    
    // Handle when Add modal is shown
    if (addModal) {
      addModal.addEventListener('show.bs.modal', function() {
        if (addBusinessTypeSelect.value) {
          populateCategoryOptions(addBusinessTypeSelect.value, 'product-category');
        } else {
          populateCategoryOptions('', 'product-category');
        }
      });
    }
  }

  // Handle Edit Supplier Modal
  const editBusinessTypeSelect = document.getElementById('edit-business-type');
  const editCategorySelect = document.getElementById('edit-category');
  const editModal = document.getElementById('editModal');
  
  if (editBusinessTypeSelect && editCategorySelect) {
    // Add change event listener for Edit modal
    editBusinessTypeSelect.addEventListener('change', function() {
      populateCategoryOptions(this.value, 'edit-category');
    });
    
    // Handle when Edit modal is shown
    if (editModal) {
      editModal.addEventListener('show.bs.modal', function(event) {
        // Get the button that triggered the modal
        const button = event.relatedTarget;
        if (!button) return;
        
        // Get the current business type and category from data attributes
        const businessType = button.getAttribute('data-business-type') || '';
        const category = button.getAttribute('data-category') || '';
        
        // Set the business type value
        editBusinessTypeSelect.value = businessType;
        
        // Populate and select the category
        populateCategoryOptions(businessType, 'edit-category', category);
      });
    }
  }
});
