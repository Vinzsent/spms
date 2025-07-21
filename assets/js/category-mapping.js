// category-mapping.js

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
  // Find all business type dropdowns on the page (add + edit)
  document.querySelectorAll('select[name="business_type"]').forEach((businessTypeSelect) => {
    businessTypeSelect.addEventListener('change', () => {
      const modal = businessTypeSelect.closest('.modal'); // scope to modal
      const categorySelect = modal.querySelector('select[name="category"]');
      const selectedType = businessTypeSelect.value;
      const categories = categoryMap[selectedType] || [];

      if (categorySelect) {
        categorySelect.innerHTML = '<option value="">-- Select Category --</option>';
        categories.forEach(category => {
          const option = document.createElement('option');
          option.value = category;
          option.textContent = category;
          categorySelect.appendChild(option);
        });
      }
    });
  });
});
