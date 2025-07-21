<form method="POST" action="../actions/add_user.php">
  <div class="row">
      <div class="col">
        <input class="form-control mb-2" name="title" placeholder="Title">
      </div>
      <div class="col">
        <input class="form-control mb-2" name="suffix" placeholder="Suffix">
      </div>
    </div>
    <input class="form-control mb-2" name="first_name" placeholder="First Name">
    <input class="form-control mb-2" name="middle_name" placeholder="Middle Name">
    <input class="form-control mb-2" name="last_name" placeholder="Last Name">
    <input class="form-control mb-2" name="academic_title" placeholder="Academic Title">
    <select class="form-control mb-2" name="user_type">
      <option value="admin">Admin</option>
      <option value="staff">Staff</option>
    </select>
    <input class="form-control mb-2" type="email" name="email" placeholder="Email">
    <input class="form-control mb-2" type="password" name="password" placeholder="Password">
    <button type="submit" class="btn btn-success">Register</button>
  </form>