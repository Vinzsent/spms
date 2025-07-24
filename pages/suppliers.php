<?php
$pageTitle = 'Supplier List';
include '../includes/auth.php';
include '../includes/header.php';
include '../includes/db.php';

$result = $conn->query("SELECT * FROM supplier");
if (!$result) {
    error_log("SQL Error: " . $conn->error);
    $_SESSION['error'] = "Unable to load suppliers at the moment. Please try again later.";
    header("Location: ../dashboard.php");
    exit();
}
?>
<?php include('../includes/navbar.php'); ?>

<?php if (isset($_SESSION['message'])): ?>
  <div class="alert alert-success m-3"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger m-3"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="container py-5">
  <style>
    /* Table styling */
    .table {
      --bs-table-bg: var(--bs-body-bg);
      --bs-table-color: var(--bs-body-color);
      --bs-table-striped-bg: var(--bs-tertiary-bg);
      --bs-table-striped-color: var(--bs-body-color);
      --bs-table-hover-bg: var(--bs-secondary-bg);
      --bs-table-hover-color: var(--bs-body-color);
    }
    
    /* Card styling */
    .card {
      --bs-card-bg: var(--bs-body-bg);
      --bs-card-color: var(--bs-body-color);
      border-color: var(--bs-border-color);
    }
    
    /* Form controls */
    .form-control, .form-select, .form-control:focus, .form-select:focus {
      background-color: var(--bs-body-bg);
      color: var(--bs-body-color);
      border-color: var(--bs-border-color);
    }
    
    /* Modal styling */
    .modal-content {
      background-color: var(--bs-body-bg);
      color: var(--bs-body-color);
      border-color: var(--bs-border-color);
    }
    
    .modal-header, .modal-footer {
      border-color: var(--bs-border-color);
    }
    
    /* Nav tabs */
    .nav-tabs .nav-link {
      color: var(--bs-body-color);
    }
    
    .nav-tabs .nav-link.active {
      background-color: var(--bs-body-bg);
      color: var(--bs-primary);
      border-color: var(--bs-border-color) var(--bs-border-color) var(--bs-body-bg);
    }
    
    .nav-tabs {
      border-bottom-color: var(--bs-border-color);
    }
    
    /* Text areas */
    textarea.form-control {
      background-color: var(--bs-body-bg) !important;
      color: var(--bs-body-color) !important;
    }
  </style>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Supplier List</h3>
    <div>
      <a href="../dashboard.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left"></i> Back</a>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add Supplier</button>
    </div>
  </div>
  <hr>
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>Supplier Name</th>
          <th>Contact Person</th>
          <th>Contact No.</th>
          <th>Email Address</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['supplier_id']) ?></td>
          <td><?= ucwords(strtoupper($row['supplier_name'])) ?></td>
          <td><?= ucwords(strtolower($row['contact_person'])) ?></td>
          <td><?= htmlspecialchars($row['contact_number']) ?></td>
          <td><?= htmlspecialchars($row['email_address']) ?></td>
          <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
              data-category="<?= trim($row['category']) ?>"
              data-business-type="<?= trim($row['business_type']) ?>"
              <?php foreach ($row as $key => $value): ?>
                data-<?= htmlspecialchars(str_replace('_', '-', $key)) ?>="<?= htmlspecialchars($value) ?>"
              <?php endforeach; ?>>
              ✏️ Edit
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modals -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title" id="addModalLabel">Add New Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php include '../modals/add_supplier.php'; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content p-3" action="../actions/edit_supplier.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php include '../modals/edit_supplier.php'; ?>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success w-100">📀 Save Changes</button>
      </div>
    </form>
  </div>
</div>


<script src="../assets/js/supplier-modals.js"></script>
<script src="../assets/js/category-mapping.js"></script>

<script>
  $(document).ready(function() {
    $('#editModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      
      // Set business type
      var businessType = button.data('business-type');
      $('#business-type').val(businessType);
      
      // Set category
      var category = button.data('category');
      $('#category').val(category);
      
      // Trigger change event to update any dependent fields
      $('#business-type').trigger('change');
    });
  });
</script>
</body>
</html>

