<form method="POST" action="../actions/edit_school_year.php">
    <input type="hidden" name="shoo_year_id" id="edit_sy_id">
    
    <div class="mb-3">
        <label for="edit_sy_name" class="form-label">School Year Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="edit_sy_name" name="school_year_name" required>
        <div class="form-text">Update the school year format (e.g., 2024-2025).</div>
    </div>
    
    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('editSYModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Update School Year</button>
    </div>
</form>
