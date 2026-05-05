<form method="POST" action="../actions/delete_school_year.php">
    <input type="hidden" name="shoo_year_id" id="delete_sy_id">
    
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Are you sure you want to delete this school year? This action cannot be undone.
    </div>
    
    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('deleteSYModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
    </div>
</form>
