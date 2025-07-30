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
            <label class="form-label">Department Unit</label>
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
            <input type="number" step="0.01" class="form-control" name="unit_cost" id="editPrice" required>
          </div>
          <div class="col-md-3">
            <label>Total Cost</label>
            <input type="text" id="editTotalAmount" name="total_cost" class="form-control" readonly>
            <input type="hidden" name="amount" id="hiddenAmount">
          </div>
        </div>

        <div class="modal-footer">
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
      quantityInput.addEventListener('input', updateTotal);
      unitPriceInput.addEventListener('input', updateTotal);
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