  <form id="deleteSupplierForm" action="../actions/delete_supplier.php" method="POST">
  <input type="hidden" name="supplier_id" id="delete-supplier-id">
  <input type="hidden" name="supplier_name" id="delete-supplier-name">
  <p>Are you sure you want to delete supplier: <strong id="delete-supplier-name-display"></strong>?</p>
  <p class="text-danger"><small>This action cannot be undone.</small></p>
  <div class="d-flex justify-content-end gap-2">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-danger">Delete</button>
  </div>
</form> 