<?php
// Get specifications for a specific transaction
function getSpecifications($conn, $transaction_id) {
    $sql = "SELECT * FROM transaction_specifications WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>

<!-- View/Edit Specifications Modal -->
<div class="modal fade" id="specificationsModal" tabindex="-1" aria-labelledby="specificationsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="specificationsModalLabel">
          <i class="fas fa-cogs me-2"></i>Item Specifications
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form id="specificationsForm" action="../actions/save_specifications.php" method="POST">
        <div class="modal-body">
          <!-- Transaction Info Display -->
          <div class="alert alert-info mb-3">
            <div class="row">
              <div class="col-md-6">
                <strong>Transaction:</strong> <span id="specTransactionInfo"></span><br>
                <strong>Item:</strong> <span id="specItemDescription"></span>
              </div>
              <div class="col-md-6">
                <strong>Supplier:</strong> <span id="specSupplier"></span><br>
                <strong>Category:</strong> <span id="specCategory"></span>
              </div>
            </div>
          </div>

          <input type="hidden" name="transaction_id" id="specTransactionId">
          
          <div class="row g-3">
            <!-- Brand -->
            <div class="col-md-6">
              <label for="specBrand" class="form-label">
                <i class="fas fa-tag me-1"></i>Brand
              </label>
              <input type="text" class="form-control" id="specBrand" name="brand" placeholder="Enter brand name">
            </div>

            <!-- Serial Number -->
            <div class="col-md-6">
              <label for="specSerialNumber" class="form-label">
                <i class="fas fa-barcode me-1"></i>Serial Number
              </label>
              <input type="text" class="form-control" id="specSerialNumber" name="serial_number" placeholder="Enter serial number">
            </div>

            <!-- Type -->
            <div class="col-md-6">
              <label for="specType" class="form-label">
                <i class="fas fa-cube me-1"></i>Type
              </label>
              <input type="text" class="form-control" id="specType" name="type" placeholder="Enter type/model">
            </div>

            <!-- Size -->
            <div class="col-md-6">
              <label for="specSize" class="form-label">
                <i class="fas fa-ruler me-1"></i>Size/Dimensions
              </label>
              <input type="text" class="form-control" id="specSize" name="size" placeholder="Enter size or dimensions">
            </div>

            <!-- Model -->
            <div class="col-md-6">
              <label for="specModel" class="form-label">
                <i class="fas fa-microchip me-1"></i>Model
              </label>
              <input type="text" class="form-control" id="specModel" name="model" placeholder="Enter model number">
            </div>

            <!-- Warranty Info -->
            <div class="col-md-6">
              <label for="specWarranty" class="form-label">
                <i class="fas fa-shield-alt me-1"></i>Warranty Information
              </label>
              <input type="text" class="form-control" id="specWarranty" name="warranty_info" placeholder="Enter warranty details">
            </div>

            <!-- Additional Notes -->
            <div class="col-12">
              <label for="specNotes" class="form-label">
                <i class="fas fa-sticky-note me-1"></i>Additional Notes
              </label>
              <textarea class="form-control" id="specNotes" name="additional_notes" rows="3" placeholder="Enter any additional specifications or notes"></textarea>
            </div>
          </div>

          <!-- Specifications Status -->
          <div class="mt-3">
            <div id="specificationsStatus" class="alert alert-warning" style="display: none;">
              <span id="specificationsStatusText">No specifications found for this item.</span>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Close
          </button>
          <button type="submit" class="btn btn-primary" id="saveSpecificationsBtn">
            <i class="fas fa-save me-1"></i>Save Specifications
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.specifications-modal .modal-header {
  background: linear-gradient(135deg, #007bff, #0056b3);
}

.specifications-modal .form-label {
  font-weight: 600;
  color: #495057;
}

.specifications-modal .form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.specifications-modal .alert-info {
  background-color: #d1ecf1;
  border-color: #bee5eb;
  color: #0c5460;
}

.specifications-modal .btn-close-white {
  filter: brightness(0) invert(1);
}
</style> 