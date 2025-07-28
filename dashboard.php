<?php
$pageTitle = 'Admin Dashboard';
include 'includes/auth.php';
include 'includes/header.php';
?>
<?php include('./includes/navbar.php'); ?>

<?php
// Display session messages
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['error']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error']);
}
?>

<div class="container">
  <h2 class="mb-4 text-center mt-5">Dashboard</h2>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card text-white bg-dark">
        <div class="card-body">
          <h5 class="card-title text-center">Supplier</h5>
          <p class="card-text">Register a new supplier into the system.</p>
          <a href="pages/suppliers.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card text-white bg-primary">
        <div class="card-body">
          <h5 class="card-title text-center">Recieved Items</h5>
          <p class="card-text">Record new supplier transactions.</p>
          <a href="pages/transaction_list.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Request Card -->
    <div class="col-md-4">
      <div class="card text-white bg-success">
        <div class="card-body">
          <h5 class="card-title text-center">Supply Requisition/Issuance</h5>
          <p class="card-text">Manage request and services details in the system.</p>
          <a href="pages/supply_request.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Settings Card -->
    <div class="col-md-4">
      <div class="card text-white bg-danger">
        <div class="card-body">
          <h5 class="card-title text-center">Settings</h5>
          <p class="card-text">Manage system settings and user preferences. Admin only</p>
          <button onclick="showPasswordModal()" class="btn btn-light">Go</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Password Modal -->
<div id="passwordModal" class="modal-custom">
  <div class="modal-content-custom">
    <div class="text-center mb-3">
      <h4>🔒 Admin Access Required</h4>
      <p class="text-muted">Please enter the admin password to access Settings</p>
    </div>
    <form id="passwordForm" method="POST" action="actions/verify_admin_password.php">
      <div class="mb-3">
        <label for="adminPassword" class="form-label">Admin Password:</label>
        <input type="password" class="form-control" id="adminPassword" name="admin_password" required>
      </div>
      <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-secondary" onclick="hidePasswordModal()">Cancel</button>
        <button type="submit" class="btn btn-danger">Access Settings</button>
      </div>
    </form>
  </div>
</div>

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
</style>

<script>
function showPasswordModal() {
    document.getElementById('passwordModal').style.display = 'block';
}

function hidePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('adminPassword').value = '';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('passwordModal');
    if (event.target == modal) {
        hidePasswordModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>