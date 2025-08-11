<?php
$suppliers = $conn->query("SELECT supplier_id, supplier_name FROM supplier ORDER BY supplier_name ASC");
?>

<!-- Modal -->
<div class="modal fade" id="addSupplyModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" action="../actions/add_supply_request.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">New Supply Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <!-- User Information Section -->
        <div class="col-md-12 mb-3">
          <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-user-circle fa-2x me-3 text-primary"></i>
            <div>
              <strong class="d-block">Requesting User</strong>
              <span class="text-muted"><?= htmlspecialchars($user_name) ?></span>
              <small class="badge bg-secondary ms-2">ID: <?= htmlspecialchars($user_id) ?></small>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <label>Date Requested <span class="text-danger">*</span></label>
          <input type="date" name="date_requested" class="form-control" required>
          <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
        </div>

        <div class="col-md-4">
          <label>Date Needed <span class="text-danger">*</span></label>
          <input type="date" name="date_needed" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label>Position/Role <span class="text-danger">*</span></label>
          <input type="text" name="department_unit" class="form-control" value="<?= htmlspecialchars($user_type) ?>" readonly>
        </div>
        
        <div class="col-md-4">
          <label>Quantity Requested <span class="text-danger">*</span></label>
          <input type="number" name="quantity_requested" class="form-control" required>
        </div>

        
        <div class="col-md-4">
          <label>Purpose of the Request <span class="text-danger">*</span></label>
          <textarea name="purpose" class="form-control row-3" required></textarea>
        </div>
        
        <div class="col-md-4">
          <label>Sales Type <span class="text-danger">*</span></label>
          <select name="sales_type" class="form-select" required>
            <option value="">-- Select Purchased Type --</option>
            <option value="Cash">Cash Purchased</option>
            <option value="Credit">Credit Purchased</option>
          </select>
        </div>
        
        <div class="col-md-6">
          <label>Category <span class="text-danger">*</span></label>
          <select name="category" class="form-select" required>
            <option value="">-- Select Category --</option>
            <optgroup label="Capital Outlay (CO)">
              <option>ICT Equipment and Devices</option>
              <option>Office Equipment</option>
              <option>Air Conditioning Units and Cooling Systems</option>
              <option>Furniture and Fixtures</option>
              <option>Laboratory Equipment</option>
              <option>School Building Improvements</option>
              <option>Other Machinery and Equipment</option>
            </optgroup>
            <optgroup label="Maintenance and Other Operating Expenses (MOOE)">
              <option>Office Supplies and Materials</option>
              <option>Instructional and Learning Materials</option>
              <option>Janitorial and Sanitation Supplies</option>
              <option>Repairs and Maintenance – Buildings and Facilities</option>
              <option>Repairs and Maintenance – Equipment and Devices</option>
              <option>Electrical and Lighting Supplies</option>
              <option>Medical and First Aid Supplies</option>
              <option>Printing and Reproduction Services</option>
              <option>Subscription, License, and Software Services</option>
              <option>Utilities and Facility Services</option>
              <option>Transportation or Delivery Services</option>
            </optgroup>
          </select>
        </div>
        <div class="col-md-12">
          <label>Request Description <span class="text-danger">*</span></label>
          <textarea name="request_description" class="form-control" rows="3" required></textarea>
        </div>
        
        <div class="col-md-3">
          <label>Quality Issued</label>
          <input type="text" name="quality_issued" class="form-control">
          <p style="font-size: 12px;">Leave this field blank if you don't know the quality of the item</p>
        </div>
        
        <div class="col-md-3">
          <label>Unit <span class="text-danger">*</span></label>
          <select name="unit" class="form-select" required>
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
          <label>Unit Cost</label>
          <input type="number" name="unit_cost" step="0.01" class="form-control">
        </div>
        <div class="col-md-3">
          <label>Total Cost</label>
          <input type="text" id="addTotalAmount" class="form-control" readonly>
          <input type="hidden" name="total_cost" id="hiddenTotalCost">
          <input type="hidden" name="amount" id="hiddenAmount">
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Submit Request</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.querySelector('#addSupplyModal [name="quantity_requested"]');
    const unitPriceInput = document.querySelector('#addSupplyModal [name="unit_cost"]');
    const totalAmountInput = document.getElementById('addTotalAmount');
    const hiddenTotalCost = document.getElementById('hiddenTotalCost');
    const hiddenAmount = document.getElementById('hiddenAmount');
    const modal = document.getElementById('addSupplyModal');
    const form = modal.querySelector('form');

    function updateTotal() {
      const quantity = parseFloat(quantityInput.value) || 0;
      const unitPrice = parseFloat(unitPriceInput.value) || 0;
      const total = quantity * unitPrice;
      totalAmountInput.value = total.toFixed(2);
      if (hiddenTotalCost) hiddenTotalCost.value = total.toFixed(2);
      if (hiddenAmount) hiddenAmount.value = total.toFixed(2); // Set amount same as total_cost for now
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
      });
    }
  });
</script>