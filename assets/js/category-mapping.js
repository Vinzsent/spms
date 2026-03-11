// Category mapping for both add and edit supplier modals - initialized empty and populated from DB
let categoryMap = {};

// Function to fetch full business type and category mapping from DB
function fetchBusinessTypes() {
  const scriptsDir = window.location.pathname.includes('/pages/') ? '../actions/' : 'actions/';
  return fetch(scriptsDir + 'get_full_category_mapping.php')
    .then(response => response.json())
    .then(data => {
      if (data && typeof data === 'object' && !data.error) {
        categoryMap = data;
      }
      // Dispatch event to notify modals that data is loaded
      document.dispatchEvent(new CustomEvent('businessTypesLoaded'));
      return categoryMap;
    })
    .catch(error => {
      console.error('Error fetching category mapping:', error);
      return categoryMap;
    });
}

function populateCategoryOptions(businessType, categorySelectId = 'product-category', selectedCategory = '') {
  const categorySelect = document.getElementById(categorySelectId);
  if (!categorySelect) return;

  // Clear existing options
  categorySelect.innerHTML = '<option value="">-- Select Category --</option><option value="N/A">-- N/A --</option>';

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
  // Initial fetch from DB to populate any new types
  fetchBusinessTypes();
  
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
