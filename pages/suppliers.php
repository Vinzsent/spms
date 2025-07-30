<?php
include '../includes/auth.php';
include '../includes/db.php';

$user_type = $_SESSION['user_type']?? '';

$dashboard_link = ($user_type == 'Admin') ? '../admin_dashboard.php' : '../dashboard.php';


$result = $conn->query("SELECT * FROM supplier");
if (!$result) {
    error_log("SQL Error: " . $conn->error);
    $_SESSION['error'] = "Unable to load suppliers at the moment. Please try again later.";
    header("Location: ../dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Supplier List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-light">

<?php if (isset($_SESSION['message'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php include('../includes/navbar.php'); ?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark">Supplier List</h3>
    <div>
      <button class="btn btn-secondary me-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add Supplier</button>
    </div>
  </div>
  <hr>
  <div class="table-responsive">
    <table class="table table-bordered table-hover bg-white">
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
            <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal"
              <?php foreach ($row as $key => $value): ?>
                data-<?= htmlspecialchars(str_replace('_', '-', $key)) ?>="<?= htmlspecialchars($value) ?>"
              <?php endforeach; ?>>
              ✏️ Edit
            </button>
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"
              data-supplier-id="<?= htmlspecialchars($row['supplier_id']) ?>"
              data-supplier-name="<?= htmlspecialchars($row['supplier_name']) ?>">
              🗑️ Delete
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

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger" id="deleteModalLabel">🗑️ Delete Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php include '../modals/delete_supplier.php'; ?>
      </div>
    </div>
  </div>
</div>


<script src="../assets/js/supplier-modals.js"></script>
<script src="../assets/js/category-mapping.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
