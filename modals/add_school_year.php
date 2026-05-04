<form method="POST" action="../actions/add_school_year.php">
    <div class="mb-3">
        <label for="school_year_name" class="form-label">School Year Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="school_year_name" name="school_year_name" placeholder="e.g., 2024-2025" required>
        <div class="form-text">Enter the school year format (e.g., 2024-2025).</div>
    </div>
    
    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('addSYModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Save School Year</button>
    </div>
</form>
