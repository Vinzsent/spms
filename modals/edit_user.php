<form action="../actions/edit_user.php" method="POST">
  <input type="hidden" name="id" id="edit-id">
  <div class="row">
    <div class="col">
      <label for="title">Title</label>
      <input class="form-control mb-2" name="title" id="edit-title" placeholder="Title">
    </div>
    <div class="col">
      <label for="suffix">Suffix</label>
      <input class="form-control mb-2" name="suffix" id="edit-suffix" placeholder="Suffix">
    </div>
  </div>
  <label for="edit-firstname">First Name</label>
  <input class="form-control mb-2" name="first_name" id="edit-firstname" placeholder="First Name" required>
  <label for="edit-middlename">Middle Name</label>
  <input class="form-control mb-2" name="middle_name" id="edit-middlename" placeholder="Middle Name">
  <label for="edit-lastname">Last Name</label>
  <input class="form-control mb-2" name="last_name" id="edit-lastname" placeholder="Last Name" required>
  <label for="edit-academictitle">Academic Title</label>
  <input class="form-control mb-2" name="academic_title" id="edit-academictitle" placeholder="Academic Title">
  <label for="edit-usertype">Position</label>
  <select class="form-control mb-2" name="user_type" id="edit-usertype" required>
    <option value"">-- Select Position --</option>
    <option value="Admin">Admin</option>
    <option value="Staff">Staff</option>
  </select>
  <input class="form-control mb-2" type="email" name="email" id="edit-email" placeholder="Email" required>
  <input class="form-control mb-2" type="password" name="password" placeholder="Password (leave blank to keep current)">
  <button type="submit" class="btn btn-success w-100">💾 Update User</button>
</form>
