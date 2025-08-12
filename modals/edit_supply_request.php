<!-- Edit Transaction Modal -->
<div class="modal fade" id="editSupplyModal" tabindex="-1"
  aria-labelledby="editSupplyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="../actions/edit_supply_request.php">
      <input type="hidden" name="request_id" id="editRequestId">

      <div class="modal-content">
                 <div class="modal-header">
           <h5 class="modal-title" id="editSupplyModalLabel">Edit Supply Request</h5>
           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>

        <div class="modal-body row g-3">
          <div class="col-md-4">
            <label>Date Requested</label>
            <input type="date" name="date_requested" class="form-control" id="editDateRequest" required>
          </div>

          <!-- Date Needed -->
          <div class="col-md-4">
            <label class="form-label">Date Needed</label>
            <input type="date" class="form-control" name="date_needed" id="editDateNeeded" required>
          </div>

          <!-- Department Unit -->
          <div class="col-md-4">
            <label class="form-label">Position/Role</label>
            <input type="text" class="form-control" name="department_unit" id="editDepartmentUnit" required>
          </div>

          <!-- Purpose -->
          <div class="col-md-6">
            <label class="form-label">Purpose</label>
            <textarea name="purpose" class="form-control" id="editPurpose" required></textarea>
          </div>

           <!-- Quality Issued -->
           <div class="col-md-6">
            <label class="form-label">Quality Issued</label>
            <input type="text" class="form-control" name="quality_issued" id="editQualityIssued" required>
          </div>

          <!-- Sales Type -->
          <div class="col-md-6">
            <label class="form-label">Sales Type</label>
            <select name="sales_type" class="form-select" id="editSalesType" required>
              <option value="">-- Select Purchased Type --</option>
              <option value="Cash">Cash Purchased</option>
              <option value="Credit">Credit Purchased</option>
            </select>
          </div>

          <!-- Category -->
          <div class="col-md-12">
            <label class="form-label">Category</label>
            <select class="form-select" name="category" id="editCategory" required>
              <option value="">-- Select Category --</option>

              <optgroup label="Capital Outlay (CO)">
                <option value="ICT Equipment and Devices">ICT Equipment and Devices</option>
                <option value="Office Equipment">Office Equipment</option>
                <option value="Air Conditioning Units and Cooling Systems">Air Conditioning Units and Cooling Systems</option>
                <option value="Furniture and Fixtures">Furniture and Fixtures</option>
                <option value="Laboratory Equipment">Laboratory Equipment</option>
                <option value="School Building Improvements">School Building Improvements</option>
                <option value="Other Machinery and Equipment">Other Machinery and Equipment</option>
              </optgroup>

              <optgroup label="Maintenance and Other Operating Expenses (MOOE)">
                <option value="Office Supplies and Materials">Office Supplies and Materials</option>
                <option value="Instructional and Learning Materials">Instructional and Learning Materials</option>
                <option value="Janitorial and Sanitation Supplies">Janitorial and Sanitation Supplies</option>
                <option value="Repairs and Maintenance – Buildings and Facilities">Repairs and Maintenance – Buildings and Facilities</option>
                <option value="Repairs and Maintenance – Equipment and Devices">Repairs and Maintenance – Equipment and Devices</option>
                <option value="Electrical and Lighting Supplies">Electrical and Lighting Supplies</option>
                <option value="Medical and First Aid Supplies">Medical and First Aid Supplies</option>
                <option value="Printing and Reproduction Services">Printing and Reproduction Services</option>
                <option value="Subscription, License, and Software Services">Subscription, License, and Software Services</option>
                <option value="Utilities and Facility Services">Utilities and Facility Services</option>
                <option value="Transportation or Delivery Services">Transportation or Delivery Services</option>
              </optgroup>
            </select>
          </div>

          <!-- Description -->
          <div class="col-md-12">
            <label class="form-label">Request Description</label>
            <textarea name="request_description" class="form-control" rows="3" id="editRequestDescription" required></textarea>
          </div>

          <!-- Quantity / Unit / Price / Total -->
          <div class="col-md-3">
            <label class="form-label">Quantity Requested</label>
            <input type="number" class="form-control" name="quantity_requested" id="editQuantity" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Unit</label>
            <select name="unit" class="form-select" id="editUnit" required>
              <option value="">-- Select Unit --</option>
              <!-- Common Units for Supplies -->
              <option value="pc">Piece (pc)</option>
              <option value="box">Box</option>
              <option value="pack">Pack</option>
              <option value="pad">Pad</option>
              <option value="ream">Ream</option>
              <option value="dozen">Dozen</option>

              <!-- Liquid and Cleaning -->
              <option value="bottle">Bottle</option>
              <option value="gallon">Gallon</option>
              <option value="liter">Liter (L)</option>
              <option value="ml">Milliliter (ml)</option>
              <option value="roll">Roll</option>
              <option value="bar">Bar</option>

              <!-- Measurement -->
              <option value="meter">Meter</option>
              <option value="cm">Centimeter (cm)</option>
              <option value="ft">Foot (ft)</option>
              <option value="kg">Kilogram (kg)</option>
              <option value="g">Gram (g)</option>
              <option value="ton">Ton</option>
              <option value="tube">Tube</option>
              <option value="can">Can</option>

              <!-- Laboratory / Medical -->
              <option value="vial">Vial</option>
              <option value="sachet">Sachet</option>

              <!-- Equipment -->
              <option value="unit">Unit</option>
              <option value="set">Set</option>
              <option value="kit">Kit</option>
              <option value="pair">Pair</option>
              <option value="lot">Lot</option>
              <option value="package">Package</option>

              <!-- Services -->
              <option value="trip">Trip</option>
              <option value="hour">Hour</option>
              <option value="day">Day</option>
              <option value="service">Service</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Unit Cost</label>
            <input type="text" class="form-control" name="unit_cost" id="editPrice" pattern="[0-9]*\.?[0-9]*" required>
          </div>
          <div class="col-md-3">
            <label>Total Cost</label>
            <input type="text" id="editTotalAmount" name="total_cost" class="form-control" readonly>
            <input type="hidden" name="amount" id="hiddenAmount">
          </div>
        </div>

                 <div class="modal-footer">
           <button type="button" class="btn btn-outline-secondary me-2" id="printSupplyRequest" title="Print Supply Request">
             <i class="fas fa-print me-1"></i>Print
           </button>
           <button type="submit" class="btn btn-primary">Update Request</button>
           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
         </div>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.querySelector('#editSupplyModal #editQuantity');
    const unitPriceInput = document.querySelector('#editSupplyModal #editPrice');
    const totalAmountInput = document.getElementById('editTotalAmount');
    const hiddenAmount = document.getElementById('hiddenAmount');
    const modal = document.getElementById('editSupplyModal');
    const form = modal.querySelector('form');

    function updateTotal() {
      const quantity = parseFloat(quantityInput.value) || 0;
      const unitPrice = parseFloat(unitPriceInput.value) || 0;
      const total = quantity * unitPrice;
      totalAmountInput.value = total.toFixed(2);
      if (hiddenAmount) hiddenAmount.value = total.toFixed(2); // Set amount same as total_cost
    }

    // Update total when quantity or price changes
    if (quantityInput && unitPriceInput && totalAmountInput) {
      // Add input validation for quantity field to allow only numbers
      quantityInput.addEventListener('input', function(e) {
        // Remove any non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        updateTotal();
      });
      
      quantityInput.addEventListener('keypress', function(e) {
        // Allow only numeric keys, backspace, delete, tab, escape, enter
        if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Escape', 'Enter'].includes(e.key)) {
          e.preventDefault();
        }
      });
      
      // Add input validation for unit cost field to allow numbers and decimals
      unitPriceInput.addEventListener('input', function(e) {
        // Remove any non-numeric characters except for decimal point
        this.value = this.value.replace(/[^0-9.]/g, '');
        // Ensure only one decimal point
        const parts = this.value.split('.');
        if (parts.length > 2) {
          this.value = parts[0] + '.' + parts.slice(1).join('');
        }
        updateTotal();
      });
      
      unitPriceInput.addEventListener('keypress', function(e) {
        // Allow only numeric keys, decimal point, backspace, delete, tab, escape, enter
        if (!/[0-9.]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Escape', 'Enter'].includes(e.key)) {
          e.preventDefault();
        }
        // Prevent multiple decimal points
        if (e.key === '.' && this.value.includes('.')) {
          e.preventDefault();
        }
      });
    }

    // Initialize total when modal is shown
    if (modal) {
      modal.addEventListener('shown.bs.modal', function() {
        updateTotal();
      });
    }

    // Ensure hidden fields are set before submit
    if (form) {
      form.addEventListener('submit', function() {
        updateTotal();
        // Ensure amount field is set
        const totalAmount = parseFloat(totalAmountInput.value) || 0;
        if (hiddenAmount) {
          hiddenAmount.value = totalAmount.toFixed(2);
        }
      });
    }
  });
</script>

<script src="../assets/js/total-amount.js"></script>

<script>
  // Print functionality for supply request
  document.addEventListener('DOMContentLoaded', function() {
    const printButton = document.getElementById('printSupplyRequest');
    
    if (printButton) {
      printButton.addEventListener('click', function() {
        // Get all the form data
        const requestId = document.getElementById('editRequestId').value;
        const dateRequested = document.getElementById('editDateRequest').value;
        const dateNeeded = document.getElementById('editDateNeeded').value;
        const departmentUnit = document.getElementById('editDepartmentUnit').value;
        const purpose = document.getElementById('editPurpose').value;
        const qualityIssued = document.getElementById('editQualityIssued').value;
        const salesType = document.getElementById('editSalesType').value;
        const category = document.getElementById('editCategory').value;
        const requestDescription = document.getElementById('editRequestDescription').value;
        const quantity = document.getElementById('editQuantity').value;
        const unit = document.getElementById('editUnit').value;
        const unitCost = document.getElementById('editPrice').value;
        const totalCost = document.getElementById('editTotalAmount').value;
        
                 // Create print window content
         const printContent = `
           <!DOCTYPE html>
           <html>
           <head>
             <title>Supply Requisition/Issuance - ${requestId}</title>
                           <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 10px; }
                .form-container { max-width: 800px; margin: 0 auto; border: 2px solid #000; padding: 15px; }
                .header { text-align: center; margin-bottom: 15px; }
                .logo-container { margin-bottom: 8px; }
                .school-logo { width: 60px; height: 60px; margin: 0 auto 5px; display: block; }
                .school-name { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
                .school-address { font-size: 10px; margin-bottom: 3px; }
                .school-contact { font-size: 10px; margin-bottom: 5px; }
                .accreditation { font-size: 9px; font-style: italic; margin-bottom: 8px; }
                .form-title { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 5px; }
                .form-info { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 10px; }
                .request-section { margin-bottom: 15px; }
                .request-row { display: flex; margin-bottom: 8px; }
                .request-label { width: 120px; font-weight: bold; font-size: 10px; }
                .request-value { flex: 1; border-bottom: 1px solid #000; padding-left: 8px; font-size: 10px; min-height: 15px; }
                .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .items-table th, .items-table td { border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px; }
                .items-table th { background-color: #f0f0f0; font-weight: bold; }
                .approval-section { display: flex; justify-content: space-between; }
                .approval-left, .approval-right { width: 48%; }
                .approval-item { margin-bottom: 12px; }
                .approval-label { font-weight: bold; font-size: 10px; margin-bottom: 3px; }
                .approval-line { border-bottom: 1px solid #000; height: 20px; margin-bottom: 3px; }
                .approval-subtitle { font-size: 8px; color: #666; }
                .phone-number { font-size: 8px; color: #666; margin-top: 3px; }
                @media print { 
                  body { margin: 0; padding: 5px; }
                  .form-container { border: none; }
                  @page { size: A4; margin: 10mm; }
                }
              </style>
           </head>
           <body>
                           <div class="form-container">
                <div class="header">
                  <div class="logo-container">
                    <img src="../assets/images/logo.png" alt="School Logo" class="school-logo">
                  </div>
                  <div class="school-name">DAVAO CENTRAL COLLEGE</div>
                  <div class="school-address">Juan dela Cruz St. Toril Davao City, Philippines</div>
                  <div class="school-contact">Tel. No. (082) 291-1882 / Fax No. (082) 291-2053</div>
                  <div class="accreditation">ACSCU-ACI ACCREDITED</div>
                  <div class="form-info">
                    <span>SO</span>
                    <span>Status: Rev. 3</span>
                    <span>Date Revised: February 2023</span>
                  </div>
                  <div class="form-title">Supply Requisition/Issuance</div>
                </div>
               
               <div class="request-section">
                 <div class="request-row">
                   <div class="request-label">Date Requested:</div>
                   <div class="request-value">${dateRequested}</div>
                   <div class="request-label" style="width: 100px; margin-left: 20px;">Date Needed:</div>
                   <div class="request-value">${dateNeeded}</div>
                 </div>
                 <div class="request-row">
                   <div class="request-label">Department/Unit:</div>
                   <div class="request-value">${departmentUnit}</div>
                 </div>
                 <div class="request-row">
                   <div class="request-label">Purpose:</div>
                   <div class="request-value">${purpose}</div>
                 </div>
               </div>
               
               <table class="items-table">
                 <thead>
                   <tr>
                     <th>Quantity Requested</th>
                     <th>Unit</th>
                     <th>Description</th>
                     <th>Quality Issued</th>
                     <th>Unit Cost</th>
                     <th>Total Cost</th>
                   </tr>
                 </thead>
                 <tbody>
                   <tr>
                     <td>${quantity}</td>
                     <td>${unit}</td>
                     <td>${requestDescription}</td>
                     <td>${qualityIssued}</td>
                     <td>₱${parseFloat(unitCost).toLocaleString()}</td>
                     <td>₱${parseFloat(totalCost).toLocaleString()}</td>
                   </tr>
                 </tbody>
               </table>
               
               <div class="approval-section">
                 <div class="approval-left">
                   <div class="approval-item">
                     <div class="approval-label">Requested By:</div>
                     <div class="approval-line"></div>
                   </div>
                   <div class="approval-item">
                     <div class="approval-label">Noted By:</div>
                     <div class="approval-line"></div>
                     <div class="approval-subtitle">Immediate Head</div>
                   </div>
                   <div class="approval-item">
                     <div class="approval-label">Checked By:</div>
                     <div class="approval-line"></div>
                     <div class="approval-subtitle">Supply In-Charge</div>
                   </div>
                   <div class="approval-item">
                     <div class="approval-label">Verified By:</div>
                     <div class="approval-line"></div>
                     <div class="approval-subtitle">Administrative Officer</div>
                   </div>
                   <div class="approval-item">
                     <div class="approval-label">Approved By:</div>
                     <div class="approval-line"></div>
                     <div class="approval-subtitle">VP for Finance & Administration</div>
                   </div>
                 </div>
                 
                 <div class="approval-right">
                   <div class="approval-item">
                     <div class="approval-label">Issued By:</div>
                     <div class="approval-line"></div>
                     <div class="approval-subtitle">Supply In-charge</div>
                   </div>
                   <div class="approval-item">
                     <div class="approval-label">Received By:</div>
                     <div class="approval-line"></div>
                   </div>
                   <div class="approval-item">
                     <div class="approval-label">Date:</div>
                     <div class="approval-line"></div>
                   </div>
                 </div>
               </div>
             </div>
           </body>
           </html>
         `;
        
        // Open print window
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        
        // Wait for content to load then print
        printWindow.onload = function() {
          printWindow.print();
        };
      });
    }
  });
</script>