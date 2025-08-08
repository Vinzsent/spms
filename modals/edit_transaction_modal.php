<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1"
     aria-labelledby="editTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="../actions/update_transaction.php">
      <input type="hidden" name="transaction_id" id="editTransactionId">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-white" id="editTransactionModalLabel">Edit Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-md-4">
            <label>Date Received</label>
            <input type="date" name="date_received" class="form-control" id="editDateReceived">
          </div>

          <!-- Invoice -->
          <div class="col-md-6">
            <label class="form-label">Invoice No.</label>
            <input type="text" class="form-control" name="invoice_no" id="editInvoiceNo" required>
          </div>

          <!-- Sales Type -->
          <div class="col-md-6">
            <label class="form-label">Sales Type</label>
            <select class="form-select" name="sales_type" id="editSalesType" required>
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
            <label class="form-label">Item Description</label>
            <textarea name="item_description" class="form-control" rows="3" id="editDescription" required></textarea>
          </div>

          <!-- Additional Item Details -->
        <div class="col-md-4">
          <label>Brand</label>
          <input type="text" name="brand" class="form-control" id="editBrand" placeholder="Enter brand name">
        </div>
        <div class="col-md-4">
          <label>Type</label>
          <input type="text" name="type" class="form-control" id="editType" placeholder="Enter item type">
        </div>
        <div class="col-md-4">
          <label>Color</label>
          <input type="text" name="color" class="form-control" id="editColor" placeholder="Enter color">
        </div>

          <!-- Quantity / Unit / Price / Total -->
          <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" id="editQuantity" required>
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
            <label class="form-label">Unit Price</label>
            <input type="number" step="0.01" class="form-control" name="unit_price" id="editPrice" required>
          </div>
          <div class="col-md-3">
            <label>Total</label>
            <input type="text" id="editTotalAmount" name="amount" class="form-control" readonly>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.querySelector('#editTransactionModal #editQuantity');
    const unitPriceInput = document.querySelector('#editTransactionModal #editPrice');
    const totalAmountInput = document.getElementById('editTotalAmount');
    const modal = document.getElementById('editTransactionModal');

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

<script src="../assets/js/total-amount.js"></script>
