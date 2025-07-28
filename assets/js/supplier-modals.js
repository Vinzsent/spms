// File: ../assets/js/supplier-modals.js

// This script handles both Add and Edit Supplier modal initialization and data binding

document.addEventListener('DOMContentLoaded', function () {
  const editModal = document.getElementById('editModal');

  const fieldMap = {
    'supplier-id': 'edit-supplier-id',
    'supplier-name': 'edit-supplier-name',
    'contact-person': 'edit-contact-person',
    'contact-number': 'edit-contact-number',
    'email-address': 'edit-email-address',
    'fax-number': 'edit-fax-number',
    'website': 'edit-website',
    'address': 'edit-address',
    'city': 'edit-city',
    'province': 'edit-province',
    'zip-code': 'edit-zip-code',
    'country': 'edit-country',
    'business-type': 'edit-business-type',
    'product-category': 'edit-product-category',
    'payment-terms': 'edit-payment-terms',
    'tax-identification-number': 'edit-tin',
    'date-registered': 'edit-date-registered',
    'status': 'edit-status',
    'notes': 'edit-notes'
  };

  let phData = {};

  async function loadPhilippineData() {
    const res = await fetch("../assets/data/ph_province_city_list.json");
    const data = await res.json();
    phData = data.Philippines;
  }

  function populateCountry(selectElement, selected = "Philippines") {
    const countries = ["Philippines"];
    selectElement.innerHTML = '<option value="">-- Select Country --</option>';
    countries.forEach(country => {
      const opt = new Option(country, country);
      selectElement.appendChild(opt);
    });
    selectElement.value = selected;
  }

  async function populateProvincesAndCities(provinceSelect, citySelect, selectedProvince = "", selectedCity = "") {
    if (!Object.keys(phData).length) await loadPhilippineData();

    provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
    Object.keys(phData).sort().forEach(province => {
      provinceSelect.appendChild(new Option(province, province));
    });
    provinceSelect.value = selectedProvince;

    citySelect.innerHTML = '<option value="">-- Select City --</option>';
    if (selectedProvince && phData[selectedProvince]) {
      phData[selectedProvince].forEach(city => {
        citySelect.appendChild(new Option(city, city));
      });
      citySelect.value = selectedCity;
    }
  }

  editModal.addEventListener('show.bs.modal', async function (event) {
    const button = event.relatedTarget;
    const provinceSelect = document.getElementById('edit-province');
    const citySelect = document.getElementById('edit-city');
    const countrySelect = document.getElementById('edit-country');

    const selectedProvince = button.getAttribute('data-province') || '';
    const selectedCity = button.getAttribute('data-city') || '';
    const selectedCountry = button.getAttribute('data-country') || 'Philippines';

    await populateProvincesAndCities(provinceSelect, citySelect, selectedProvince, selectedCity);
    populateCountry(countrySelect, selectedCountry);

    for (const [dataAttr, inputId] of Object.entries(fieldMap)) {
      if (["province", "city", "country"].includes(dataAttr)) continue;
      const input = document.getElementById(inputId);
      if (input) input.value = button.getAttribute(`data-${dataAttr}`) || '';
    }
  });

  editModal.addEventListener('hidden.bs.modal', () => {
    editModal.querySelectorAll('input, textarea, select').forEach(el => el.value = '');
    document.getElementById('edit-city').innerHTML = '<option value="">-- Select City --</option>';
  });

  const addProvince = document.getElementById('province');
  const addCity = document.getElementById('city');
  const addCountry = document.getElementById('country');

  if (addProvince && addCity && addCountry) {
    loadPhilippineData().then(() => {
      addProvince.innerHTML = '<option value="">-- Select Province --</option>';
      Object.keys(phData).sort().forEach(province => {
        addProvince.appendChild(new Option(province, province));
      });
      populateCountry(addCountry);
    });

    addProvince.addEventListener('change', function () {
      const selectedProvince = this.value;
      addCity.innerHTML = '<option value="">-- Select City --</option>';
      if (phData[selectedProvince]) {
        phData[selectedProvince].forEach(city => {
          addCity.appendChild(new Option(city, city));
        });
      }
    });
  }

  const businessTypeSelect = document.getElementById("edit-business-type");
  const categorySelect = document.getElementById("edit-product-category");

  if (businessTypeSelect && categorySelect) {
    businessTypeSelect.addEventListener("change", function () {
      populateCategoryOptions(this.value, "edit-product-category");
    });
  }

  // Delete Modal Functionality
  const deleteModal = document.getElementById('deleteModal');
  
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const supplierId = button.getAttribute('data-supplier-id');
      const supplierName = button.getAttribute('data-supplier-name');
      
      console.log('Delete modal opened for:', supplierName, 'ID:', supplierId);
      
      // Set the values in the delete form
      const idField = document.getElementById('delete-supplier-id');
      const nameField = document.getElementById('delete-supplier-name');
      const displayField = document.getElementById('delete-supplier-name-display');
      
      if (idField) idField.value = supplierId;
      if (nameField) nameField.value = supplierName;
      if (displayField) displayField.textContent = supplierName;
      
      console.log('Form values set:', {
        id: idField ? idField.value : 'field not found',
        name: nameField ? nameField.value : 'field not found',
        display: displayField ? displayField.textContent : 'field not found'
      });
    });
  }
});