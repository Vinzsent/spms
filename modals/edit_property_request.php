<?php
$suppliers = $conn->query("SELECT supplier_id, supplier_name FROM supplier ORDER BY supplier_name ASC");
?>

<!-- Modern Supply Request Modal -->
<div class="modal fade" id="editSupplyModal" tabindex="-1" aria-labelledby="editSupplyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content border-0 shadow-lg">
      <!-- Modern Header with Gradient -->
      <div class="modal-header border-0 bg-gradient-primary text-white">
        <div class="d-flex align-items-center" style="height: 10px;">
          <div class="modal-icon me-3">
            <i class="fas fa-edit fa-2x"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0 fw-bold" id="editSupplyModalLabel">
              <i class="fas fa-edit me-2"></i>Edit Supply Request
              <small class="text-white-50" style="font-size: 15px;">(Update your supply request information)</small>
            </h5>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="../actions/edit_supply_request.php" method="POST">
        <div class="modal-body p-4">
          <!-- User Information Card -->
          <div class="user-info-card mb-4">
            <div class="card border-0 bg-light">
            </div>
          </div>
          
          <!-- Hidden Fields -->
          <input type="hidden" name="request_id" id="editRequestId">
          <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
          <input type="hidden" name="department_unit" class="form-control bg-light" value="<?= htmlspecialchars($user_type) ?>" readonly id="editDepartmentUnit">
          <input type="hidden" name="user_name" value="<?= htmlspecialchars($user_name) ?>">
          <!-- Request Details Section -->
          <div class="section-card mb-4" style="margin-top: -40px;">
            <div class="section-header mb-3">
              <h6 class="section-title">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>Request Details
              </h6>
              <div class="section-divider"></div>
            </div>

            <div class="row g-3">
              <div class="col-md-3">
                <div class="form-floating">
                  <input type="date" name="date_requested" class="form-control" id="editDateRequest" required>
                  <label for="editDateRequest">
                    <i class="fas fa-calendar me-1"></i>Date Requested <span class="text-danger">*</span>
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-floating">
                  <input type="date" name="date_needed" class="form-control" id="editDateNeeded" required>
                  <label for="editDateNeeded">
                    <i class="fas fa-clock me-1"></i>Date Needed <span class="text-danger">*</span>
                  </label>
                </div>
              </div>

              <div class="col-md-6">
              <div class="form-floating">
                <textarea name="purpose" class="form-control" id="editPurpose" style="height: 60px" required></textarea>
                <label for="editPurpose">
                  <i class="fas fa-bullseye me-1"></i>Purpose <span class="text-danger">*</span>
                </label>
              </div>
            </div>

            </div>
          </div>

          
          <!-- Category & Description Section -->
          <div class="section-card mb-4" style="margin-top: -10px;">
            <div class="section-header mb-3">
              <h6 class="section-title">
                <i class="fas fa-tags me-2 text-primary"></i>Category & Item Description
              </h6>
              <div class="section-divider"></div>
            </div>
            

            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-floating">
                  <select name="category" class="form-select" id="editCategorySelect" required>
                    <option value="">Select Category</option>
                    <?php
                    // Use the same organized categories from the main page
                    if (isset($organized_categories) && !empty($organized_categories)) {
                        foreach ($organized_categories as $main_category => $subcategories) {
                            echo '<optgroup label="' . htmlspecialchars($main_category) . '">';
                            foreach ($subcategories as $subcategory) {
                                echo '<option value="' . htmlspecialchars($subcategory) . '">' . htmlspecialchars($subcategory) . '</option>';
                            }
                            echo '</optgroup>';
                        }
                    } else {
                        // Fallback options if no data available - display as bold headers only
                        echo '<optgroup label="Property and Equipment"></optgroup>';
                        echo '<optgroup label="Intangible Assets"></optgroup>';
                        echo '<optgroup label="Office Supplies"></optgroup>';
                        echo '<optgroup label="Medical Supplies"></optgroup>';
                    }
                    ?>
                  </select>
                  <label for="editCategorySelect">
                    <i class="fas fa-folder me-1"></i>Category <span class="text-danger">*</span>
                  </label>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-floating">
                  <input type="number" name="quantity_requested" class="form-control" id="editQuantity" required>
                  <label for="editQuantity">
                    <i class="fas fa-hashtag me-1"></i>Quantity <span class="text-danger">*</span>
                  </label>
                </div>
              </div>

              <div class="form-floating col-md-3">
                <select name="unit" class="form-select" id="editUnit" required>
                  <option value="">Select Unit</option>
                  <optgroup label="Common Units">
                    <option value="pc">Piece (pc)</option>
                    <option value="box">Box</option>
                    <option value="pack">Pack</option>
                    <option value="pad">Pad</option>
                    <option value="ream">Ream</option>
                    <option value="dozen">Dozen</option>
                  </optgroup>
                  <optgroup label="Liquid & Cleaning">
                    <option value="bottle">Bottle</option>
                    <option value="gallon">Gallon</option>
                    <option value="liter">Liter (L)</option>
                    <option value="ml">Milliliter (ml)</option>
                    <option value="roll">Roll</option>
                    <option value="bar">Bar</option>
                  </optgroup>
                  <optgroup label="Measurement">
                    <option value="meter">Meter</option>
                    <option value="cm">Centimeter (cm)</option>
                    <option value="ft">Foot (ft)</option>
                    <option value="kg">Kilogram (kg)</option>
                    <option value="g">Gram (g)</option>
                    <option value="ton">Ton</option>
                    <option value="tube">Tube</option>
                    <option value="can">Can</option>
                  </optgroup>
                  <optgroup label="Laboratory & Medical">
                    <option value="vial">Vial</option>
                    <option value="sachet">Sachet</option>
                  </optgroup>
                  <optgroup label="Equipment">
                    <option value="unit">Unit</option>
                    <option value="set">Set</option>
                    <option value="kit">Kit</option>
                    <option value="pair">Pair</option>
                    <option value="lot">Lot</option>
                    <option value="package">Package</option>
                  </optgroup>
                  <optgroup label="Services">
                    <option value="trip">Trip</option>
                    <option value="hour">Hour</option>
                    <option value="day">Day</option>
                    <option value="service">Service</option>
                  </optgroup>
                </select>

                <label for="editUnit">
                  <i class="fas fa-ruler me-1"></i>Unit <span class="text-danger">*</span>
                </label>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" name="item_name" class="form-control" id="editItemName" style="height: 70px" required>
                  <label for="editItemName">
                    <i class="fas fa-align-items me-1"></i>Item Name <span class="text-danger">*</span>
                  </label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <textarea name="request_description" class="form-control" id="editRequestDescription" style="height: 70px" required></textarea>
                  <label for="editRequestDescription">
                    <i class="fas fa-align-left me-1"></i>Description <span class="text-danger">*</span>
                  </label>
                </div>
              </div>
              

              <div class="col-md-3">
                <div class="form-floating">
                  <input type="text" name="brand" class="form-control" id="editBrand" required>
                  <label for="editBrand">
                    Brand <span class="text-danger">*</span>
                  </label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-floating">
                  <input type="text" name="color" class="form-control" id="editColor" required>
                  <label for="editColor">
                    Color <span class="text-danger">*</span>
                  </label>
                </div>
              </div>

                <div class="col-md-3">
                  <div class="form-floating">
                    <input type="text" name="unit_cost" class="form-control" id="editPrice" pattern="[0-9]*\.?[0-9]*">
                    <label for="editPrice">
                      <i class="fas fa-dollar-sign me-1"></i>Unit Cost (Optional)
                    </label>
                  </div>

                  <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>Leave blank if unknown
                  </small>
                </div>
                
                <div class="col-md-3">
                  <div class="form-floating">
                    <input type="text" id="editTotalAmount" class="form-control bg-light" readonly>
                    <label for="editTotalAmount">
                      <i class="fas fa-calculator me-1"></i>Total Cost
                    </label>
                  </div>
                  <input type="hidden" name="total_cost" id="editHiddenTotalCost">
                  <input type="hidden" name="amount" id="editHiddenAmount">
                </div>

        <!-- Modern Footer -->
        <div class="modal-footer border-0 bg-light" style="margin-top: 10px; padding: 8px 16px;">
          <div class="d-flex justify-content-between w-100">
            <div class="form-info">
              <small class="text-muted" style="font-size: 12px;">
                <i class="fas fa-info-circle me-1"></i>
                Fields marked with <span class="text-danger">*</span> are required
              </small>
            </div>
            <div class="action-buttons">
              <button type="button" class="btn btn-outline-secondary me-2" style="font-size: 12px; padding: 10px 12px;" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i>Cancel
              </button>
              <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 12px 12px;">
                <i class="fas fa-save me-1"></i>Update Request
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div> </div>
</div>

<!-- Bulk Request Confirmation Modal -->
<div class="modal fade" id="bulkConfirmModal" tabindex="-1" aria-labelledby="bulkConfirmLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-gradient-primary text-white">
        <h5 class="modal-title" id="bulkConfirmLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>Bulk Request Confirmation
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="mb-3">
          You are about to make a bulk request (50 or more items).
        </p>
        <p class="text-muted">
          If you continue, you are going through a process and it's going to take long to get your item.
        </p>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary" id="bulkConfirmNo">
          <i class="fas fa-arrow-left me-1"></i>No, go back
        </button>
        <button type="button" class="btn btn-primary" id="bulkConfirmYes">
          <i class="fas fa-check me-1"></i>Yes, I’d like to continue
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Hidden flag to record bulk confirmation -->
<input type="hidden" id="bulkConfirmed" value="0">

<!-- Modern Modal Styles -->
<style>
  /* Gradient Background for Header */
  .bg-gradient-primary {
    background: linear-gradient(135deg, #1a5f3c, #2d7a4d);
  }

  /* Modal Enhancements */
  .modal-content {
    border-radius: 15px;
    overflow: hidden;
  }

  .modal-header {
    padding: 1.5rem;
  }

  .modal-icon {
    width: 60px;
    height: 60px;
    background: #2d7a4d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Section Cards */
  .section-card {
    background: #fff;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #e9ecef;
  }

  .section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .section-title {
    color: #495057;
    font-weight: 600;
    margin: 0;
    font-size: 1.1rem;
  }

  .section-divider {
    flex: 1;
    height: 2px;
    background: linear-gradient(135deg, #1a5f3c, #2d7a4d);
    ;
    border-radius: 1px;
  }

  /* User Info Card */
  .user-info-card .card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
  }

  .user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }

  /* Form Floating Enhancements */
  .form-floating {
    position: relative;
  }

  .form-floating>.form-control,
  .form-floating>.form-select {
    height: 60px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
  }

  .form-floating>.form-control:focus,
  .form-floating>.form-select:focus {
    border-color: #2d7a4d;
    box-shadow: 0 0 0 0.2rem rgba(38, 243, 158, 0.25);
  }

  .form-floating>label {
    padding: 1rem 0.75rem;
    color: #6c757d;
    font-weight: 500;
  }

  .form-floating>.form-control:focus~label,
  .form-floating>.form-control:not(:placeholder-shown)~label,
  .form-floating>.form-select~label {
    color: #2d7a4d;
    transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
  }

  /* Cost Summary Card */
  .cost-summary-card .card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
  }

  .cost-breakdown {
    text-align: left;
  }

  .cost-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
  }

  .cost-item:last-child {
    border-bottom: none;
  }

  .cost-item.total {
    font-weight: bold;
    color: #2d7a4d;
    font-size: 1.1rem;
    border-top: 2px solid #2d7a4d;
    margin-top: 0.5rem;
    padding-top: 0.75rem;
  }

  .cost-label {
    color: #6c757d;
  }

  .cost-value {
    font-weight: 600;
    color: #495057;
  }

  /* Button Enhancements */
  .btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .btn-primary {
    background: linear-gradient(135deg, #1a5f3c, #2d7a4d);
    border: none;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #1a5f3c, #2d7a4d);
    transform: translateY(-2px);
    box-shadow: #2d7a4d;
  }

  .btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
  }

  .btn-outline-secondary:hover {
    background: #6c757d;
    border-color: #6c757d;
    transform: translateY(-2px);
  }

  /* Modal Footer */
  .modal-footer {
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .modal-dialog {
      margin: 1rem;
    }

    .section-card {
      padding: 1rem;
    }

    .user-avatar {
      width: 50px;
      height: 50px;
    }

    .btn {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
    }
  }

  /* Animation Effects */
  .modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
    transform: translate(0, -50px);
  }

  .modal.show .modal-dialog {
    transform: none;
  }

  /* Form Validation Styles */
  .form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
  }

  .form-control.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
  }

  /* Loading States */
  .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  /* Hover Effects */
  .section-card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
    transition: all 0.3s ease;
  }

  /* Icon Enhancements */
  .fas {
    transition: all 0.3s ease;
  }

  .form-floating>.form-control:focus~label .fas,
  .form-floating>.form-control:not(:placeholder-shown)~label .fas {
    color: #2d7a4d;
  }

  /* Dim the underlying modal when the bulk confirmation is active */
  .modal-dim {
    filter: grayscale(60%) brightness(0.35);
    pointer-events: none;
    /* block interaction */
  }

  /* Center only specific optgroup labels in category dropdown */
  #categorySelect optgroup[label="Assets"],
  #categorySelect optgroup[label="Expenses"] {
    text-align: center;
    font-weight: bold;
    font-size: 14px;
    color: #1a5f3c;
    background-color: #f8f9fa;
    padding: 8px 0;
  }

  /* Keep other optgroups left-aligned */
  #categorySelect optgroup:not([label="Assets"]):not([label="Expenses"]) {
    text-align: left;
    font-weight: bold;
    font-size: 14px;
    color: #1a5f3c;
    background-color: #f8f9fa;
    padding: 8px 0;
  }

  #categorySelect option {
    text-align: left;
    padding-left: 20px;
    font-weight: normal;
    color: #333;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Get form elements with new IDs
    const quantityInput = document.getElementById('editQuantity');
    const unitPriceInput = document.getElementById('editPrice');
    const totalAmountInput = document.getElementById('editTotalAmount');
    const hiddenTotalCost = document.getElementById('editHiddenTotalCost');
    const hiddenAmount = document.getElementById('editHiddenAmount');
    const modal = document.getElementById('editSupplyModal');
    const form = modal.querySelector('form');

    // Summary elements
    const summaryQuantity = document.getElementById('summaryQuantity');
    const summaryUnitCost = document.getElementById('summaryUnitCost');
    const summaryTotal = document.getElementById('summaryTotal');

    // Enhanced total calculation with summary update
    function updateTotal() {
      const quantity = parseFloat(quantityInput.value) || 0;
      const unitPrice = parseFloat(unitPriceInput.value) || 0;
      const total = quantity * unitPrice;

      // Update total amount field
      totalAmountInput.value = total.toFixed(2);
      if (hiddenTotalCost) hiddenTotalCost.value = total.toFixed(2);
      if (hiddenAmount) hiddenAmount.value = total.toFixed(2);

      // Update summary display
      if (summaryQuantity) summaryQuantity.textContent = quantity;
      if (summaryUnitCost) summaryUnitCost.textContent = `₱${unitPrice.toFixed(2)}`;
      if (summaryTotal) summaryTotal.textContent = `₱${total.toFixed(2)}`;

      // Add visual feedback
      if (total > 0) {
        summaryTotal.style.color = '#28a745';
        summaryTotal.style.fontWeight = 'bold';
      } else {
        summaryTotal.style.color = '#495057';
        summaryTotal.style.fontWeight = '600';
      }
    }

    // Enhanced event listeners with debouncing
    let updateTimeout;

    function debouncedUpdate() {
      clearTimeout(updateTimeout);
      updateTimeout = setTimeout(updateTotal, 100);
    }

    // Add event listeners for real-time updates
    if (quantityInput && unitPriceInput && totalAmountInput) {
      // Add input validation for quantity field to allow only numbers
      quantityInput.addEventListener('input', function(e) {
        // Remove any non-numeric characters except for the first character
        this.value = this.value.replace(/[^0-9]/g, '');
        debouncedUpdate();
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
        debouncedUpdate();
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

      // Add focus effects
      quantityInput.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
      });

      quantityInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('focused');
      });

      unitPriceInput.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
      });

      unitPriceInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('focused');
      });
    }

    // Initialize when modal is shown
    if (modal) {
      modal.addEventListener('shown.bs.modal', function() {
        updateTotal();

        // Set default date to today
        const today = new Date().toISOString().split('T')[0];
        const dateRequested = document.getElementById('editDateRequest');
        if (dateRequested && !dateRequested.value) {
          dateRequested.value = today;
        }

        // Focus on first input
        setTimeout(() => {
          const firstInput = modal.querySelector('input:not([readonly]):not([type="hidden"])');
          if (firstInput) firstInput.focus();
        }, 300);
      });
    }

    // Bulk confirmation modal logic
    const bulkModalEl = document.getElementById('bulkConfirmModal');
    const bulkConfirmedEl = document.getElementById('bulkConfirmed');
    let bulkModal;
    if (bulkModalEl && window.bootstrap) {
      bulkModal = new bootstrap.Modal(bulkModalEl, {
        backdrop: 'static',
        keyboard: false
      });

      // When bulk confirmation shows, dim the underlying add modal
      const addModalContent = document.querySelector('#editSupplyModal .modal-content');
      bulkModalEl.addEventListener('show.bs.modal', function() {
        if (addModalContent) addModalContent.classList.add('modal-dim');
      });
      bulkModalEl.addEventListener('hidden.bs.modal', function() {
        if (addModalContent) addModalContent.classList.remove('modal-dim');
      });
    }

    function needsBulkConfirm() {
      const qty = parseInt(quantityInput.value || '0', 10);
      return qty >= 50;
    }

    function maybePromptBulkConfirm() {
      if (!bulkModal) return;
      if (needsBulkConfirm() && bulkConfirmedEl && bulkConfirmedEl.value !== '1') {
        bulkModal.show();
      }
    }

    if (quantityInput) {
      quantityInput.addEventListener('change', () => {
        // Reset confirmation if value goes below threshold
        if (!needsBulkConfirm() && bulkConfirmedEl) {
          bulkConfirmedEl.value = '0';
        }
        maybePromptBulkConfirm();
      });

      quantityInput.addEventListener('input', () => {
        // Live check while typing
        maybePromptBulkConfirm();
      });
    }

    const btnYes = document.getElementById('bulkConfirmYes');
    const btnNo = document.getElementById('bulkConfirmNo');
    if (btnYes) {
      btnYes.addEventListener('click', () => {
        if (bulkConfirmedEl) bulkConfirmedEl.value = '1';
        if (bulkModal) bulkModal.hide();
      });
    }
    if (btnNo) {
      btnNo.addEventListener('click', () => {
        if (bulkConfirmedEl) bulkConfirmedEl.value = '0';
        if (bulkModal) bulkModal.hide();
        if (quantityInput) {
          quantityInput.focus();
          quantityInput.select && quantityInput.select();
        }
      });
    }

    // Form submission enhancement with bulk check
    if (form) {
      form.addEventListener('submit', function(e) {
        // Update totals before submission
        updateTotal();

        // If bulk threshold is met but not confirmed, block and show confirm modal
        if (needsBulkConfirm() && (!bulkConfirmedEl || bulkConfirmedEl.value !== '1')) {
          e.preventDefault();
          maybePromptBulkConfirm();
          return;
        }

        // Add loading state to submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
        }

        // Re-enable after a delay (in case of validation errors)
        setTimeout(() => {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Update Request';
          }
        }, 3000);
      });
    }

    // Enhanced form validation
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
      field.addEventListener('blur', function() {
        if (this.value.trim() === '') {
          this.classList.add('is-invalid');
        } else {
          this.classList.remove('is-invalid');
          this.classList.add('is-valid');
        }
      });
    });
  });
</script>