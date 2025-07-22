<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1"
     aria-labelledby="editTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="../actions/update_transaction.php">
      <input type="hidden" name="transaction_id" id="editTransactionId">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-md-4">
          <label>Date Received</label>
          <input type="date" name="date_received"  class="form-control" id="editDateRecieved">
        </div>
		  <!-- Invoice -->
          <div class="col-md-6">
            <label class="form-label">Invoice No.</label>
            <input type="text" class="form-control" name="invoice_no" id="editInvoiceNo" required>
          </div>

          <!-- Sales Type -->
          <div class="col-md-6">
            <label class="form-label">Sales Type</label>
            <select class="form-select" name="sales_type" id="editSalesType" required>
              <option value="Cash Purchased">Cash Purchased</option>
              <option value="Credit Purchased">Credit Purchased</option>
            </select>
          </div>

          <!-- Category (NOW A DROPDOWN) -->
          <div class="col-md-12">
            <label class="form-label">Category</label>
            <select class="form-select" name="category" id="editCategory" required>
              <option value="">-- Select Category --</option>

              <!-- Capital Outlay -->
              <optgroup label="Capital Outlay (CO)">
                <option value="ICT Equipment and Devices">ICT Equipment and Devices</option>
                <option value="Office Equipment">Office Equipment</option>
                <option value="Air Conditioning Units and Cooling Systems">Air Conditioning Units and Cooling Systems</option>
                <option value="Furniture and Fixtures">Furniture and Fixtures</option>
                <option value="Laboratory Equipment">Laboratory Equipment</option>
                <option value="School Building Improvements">School Building Improvements</option>
                <option value="Other Machinery and Equipment">Other Machinery and Equipment</option>
              </optgroup>

              <!-- MOOE -->
              <optgroup label="Maintenance and Other Operating Expenses (MOOE)">
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
            <label class="form-label">Item Description</label>
            <textarea name="item_description" class="form-control" rows="3" id="editDescription" required></textarea>
          </div>

          <!-- Quantity / Unit / Price -->
          <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" id="editQuantity" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Unit</label>
            <input type="text" class="form-control" name="unit" id="editUnit" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Unit Price</label>
            <input type="number" step="0.01" class="form-control" name="unit_price" id="editPrice" required>
          </div>
		  <div class="col-md-3">
  <label>Total</label>
  <input type="text" id="totalAmount" class="form-control" readonly>
</div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>