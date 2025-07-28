<?php
$pageTitle = 'User Management';
include '../includes/auth.php';

// Check if admin is verified (password was entered correctly)
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
    $_SESSION['error'] = 'Admin access required. Please enter the admin password.';
    header("Location: ../dashboard.php");
    exit;
}

// Check if admin session is still valid (30 minutes timeout)
if (isset($_SESSION['admin_verified_time']) && (time() - $_SESSION['admin_verified_time']) > 1800) {
    unset($_SESSION['admin_verified']);
    unset($_SESSION['admin_verified_time']);
    $_SESSION['error'] = 'Admin session expired. Please re-enter the password.';
    header("Location: ../dashboard.php");
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

$result = $conn->query("SELECT * FROM user");

// Display session messages
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>

<?php include('../includes/navbar.php'); ?>

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
    background-color: var(--bs-body-bg);
    margin: 10% auto;
    padding: 2rem;
    border-radius: 8px;
    max-width: 500px;
    color: var(--bs-body-color);
    border: 1px solid var(--bs-border-color);
}

/* Dark mode specific styles */
[data-bs-theme="dark"] .modal-content-custom {
    background-color: var(--bs-tertiary-bg);
}

[data-bs-theme="dark"] .table {
    --bs-table-bg: var(--bs-body-bg);
    --bs-table-color: var(--bs-body-color);
}
</style>
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
          <th>Position</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
          <td><?= htmlspecialchars(strtoupper($row['user_type'])) ?></td>
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

            <button type="submit" class="btn btn-sm btn-danger"
              onclick="openDeleteModal(
                <?= $row['id'] ?>,
                '<?= addslashes($row['title']) ?>',
                '<?= addslashes($row['first_name']) ?>',
                '<?= addslashes($row['middle_name']) ?>',
                '<?= addslashes($row['last_name']) ?>',
                '<?= addslashes($row['suffix']) ?>',
                '<?= addslashes($row['academic_title']) ?>',
                '<?= addslashes ($row['user_type']) ?>',
                '<?= addslashes($row['email']) ?>'
              )">❌ Delete</button>
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
<div id="editUser" class="modal-custom">
  <div class="modal-content-custom shadow">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">Edit User</h5>
      <button class="btn-close" onclick="document.getElementById('editUser').style.display='none'"></button>
    </div>
    <?php include '../modals/edit_user.php'; ?>
  </div>
</div>

<!-- Delete User Modal -->
<div id="deleteUser" class="modal-custom">
  <div class="modal-content-custom shadow">
    <div class="d-flex justify-content-between mb-3">
      <h5 class="mb-0">Delete User</h5>
      <button class="btn-close" onclick="document.getElementById('deleteUser').style.display='none'"></button>
    </div>
    <?php include '../modals/delete_user.php'; ?>
  </div>
</div>

<script>
// Close modals when clicking outside
window.onclick = function(event) {
  if (event.target.className === 'modal-custom') {
    event.target.style.display = 'none';
  }
}

function openEditModal(id, title, firstName, middleName, lastName, suffix, academicTitle, userType, email) {
  document.getElementById('edit-id').value = id;
  document.getElementById('edit-title').value = title;
  document.getElementById('edit-firstname').value = firstName;
  document.getElementById('edit-middlename').value = middleName || '';
  document.getElementById('edit-lastname').value = lastName;
  document.getElementById('edit-suffix').value = suffix || '';
  document.getElementById('edit-academictitle').value = academicTitle || '';
  document.getElementById('edit-usertype').value = userType;
  document.getElementById('edit-email').value = email;
  
  // Show the edit modal
  document.getElementById('editUser').style.display = 'block';
}

function openDeleteModal(id) {
  document.getElementById('delete-id').value = id;
  document.getElementById('deleteUser').style.display = 'block';
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    document.querySelectorAll('.modal-custom').forEach(modal => {
      modal.style.display = 'none';
    });
  }
});

// Initialize DataTable with dark mode support
document.addEventListener('DOMContentLoaded', function() {
  // Initialize any DataTables if present
  if ($.fn.DataTable.isDataTable('table')) {
    $('table').DataTable({
      responsive: true,
      pageLength: 10,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search..."
      },
      dom: 'Bfrtip',
      buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
      ]
    });
  }
});
</script>

<?php include('../includes/footer.php'); ?>
</html>
