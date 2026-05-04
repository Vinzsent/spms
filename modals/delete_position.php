<form method="POST" action="../actions/delete_position.php">
    <input type="hidden" name="position_id" id="delete_pos_id">
    
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Are you sure you want to delete this position? This action cannot be undone and may affect users assigned to this position.
    </div>
    
    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('deletePosModal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
    </div>
</form>
