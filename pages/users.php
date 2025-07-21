<?php
include '../includes/auth.php';
include '../includes/db.php';

$result = $conn->query("SELECT * FROM user");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .modal-custom {
      display: none;
      position: fixed;
      z-index: 1050;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
    }
    .modal-content-custom {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border-radius: 8px;
      max-width: 500px;
    }
  </style>
</head>
<body class="bg-light">
<?php include('../includes/navbar.php'); ?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>User List</h3>
    <div>
      <a href="../dashboard.php" class="btn btn-secondary me-2">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
      <button class="btn btn-primary" onclick="document.getElementById('addUser').style.display='block'">➕ Add User</button>
	  </div>
  </div>
  <hr>
  <div class="table-responsive">
    <table class="table table-bordered table-hover bg-white">
      <thead class="table-primary">
        <tr>
          <th>Name</th>
          <th>User Type</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
          <td><?= htmlspecialchars($row['user_type']) ?></td>
          <td>
            <button class="btn btn-sm btn-warning"
              onclick="openEditModal(
                <?= $row['id'] ?>,
                '<?= addslashes($row['title']) ?>',
                '<?= addslashes($row['first_name']) ?>',
                '<?= addslashes($row['middle_name']) ?>',
                '<?= addslashes($row['last_name']) ?>',
                '<?= addslashes($row['suffix']) ?>',
                '<?= addslashes($row['academic_title']) ?>',
                '<?= addslashes($row['user_type']) ?>',
                '<?= addslashes($row['email']) ?>'
              )">✏️ Edit</button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal -->
<div id="addUser" class="modal-custom">
  <div class="modal-content-custom shadow">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">Add New User</h5>
      <button class="btn-close" onclick="document.getElementById('addUser').style.display='none'"></button>
    </div>
    <?php include '../modals/add_user.php'; ?>
  </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-custom">
  <div class="modal-content-custom shadow">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">Edit User</h5>
      <button class="btn-close" onclick="document.getElementById('editUserModal').style.display='none'"></button>
    </div>
    <form action="../actions/edit_user.php" method="POST">
      <input type="hidden" name="id" id="edit-id">
      <div class="mb-2"><input type="text" class="form-control" name="title" id="edit-title" placeholder="Title"></div>
      <div class="mb-2"><input type="text" class="form-control" name="first_name" id="edit-firstname" placeholder="First Name" required></div>
      <div class="mb-2"><input type="text" class="form-control" name="middle_name" id="edit-middlename" placeholder="Middle Name"></div>
      <div class="mb-2"><input type="text" class="form-control" name="last_name" id="edit-lastname" placeholder="Last Name" required></div>
      <div class="mb-2"><input type="text" class="form-control" name="suffix" id="edit-suffix" placeholder="Suffix"></div>
      <div class="mb-2"><input type="text" class="form-control" name="academic_title" id="edit-academic-title" placeholder="Academic Title"></div>
      <div class="mb-2">
        <select class="form-select" name="user_type" id="edit-usertype" required>
          <option value="">-- User Type --</option>
          <option value="admin">Admin</option>
          <option value="user">User</option>
        </select>
      </div>
      <div class="mb-2"><input type="email" class="form-control" name="email" id="edit-email" placeholder="Email" required></div>
      <div class="mb-2">
        <input type="password" class="form-control" name="password" placeholder="New Password (leave blank to keep current)">
      </div>
      <button type="submit" class="btn btn-success w-100">💾 Save Changes</button>
    </form>
  </div>
</div>

<script>
  function openEditModal(id, title, firstName, middleName, lastName, suffix, academicTitle, userType, email) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-firstname').value = firstName;
    document.getElementById('edit-middlename').value = middleName;
    document.getElementById('edit-lastname').value = lastName;
    document.getElementById('edit-suffix').value = suffix;
    document.getElementById('edit-academic-title').value = academicTitle;
    document.getElementById('edit-usertype').value = userType;
    document.getElementById('edit-email').value = email;
    document.getElementById('editUserModal').style.display = 'block';
  }

  window.onclick = function(event) {
    const addModal = document.getElementById('addUser');
    const editModal = document.getElementById('editUserModal');
    if (event.target === addModal) addModal.style.display = "none";
    if (event.target === editModal) editModal.style.display = "none";
  };
</script>

</body>
</html>
