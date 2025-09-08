<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Procurement Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="../actions/edit_procurement.php" method="POST">
                <input type="hidden" id="edit-id" name="procurement_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <input type="text" id="edit-item" name="item_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <select id="edit-supplier" name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                <!-- Supplier options will be populated via JavaScript -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" id="edit-quantity" name="quantity" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit</label>
                            <select id="edit-unit" name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <option value="pc">Piece</option>
                                <option value="box">Box</option>
                                <option value="kg">Kilogram</option>
                                <option value="liter">Liter</option>
                                <option value="set">Set</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit Price</label>
                            <input type="number" id="edit-price" name="unit_price" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea id="edit-notes" name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>