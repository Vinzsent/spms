<form method="POST" action="../actions/add_user.php">
  <div class="row">
      <div class="col">
        <label for="title">Title</label>
        <input class="form-control mb-2" name="title" id="add-title" placeholder="Title">
      </div>
      <div class="col">
        <label for="suffix">Suffix</label>
        <input class="form-control mb-2" name="suffix" id="add-suffix" placeholder="Suffix">
      </div>
    </div>
    <label for="first_name">First Name</label>
    <input class="form-control mb-2" name="first_name" id="add-firstname" placeholder="First Name">

    <label for="middle_name">Middle Name</label>
    <input class="form-control mb-2" name="middle_name" id="add-lastname" placeholder="Middle Name">

    <label for="last_name">Last Name</label>
    <input class="form-control mb-2" name="last_name" id="add-lastname" placeholder="Last Name">

    <label for="academic_title">Academic Title</label>
    <input class="form-control mb-2" name="academic_title" id="add-academic" placeholder="Academic Title">

    <label for="user_type">Position</label>
    <select class="form-control mb-2" name="user_type">
      <option value="">-- Select Position --</option>
      <option value="Admin">Admin</option>
      <option value="Staff">Staff</option>
    </select>
    
    <label for="email">Email</label>
    <input class="form-control mb-2" type="email" name="email" id="add-email" placeholder="Email">

    <label for="password">Password</label>
    <input class="form-control mb-2" type="password" name="password" id="password" placeholder="Password">
    <button type="submit" class="btn btn-success mt-3">Register</button>
  </form>