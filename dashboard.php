<?php
$pageTitle = 'Admin Dashboard';
include 'includes/auth.php';
include 'includes/header.php';
?>
<?php include('./includes/navbar.php'); ?>

<?php
?>
<style>
  .card {
    height: 200px; /* Set your preferred fixed height */
    overflow-y: auto;
}
</style>

<div class="container">
  <h4 class="mb-4 text-center mt-5">Dashboard Menu</h4>
  <div class="row g-4">

    <!-- Puchase Card -->
    <div class="col-md-4">
      <div class="card text-black bg-warning">
        <div class="card-body">
          <h3 class="card-title text-center">Procurement/Aquisition</h3>
          <p class="card-text">Logs purchased items with supplier, cost, and receipt details, then marks them as received.</p>
          <a href="pages/procurement.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card text-white bg-primary">
        <div class="card-body">
          <h3 class="card-title text-center">Recieved Items</h3>
          <p class="card-text">Record new supplier transactions.</p>
          <a href="pages/transaction_list.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Request Card -->
    <div class="col-md-4">
      <div class="card text-white bg-success">
        <div class="card-body">
          <h3 class="card-title text-center">Supply Requisition/Issuance</h3>
          <p class="card-text">Enables users to request items and managers to approve, reject, or track them.</p>
          <a href="pages/supply_request.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>


    <!-- Inventory Card -->
    <div class="col-md-4">
      <div class="card text-white bg-info">
        <div class="card-body">
          <h3 class="card-title text-center">Inventory Supplies</h3>
          <p class="card-text">Tracks real-time stock levels with stock logs and low inventory alerts.</p>
          <a href="pages/inventory.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Asset Registration Card -->
    <div class="col-md-4">
      <div class="card text-white bg-dark">
        <div class="card-body">
          <h3 class="card-title text-center">Asset Registration</h3>
          <p class="card-text">Registers assets with specs, value, documents, and auto-generated tags or barcodes.</p>
          <a href="pages/supply_request.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Assignment/Issuance Card -->
    <div class="col-md-4">
      <div class="card text-white bg-primary">
        <div class="card-body">
          <h3 class="card-title text-center">Assignment/Issuance</h3>
          <p class="card-text">Handles asset assignment and issuance of supplies with quantity tracking.</p>
          <a href="pages/supply_request.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Maintenance Module Card -->
    <div class="col-md-4">
      <div class="card text-white bg-success">
        <div class="card-body">
          <h3 class="card-title text-center">Maintenance</h3>
          <p class="card-text">Schedules and records asset maintenance with service history and costs.</p>
          <a href="pages/supply_request.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Audit Module Card -->
    <div class="col-md-4">
      <div class="card text-dark bg-warning">
        <div class="card-body">
          <h3 class="card-title text-center">Audit</h3>
          <p class="card-text">Conducts inventory checks by comparing system data with physical counts.</p>
          <a href="pages/supply_request.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Disposal Module Card -->
    <div class="col-md-4">
      <div class="card text-white bg-info">
        <div class="card-body">
          <h3 class="card-title text-center">Disposal</h3>
          <p class="card-text">Manages disposal of obsolete items with approvals and reason tracking.</p>
          <a href="pages/supply_request.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>

    <!-- Reports Module Card -->
    <div class="col-md-4">
      <div class="card text-white bg-dark">
        <div class="card-body">
          <h3 class="card-title text-center">Reports</h3>
          <p class="card-text">Generates reports on requisitions, inventory, maintenance, and disposals.</p>
          <a href="pages/supply_request.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>
    

    <!-- Settings Card -->
    <div class="col-md-4">
      <div class="card text-white bg-danger">
        <div class="card-body">
          <h3 class="card-title text-center">Settings</h3>
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