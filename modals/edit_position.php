<form method="POST" action="../actions/edit_position.php">
    <input type="hidden" name="position_id" id="edit_pos_id">
    
    <div class="mb-3">
        <label for="edit_pos_name" class="form-label">Position Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="edit_pos_name" name="position_name" required>
        <div class="form-text">Update the title or name of the position.</div>
    </div>
    
    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('editPosModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Position</button>
    </div>
</form>
