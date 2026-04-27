<!-- Edit Office Supply Modal (Modern Design) -->
<div class="modal fade" id="editOfficeSupplyModal" tabindex="-1" aria-labelledby="editOfficeSupplyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-success py-3">
                <h5 class="modal-title fw-bold text-white" id="editOfficeSupplyModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Requisition Record
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form id="editOfficeSupplyForm" action="../actions/edit_office_supply_action.php" method="POST">

                    <input type="hidden" name="request_id" id="edit_request_id">

                    <!-- Hidden fields for backend validation/logic -->
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id ?? '') ?>">
                    <input type="hidden" name="request_type" value="office_supply">
                    <input type="hidden" name="department_unit" value="<?= htmlspecialchars($office ?? '') ?>">
                    <input type="hidden" name="category" value="Office Supplies">
                    <input type="hidden" name="item_name" id="edit_item_name" value="">
                    <input type="hidden" name="amount" id="edit_amount" value="0">
                    <input type="hidden" name="sales_type" value="">
                    <input type="hidden" name="brand" value="">
                    <input type="hidden" name="color" value="">


                    <div class="row g-4">
                        <!-- Left Column: Request Info -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                <div class="card-header bg-white border-0 pt-3">
                                    <h6 class="fw-bold text-success mb-0"><i class="fas fa-info-circle me-2"></i>Request Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Date Requested <span style="font-weight: 200;" class="text-secondary">(MM/DD/YY)</span></label>
                                        <input type="date" name="date_requested" id="edit_date_requested" class="form-control form-control-sm border-2 shadow-none" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Item Number</label>
                                        <input type="number" name="item_number" id="edit_item_number" class="form-control form-control-sm border-2 shadow-none fw-bold text-success" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Date Needed</label>
                                        <input type="date" name="date_needed" id="edit_date_needed" class="form-control form-control-sm border-2 shadow-none" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Department / Unit</label>
                                        <input type="text" class="form-control form-control-sm bg-light border-2 shadow-none fw-bold" value="<?= htmlspecialchars($office ?? '') ?>" disabled>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold">Purpose</label>
                                        <input type="text" name="purpose" id="edit_purpose" class="form-control form-control-sm border-2 shadow-none" placeholder="e.g., Office Operations" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Item Info -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                <div class="card-header bg-white border-0 pt-3">
                                    <h6 class="fw-bold text-success mb-0"><i class="fas fa-box-open me-2"></i>Item Specifications</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold">Item Description</label>
                                            <textarea name="request_description" id="edit_description" class="form-control border-2 shadow-none" rows="3" required style="resize: none;"></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Unit Type</label>
                                            <input type="text" name="unit" id="edit_unit" class="form-control form-control-sm border-2 shadow-none" placeholder="pcs / box / ream" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Quantity Issued</label>
                                            <input type="number" step="0.01" name="quantity_requested" id="edit_quantity" class="form-control form-control-sm border-2 shadow-none" required>
                                        </div>
                                        <!--<div class="col-md-4">
                                            <label class="form-label small fw-bold">Quantity Issued (Optional)</label>
                                            <input type="number" step="0.01" name="quality_issued" id="edit_quantity_issued" class="form-control form-control-sm border-2 shadow-none">
                                        </div>-->
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Unit Cost (₱)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-2 border-end-0">₱</span>
                                                <input type="number" step="0.01" name="unit_cost" id="edit_unit_cost" class="form-control border-2 border-start-0 shadow-none" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Total Estimated Cost</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-success-subtle border-2 border-end-0 text-dark fw-bold">₱</span>
                                                <input type="number" step="0.01" name="total_cost" id="edit_total_cost" class="form-control border-2 border-start-0 shadow-none fw-bold bg-white" readonly required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Print-Only Signatures (Hidden in UI, Visible when Printing) -->
                    <div class="d-none d-print-block mt-5 pt-4 border-top border-dark text-center">
                        <div class="row mt-4">
                            <div class="col-4">
                                <div class="border-bottom border-dark mx-3 mt-4"></div>
                                <small>Requested By</small>
                            </div>
                            <div class="col-4">
                                <div class="border-bottom border-dark mx-3 mt-4"></div>
                                <small>Immediate Head</small>
                            </div>
                            <div class="col-4">
                                <div class="border-bottom border-dark mx-3 mt-4"></div>
                                <small>Supply In-charge</small>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4 pt-3 border-top d-print-none">
                        <button type="button" class="btn btn-light border px-4 rounded-pill fw-bold text-muted me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-5 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i>Update Requisition
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editOfficeSupplyModal');
        if (!modal) return;

        const qtyInput = modal.querySelector('#edit_quantity');
        const unitCostInput = modal.querySelector('#edit_unit_cost');
        const totalCostInput = modal.querySelector('#edit_total_cost');
        const hiddenAmount = modal.querySelector('#edit_amount');
        const descInput = modal.querySelector('#edit_description');
        const hiddenItemName = modal.querySelector('#edit_item_name');

        function calculateTotalEdit() {
            const qty = parseFloat(qtyInput.value) || 0;
            const cost = parseFloat(unitCostInput.value) || 0;
            const total = qty * cost;
            totalCostInput.value = total.toFixed(2);
            hiddenAmount.value = total.toFixed(2);
        }

        if (qtyInput && unitCostInput) {
            qtyInput.addEventListener('input', calculateTotalEdit);
            unitCostInput.addEventListener('input', calculateTotalEdit);
        }

        if (descInput && hiddenItemName) {
            descInput.addEventListener('input', function() {
                hiddenItemName.value = this.value.substring(0, 50);
            });
        }
    });
</script>