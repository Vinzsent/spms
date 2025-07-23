<?php
$pageTitle = 'Supplier List';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$result = $conn->query("SELECT * FROM supplier");
if (!$result) {
    error_log("SQL Error: " . $conn->error);
    $_SESSION['error'] = "Unable to load suppliers at the moment. Please try again later.";
    header("Location: ../dashboard.php");
    exit();
}

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
      
      <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3>Supplier List</h3>
          <div>
            <a href="../dashboard.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left"></i> Back</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">➕ Add Supplier</button>
          </div>
        </div>
        <h5>After submitting supplier information, Refresh the page to add another one.</h5>
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
          <td><?= htmlspecialchars (strtoupper($row['supplier_name'])) ?></td>
          <td><?= htmlspecialchars(ucwords(strtolower($row['contact_person']))) ?></td>
          <td><?= htmlspecialchars($row['contact_number']) ?></td>
          <td><?= htmlspecialchars($row['email_address']) ?></td>
          <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
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
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../actions/save_supplier.php" method="post">
        <div class="modal-body">
          <div class="mb-3">
            <label for="supplierName" class="form-label">Supplier Name</label>
            <input type="text" class="form-control" id="supplierName" name="supplier_name" required>
          </div>
          <div class="mb-3">
            <label for="contactPerson" class="form-label">Contact Person</label>
            <input type="text" class="form-control" id="contactPerson" name="contact_person">
          </div>
          <div class="mb-3">
            <label for="contactNo" class="form-label">Contact Number</label>
            <input type="tel" class="form-control" id="contactNo" name="contact_no">
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email">
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Supplier</button>
        </div>
      </form>
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

<script>
// Edit Supplier Modal Handler
document.addEventListener('DOMContentLoaded', function() {
  const editButtons = document.querySelectorAll('.edit-btn');
  const editSupplierModal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
  
  editButtons.forEach(button => {
    button.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      const name = this.getAttribute('data-name');
      const contact = this.getAttribute('data-contact');
      const phone = this.getAttribute('data-phone');
      const email = this.getAttribute('data-email');
      const address = this.getAttribute('data-address');
      
      document.getElementById('edit_id').value = id;
      document.getElementById('edit_supplier_name').value = name;
      document.getElementById('edit_contact_person').value = contact;
      document.getElementById('edit_contact_no').value = phone;
      document.getElementById('edit_email').value = email;
      document.getElementById('edit_address').value = address;
      
      editSupplierModal.show();
    });
  });
});
</script>

<?php include('../includes/footer.php'); ?>
