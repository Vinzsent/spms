<?php
$suppliers = $conn->query("SELECT supplier_id, supplier_name FROM supplier ORDER BY supplier_name ASC");
?>

<!-- Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" action="../actions/save_transaction.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title text-white">New Transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-4">
          <label>Date Requested <span class="text-danger">*</span> </label>
          <input type="hidden" name="status" value="Pending">
          <input type="date" name="date_received" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label>Invoice No. <span class="text-danger">*</span></label>
          <input type="text" name="invoice_no" class="form-control" required>
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
          <label>Supplier Name <span class="text-danger">*</span></label>
          <select name="supplier_id" class="form-select" required>
            <option value="">-- Select Supplier --</option>
            <?php while($row = $suppliers->fetch_assoc()): ?>
              <option value="<?= $row['supplier_id'] ?>"><?= htmlspecialchars(strtoupper($row['supplier_name'])) ?></option>
            <?php endwhile; ?>
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
          <label>Description <span class="text-danger">*</span></label>
          <textarea name="item_description" class="form-control" rows="3" required></textarea>
        </div>

        <!-- Additional Item Details -->
        <div class="col-md-4">
          <label>Brand <span class="text-danger">*</span></label>
          <input type="text" name="brand" class="form-control" placeholder="Enter brand name">
        </div>
        <div class="col-md-4">
          <label>Type <span class="text-danger">*</span></label>
          <input type="text" name="type" class="form-control" placeholder="Enter item type">
        </div>
        <div class="col-md-4">
          <label>Color <span class="text-danger">*</span></label>
          <input type="text" name="color" class="form-control" placeholder="Enter color">
        </div>

        <div class="col-md-3">
          <label>Quantity Issued <span class="text-danger">*</span></label>
          <input type="number" name="quantity" class="form-control" required>
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
          <label>Unit Cost <span class="text-danger">*</span></label>
          <input type="number" name="unit_price" step="0.01" class="form-control" required>
        </div>
		<div class="col-md-3">
  <label>Total Cost <span class="text-danger">*</span></label>
  <input type="text" id="addTotalAmount" class="form-control" readonly>
</div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save Request</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.querySelector('#addTransactionModal [name="quantity"]');
    const unitPriceInput = document.querySelector('#addTransactionModal [name="unit_price"]');
    const totalAmountInput = document.getElementById('addTotalAmount');
    const modal = document.getElementById('addTransactionModal');

    function updateTotal() {
      const quantity = parseFloat(quantityInput.value) || 0;
      const unitPrice = parseFloat(unitPriceInput.value) || 0;
      const total = quantity * unitPrice;
      totalAmountInput.value = total.toFixed(2);
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
  });
</script>
