<form id="deleteUserForm" action="../actions/delete_user.php" method="POST">
  <input type="hidden" name="id" id="delete-id">
  <p>Are you sure you want to delete this user?</p>
  <div class="d-flex justify-content-end gap-2">
    <button type="button" class="btn btn-secondary" onclick="document.getElementById('deleteUser').style.display='none'">Cancel</button>
    <button type="submit" class="btn btn-danger">Delete</button>
  </div>
</form> 