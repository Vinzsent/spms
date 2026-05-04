<form method="POST" action="../actions/add_position.php">
    <div class="mb-3">
        <label for="position_name" class="form-label">Position Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="position_name" name="position_name" placeholder="e.g., Faculty - CELA" required>
        <div class="form-text">Enter the title or name of the new position.</div>
    </div>
    
    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('addPosModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Position</button>
    </div>
</form>
