<?php
include '../includes/auth.php';
include '../includes/db.php';

$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Supplier Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #1a5f3c;
      --secondary-color: #ff6b35;
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
      --light-bg: #f8fafc;
      --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    body {
      background: var(--light-bg);
      min-height: 100vh;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .main-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: var(--card-shadow);
      margin-top: 120px;
      margin-bottom: 40px;
      overflow: hidden;
    }

    .page-header {
      background: linear-gradient(135deg, var(--primary-color));
      color: white;
      padding: 2rem;
      position: relative;
      overflow: hidden;
    }

    .page-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }

    .page-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0;
      position: relative;
      z-index: 1;
    }

    .page-subtitle {
      font-size: 1.1rem;
      opacity: 0.9;
      margin: 0.5rem 0 0 0;
      position: relative;
      z-index: 1;
    }

    .content-section {
      padding: 2rem;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .btn-modern {
      border-radius: 12px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      position: relative;
      overflow: hidden;
    }

    .btn-modern::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .btn-modern:hover::before {
      left: 100%;
    }

    .btn-primary-modern {
      background: linear-gradient(135deg, var(--secondary-color));
      color: white;
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
    }

    .btn-primary-modern:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79, 70, 229, 0.6);
      color: white;
    }

    .btn-secondary-modern {
      background: linear-gradient(135deg, #6b7280, #9ca3af);
      color: white;
      box-shadow: 0 4px 15px rgba(107, 114, 128, 0.4);
    }

    .btn-secondary-modern:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(107, 114, 128, 0.6);
      color: white;
    }

    .table-modern {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      border: none;
    }

    .table-modern thead {
      background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    }

    .table-modern th {
      border: none;
      padding: 1.25rem 1rem;
      font-weight: 700;
      color: #374151;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .table-modern td {
      border: none;
      padding: 1.25rem 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #f3f4f6;
    }

    .table-modern tbody tr {
      transition: all 0.3s ease;
    }

    .table-modern tbody tr:hover {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      transform: scale(1.01);
      box-shadow: var(--hover-shadow);
    }

    .btn-action {
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-weight: 600;
      font-size: 0.875rem;
      transition: all 0.3s ease;
      border: none;
      margin: 0.25rem;
    }

    .btn-warning-modern {
      background: linear-gradient(135deg, var(--warning-color), #fbbf24);
      color: white;
      box-shadow: 0 2px 10px rgba(245, 158, 11, 0.3);
    }

    .btn-warning-modern:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(245, 158, 11, 0.5);
      color: white;
    }

    .btn-danger-modern {
      background: linear-gradient(135deg, var(--danger-color), #f87171);
      color: white;
      box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
    }

    .btn-danger-modern:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.5);
      color: white;
    }

    .btn-info-modern {
      background: linear-gradient(135deg, #0ea5e9, #38bdf8);
      color: white;
      box-shadow: 0 2px 10px rgba(14, 165, 233, 0.3);
    }

    .btn-info-modern:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 15px rgba(14, 165, 233, 0.5);
      color: white;
    }

    .alert-modern {
      border-radius: 12px;
      border: none;
      padding: 1rem 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: var(--card-shadow);
    }

    .alert-success-modern {
      background: linear-gradient(135deg, #d1fae5, #a7f3d0);
      color: #065f46;
      border-left: 4px solid var(--success-color);
    }

    .alert-danger-modern {
      background: linear-gradient(135deg, #fee2e2, #fecaca);
      color: #991b1b;
      border-left: 4px solid var(--danger-color);
    }

    .modal-modern .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-modern .modal-header {
      background: linear-gradient(135deg, var(--primary-color));
      color: white;
      border-radius: 16px 16px 0 0;
      border: none;
    }

    .modal-modern .modal-title {
      font-weight: 700;
    }

    .stats-card {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: var(--card-shadow);
      margin-bottom: 2rem;
      border-left: 4px solid var(--primary-color);
    }

    .stats-number {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary-color);
    }

    .stats-label {
      color: #6b7280;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    @media (max-width: 768px) {
      .page-title {
        font-size: 2rem;
      }

      .action-buttons {
        flex-direction: column;
      }

      .btn-modern {
        width: 100%;
      }
    }
  </style>
</head>

<body>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success-modern alert-modern">
      <i class="fas fa-check-circle me-2"></i>
      <?= htmlspecialchars($_SESSION['message']);
      unset($_SESSION['message']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger-modern alert-modern">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <?= htmlspecialchars($_SESSION['error']);
      unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <?php include('../includes/navbar.php'); ?>

  <div class="container">
    <div class="main-container">
      <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h1 class="page-title">Supplier Management</h1>
            <p class="page-subtitle">Manage your supplier database efficiently</p>
          </div>
          <div class="action-buttons">
            <button class="btn btn-secondary-modern btn-modern" onclick="window.history.back()">
              <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <?php if (in_array(strtolower($user_type), ['admin', 'purchasing officer'])): ?>
              <button class="btn btn-primary-modern btn-modern" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i>Add Supplier
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="content-section">
        <!-- Stats Cards -->
        <div class="row mb-4">
          <div class="col-md-4">
            <div class="stats-card">
              <div class="stats-number"><?= $result->num_rows ?></div>
              <div class="stats-label">Total Suppliers</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stats-card">
              <div class="stats-number">
                <i class="fas fa-building text-primary"></i>
              </div>
              <div class="stats-label">Active Records</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stats-card">
              <div class="stats-number">
                <i class="fas fa-chart-line text-success"></i>
              </div>
              <div class="stats-label">Management</div>
            </div>
          </div>
        </div>

        <!-- Suppliers Table -->
        <div class="table-responsive">
          <table class="table table-modern">
            <thead>
              <tr>
                <th><i class="fas fa-building me-2"></i>Supplier Name</th>
                <th><i class="fas fa-user me-2"></i>Contact Person</th>
                <th><i class="fas fa-phone me-2"></i>Mobile No.</th>
                <th><i class="fas fa-phone me-2"></i>Telephone No.</th>
                <th><i class="fas fa-envelope me-2"></i>Email Address</th>
                <?php if (in_array(strtolower($user_type), ['admin', 'purchasing officer'])): ?>
                  <th><i class="fas fa-cogs me-2"></i>Actions</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><strong><?= ucwords(strtoupper($row['supplier_name'])) ?></strong></td>
                  <td><?= ucwords(strtolower($row['contact_person'])) ?></td>
                  <td><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($row['contact_number']) ?></td>
                  <td><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($row['landline_number']) ?></td>
                  <td><i class="fas fa-envelope text-muted me-1"></i><?= htmlspecialchars($row['email_address']) ?></td>
                  <?php if (in_array(strtolower($user_type), ['admin', 'purchasing officer'])): ?>
                    <td>
                      <button class="btn btn-info-modern btn-action btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal" title="View Details"
                        <?php foreach ($row as $key => $value): ?>
                        data-<?= htmlspecialchars(str_replace('_', '-', $key)) ?>="<?= htmlspecialchars($value) ?>"
                        <?php endforeach; ?>>
                        <i class="fas fa-eye"></i>
                      </button>
                      <button class="btn btn-warning-modern btn-action btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" title="Edit Supplier"
                        <?php foreach ($row as $key => $value): ?>
                        data-<?= htmlspecialchars(str_replace('_', '-', $key)) ?>="<?= htmlspecialchars($value) ?>"
                        <?php endforeach; ?>>
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-danger-modern btn-action btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" title="Delete Supplier"
                        data-supplier-id="<?= htmlspecialchars($row['supplier_id']) ?>"
                        data-supplier-name="<?= htmlspecialchars($row['supplier_name']) ?>">
                        <i class="fas fa-trash"></i>

                      </button>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modals -->
  <div class="modal fade modal-modern" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">
            <i class="fas fa-plus me-2"></i>Add New Supplier
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php include '../modals/add_supplier.php'; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade modal-modern" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h5 class="modal-title" id="viewModalLabel">
            <i class="fas fa-eye me-2"></i>View Supplier Details
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php include '../modals/view_supplier.php'; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary-modern btn-modern" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade modal-modern" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <form class="modal-dialog modal-lg" action="../actions/edit_supplier.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">
            <i class="fas fa-edit me-2"></i>Edit Supplier
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php include '../modals/edit_supplier.php'; ?>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary-modern btn-modern w-100">
            <i class="fas fa-save me-2"></i>Save Changes
          </button>
        </div>
      </div>
    </form>
  </div>

  <div class="modal fade modal-modern" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger" id="deleteModalLabel">
            <i class="fas fa-trash me-2"></i>Delete Supplier
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php include '../modals/delete_supplier.php'; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/supplier-modals.js?v=<?= time() ?>"></script>
  <script src="../assets/js/category-mapping.js?v=<?= time() ?>"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('add') === '1') {
        const addModalEl = document.getElementById('addModal');
        const addModal = new bootstrap.Modal(addModalEl);
        addModal.show();

        addModalEl.addEventListener('hidden.bs.modal', function() {
          window.location.href = 'canvas_form.php';
        });
      }
    });
  </script>
</body>

</html>