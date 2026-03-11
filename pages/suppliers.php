<?php
include '../includes/auth.php';
include '../includes/db.php';

$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';

$dashboard_link = ($user_type == 'Admin') ? '../admin_dashboard.php' : '../dashboard.php';

// Handle AJAX requests for pagination/search
$isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == 1);

$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%$search%";

// Count for pagination (Filtered)
if (!empty($search)) {
  $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM supplier WHERE supplier_name LIKE ? OR contact_person LIKE ? OR email_address LIKE ?");
  $count_stmt->bind_param("sss", $search_param, $search_param, $search_param);
  $count_stmt->execute();
  $total_filtered = $count_stmt->get_result()->fetch_assoc()['count'];
} else {
  $total_filtered = $conn->query("SELECT COUNT(*) as count FROM supplier")->fetch_assoc()['count'];
}

$total_pages = max(1, (int) ceil($total_filtered / $records_per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $records_per_page;

if (!empty($search)) {
  $stmt = $conn->prepare("SELECT * FROM supplier WHERE supplier_name LIKE ? OR contact_person LIKE ? OR email_address LIKE ? LIMIT ?, ?");
  $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $offset, $records_per_page);
} else {
  $stmt = $conn->prepare("SELECT * FROM supplier LIMIT ?, ?");
  $stmt->bind_param("ii", $offset, $records_per_page);
}
$stmt->execute();
$result = $stmt->get_result();

// Prepare table rows for AJAX or initial load
ob_start();
if ($result->num_rows > 0):
  while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><strong><?= ucwords(strtoupper($row['supplier_name'])) ?></strong></td>
      <td><?= ucwords(strtolower($row['contact_person'])) ?></td>
      <td><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($row['contact_number']) ?></td>
      <td><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($row['landline_number']) ?></td>
      <td><i class="fas fa-envelope text-muted me-1"></i><?= htmlspecialchars($row['email_address']) ?></td>
      <?php if (in_array(strtolower($user_type), ['admin', 'purchasing officer', 'purchasing staff', 'purchasingstaff'])): ?>
        <td class="action-cell">
          <div class="action-dropdown" onclick="toggleActionDropdown(event, this)">
            <button class="btn btn-sm btn-action-trigger" title="More Actions">
              <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="action-dropdown-menu">
              <a href="#" class="action-dropdown-item" data-bs-toggle="modal" data-bs-target="#viewModal"
                <?php foreach ($row as $key => $value): ?>
                data-<?= htmlspecialchars(str_replace('_', '-', $key)) ?>="<?= htmlspecialchars($value) ?>"
                <?php endforeach; ?>>
                <i class="fas fa-eye text-info me-2"></i> View Details
              </a>
              <a href="#" class="action-dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal"
                <?php foreach ($row as $key => $value): ?>
                data-<?= htmlspecialchars(str_replace('_', '-', $key)) ?>="<?= htmlspecialchars($value) ?>"
                <?php endforeach; ?>>
                <i class="fas fa-edit text-warning me-2"></i> Edit Supplier
              </a>
              <a href="#" class="action-dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"
                data-supplier-id="<?= htmlspecialchars($row['supplier_id']) ?>"
                data-supplier-name="<?= htmlspecialchars($row['supplier_name']) ?>">
                <i class="fas fa-trash me-2"></i> Delete
              </a>
            </div>
          </div>
        </td>
      <?php endif; ?>
    </tr>
  <?php endwhile;
else: ?>
  <tr>
    <td colspan="<?= in_array(strtolower($user_type), ['admin', 'purchasing officer', 'purchasing staff', 'purchasingstaff']) ? 6 : 5 ?>" class="text-center py-4">
      <div class="text-muted">No suppliers found matching your search.</div>
    </td>
  </tr>
<?php endif;
$table_rows = ob_get_clean();

// Prepare Pagination HTML
ob_start();
if ($total_filtered > 0): ?>
  <div class="pagination-wrapper d-flex justify-content-between align-items-center flex-wrap mt-3">
    <div class="pagination-info mb-2 mb-sm-0">
      Showing <?= ($total_filtered > 0) ? $offset + 1 : 0 ?> to <?= min($offset + $records_per_page, $total_filtered) ?> of <?= $total_filtered ?> entries
    </div>
    <nav aria-label="Supplier pagination">
      <ul class="pagination-modern mb-0">
        <li class="page-item-modern <?= $page <= 1 ? 'disabled' : '' ?>" onclick="<?= $page > 1 ? "loadSuppliers(" . ($page - 1) . ")" : "" ?>">
          <a class="page-link-modern" href="javascript:void(0)">Previous</a>
        </li>
        <?php
        // Simple "smart" pagination showing limited numbers
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);

        if ($start_page > 1) {
          echo '<li class="page-item-modern" onclick="loadSuppliers(1)"><a class="page-link-modern" href="javascript:void(0)">1</a></li>';
          if ($start_page > 2) echo '<li class="page-item-modern disabled"><span class="page-link-modern">...</span></li>';
        }

        for ($i = $start_page; $i <= $end_page; $i++): ?>
          <li class="page-item-modern <?= $page == $i ? 'active' : '' ?>" onclick="loadSuppliers(<?= $i ?>)">
            <a class="page-link-modern" href="javascript:void(0)"><?= $i ?></a>
          </li>
        <?php endfor;

        if ($end_page < $total_pages) {
          if ($end_page < $total_pages - 1) echo '<li class="page-item-modern disabled"><span class="page-link-modern">...</span></li>';
          echo '<li class="page-item-modern" onclick="loadSuppliers(' . $total_pages . ')"><a class="page-link-modern" href="javascript:void(0)">' . $total_pages . '</a></li>';
        }
        ?>
        <li class="page-item-modern <?= $page >= $total_pages ? 'disabled' : '' ?>" onclick="<?= $page < $total_pages ? "loadSuppliers(" . ($page + 1) . ")" : "" ?>">
          <a class="page-link-modern" href="javascript:void(0)">Next</a>
        </li>
      </ul>
    </nav>
  </div>
<?php endif;
$pagination_html = ob_get_clean();

// Return JSON if AJAX
if ($isAjax) {
  header('Content-Type: application/json');
  echo json_encode([
    'success' => true,
    'table_rows' => $table_rows,
    'pagination' => $pagination_html,
    'total_count' => $total_filtered
  ]);
  exit;
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
      --active-green: #1a5f3c;

      /* Sidebar specific colors from procurement_statistics.php */
      --primary-green: #073b1d;
      --dark-green: #073b1d;
      --accent-orange: #EACA26;
    }

    body {
      background: var(--light-bg);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
    }

    /* Sidebar Styles from procurement_statistics.php */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      height: 100vh;
      width: 240px;
      background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
      color: white;
      z-index: 1000;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header {
      padding: 15px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header h3 {
      margin: 0;
      font-weight: 700;
      font-size: 1.25rem;
      color: white;
    }

    .welcome-text {
      font-size: 0.8rem;
      opacity: 0.9;
      margin-top: 5px;
    }

    .sidebar-nav {
      padding: 10px 0;
    }

    .sidebar-nav ul {
      list-style: none;
      margin: 0;
      padding-left: 0;
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 8px 15px;
      color: white;
      text-decoration: none;
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
      font-size: 0.85rem;
    }

    .nav-link:hover {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
      border-left-color: var(--accent-orange);
    }

    .nav-link.active {
      background-color: rgba(255, 255, 255, 0.15);
      border-left-color: var(--accent-orange);
      font-weight: 600;
    }

    .nav-link i {
      margin-right: 10px;
      width: 18px;
      text-align: center;
    }

    .nav-link.logout {
      color: #ef4444;
      margin-top: 10px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Main Content Layout Adjustments */
    .main-content {
      margin-left: 240px;
      padding: 1.5rem;
      min-height: 100vh;
    }

    .main-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: var(--card-shadow);
      margin-top: 0;
      /* Removed top margin */
      margin-bottom: 2rem;
      /* overflow: hidden removed to prevent dropdown clipping */
    }

    .page-header {
      background: linear-gradient(135deg, var(--primary-color));
      color: white;
      padding: 1.25rem 2rem;
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
      font-size: 1.75rem;
      font-weight: 700;
      margin: 0;
      position: relative;
      z-index: 1;
    }

    .page-subtitle {
      font-size: 1rem;
      opacity: 0.9;
      margin: 0.25rem 0 0 0;
      position: relative;
      z-index: 1;
    }

    .content-section {
      padding: 1.25rem;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .btn-modern {
      border-radius: 10px;
      padding: 0.5rem 1.25rem;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      position: relative;
      overflow: hidden;
      font-size: 0.9rem;
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
      box-shadow: var(--card-shadow);
      border: none;
    }

    .table-modern thead {
      background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    }

    .table-modern th {
      border: none;
      padding: 0.75rem 1rem;
      font-weight: 700;
      color: #374151;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .table-modern td {
      border: none;
      padding: 0.6rem 1rem;
      vertical-align: middle;
      border-bottom: 1px solid #f3f4f6;
      font-size: 0.9rem;
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

    /* Action Dropdown Menu Styling */
    .action-cell {
      position: relative;
    }

    .action-dropdown {
      position: relative;
      display: inline-block;
    }

    .btn-action-trigger {
      background: transparent;
      border: none;
      color: #6b7280;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      cursor: pointer;
    }

    .btn-action-trigger:hover,
    .action-dropdown.active .btn-action-trigger {
      background: rgba(0, 0, 0, 0.05);
      color: #111827;
    }

    .action-dropdown-menu {
      position: absolute;
      right: 0;
      top: 100%;
      min-width: 180px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
      border: 1px solid #f3f4f6;
      padding: 0.5rem;
      z-index: 9999;
      visibility: hidden;
      opacity: 0;
      transform: translateY(-10px);
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      transform-origin: top right;
    }

    .action-dropdown.active .action-dropdown-menu {
      visibility: visible;
      opacity: 1;
      transform: translateY(4px);
    }

    .action-dropdown-item {
      display: flex;
      align-items: center;
      padding: 0.6rem 1rem;
      color: #374151;
      text-decoration: none;
      border-radius: 8px;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .action-dropdown-item i {
      width: 16px;
      text-align: center;
    }

    .action-dropdown-item:hover {
      background-color: #f9fafb;
      color: #111827;
    }

    .action-dropdown-item.text-danger:hover {
      background-color: #fef2f2;
      color: #dc2626 !important;
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
      border-radius: 12px;
      padding: 0.75rem 1rem;
      box-shadow: var(--card-shadow);
      margin-bottom: 1.25rem;
      border-left: 4px solid var(--primary-color);
    }

    .stats-number {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-color);
    }

    .stats-label {
      color: #6b7280;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-size: 0.75rem;
    }

    /* Modern Pagination Styles matching image */
    .pagination-modern {
      display: flex;
      list-style: none;
      padding: 0;
      gap: 5px;
    }

    .page-item-modern {
      cursor: pointer;
    }

    .page-link-modern {
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 40px;
      height: 40px;
      padding: 0 12px;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      background: white;
      color: #4b5563;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .page-item-modern.active .page-link-modern {
      background: var(--active-green);
      border-color: var(--active-green);
      color: white;
    }

    .page-item-modern:not(.active):not(.disabled):hover .page-link-modern {
      background: #f9fafb;
      border-color: #d1d5db;
      color: #111827;
    }

    .page-item-modern.disabled {
      cursor: not-allowed;
      opacity: 0.5;
    }

    .pagination-info {
      color: #6b7280;
      font-size: 0.95rem;
    }

    /* Search Bar in Header */
    .header-search-container {
      flex: 1;
      max-width: 400px;
      margin: 0 2rem;
    }

    .header-search-input {
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 10px;
      padding: 0.5rem 1rem 0.5rem 2.5rem;
      color: white;
      width: 100%;
      transition: all 0.3s ease;
      backdrop-filter: blur(5px);
      font-size: 0.9rem;
    }

    .header-search-input::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }

    .header-search-input:focus {
      background: rgba(255, 255, 255, 0.25);
      border-color: rgba(255, 255, 255, 0.4);
      outline: none;
      box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
      color: white;
    }

    .search-icon-wrapper {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(255, 255, 255, 0.7);
      pointer-events: none;
    }

    .table-responsive {
      overflow: visible;
      min-height: 250px;
      padding-bottom: 150px;
      /* Space for the last dropdown */
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

  <!-- Sidebar from procurement_statistics.php -->
  <div class="sidebar">
    <div class="sidebar-header">
      <h3>DARTS</h3>
      <div class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></div>
    </div>
    <nav class="sidebar-nav">
      <ul>
        <li><a href="../dashboard.php" class="nav-link">
            <i class="fas fa-chart-line"></i> Dashboard
          </a></li>
        <li><a href="suppliers.php" class="nav-link active">
            <i class="fas fa-users"></i> Suppliers
          </a></li>
        <li><a href="received_items.php" class="nav-link">
            <i class="fas fa-box-open"></i> Received Items
          </a></li>
        <li><a href="purchase_order_list.php" class="nav-link">
            <i class="fas fa-file-invoice"></i> Purchase Order List
          </a></li>
        <li><a href="procurement_statistics.php" class="nav-link">
            <i class="fas fa-chart-line"></i> Procurement Statistics
          </a></li>
        <li><a href="procurement.php" class="nav-link">
            <i class="fas fa-shopping-cart"></i> Procurement Tables
          </a></li>
        <li><a href="canvass_form.php" class="nav-link">
            <i class="fas fa-file-invoice"></i> Canvass Form
          </a></li>
        <li><a href="canvass_form_list.php" class="nav-link">
            <i class="fas fa-list"></i> Canvass Form List
          </a></li>
        <li><a href="purchase_order.php" class="nav-link">
            <i class="fas fa-shopping-basket"></i> Purchase Order
          </a></li>
        <li><a href="Inventory.php" class="nav-link">
            <i class="fas fa-box"></i> Supply Inventory
          </a></li>
        <li><a href="property_inventory.php" class="nav-link">
            <i class="fas fa-boxes"></i> Property Inventory
          </a></li>
        <li><a href="../logout.php" class="nav-link logout">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a></li>
      </ul>
    </nav>
  </div>

  <div class="main-content">
    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-success-modern alert-modern alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['message']);
        unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger-modern alert-modern alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($_SESSION['error']);
        unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="main-container">
      <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div>
            <h1 class="page-title">Supplier Management</h1>
            <p class="page-subtitle">Manage your supplier database efficiently</p>
          </div>
          <div class="header-search-container position-relative">
            <div class="search-icon-wrapper">
              <i class="fas fa-search"></i>
            </div>
            <input type="text" id="supplierSearch" class="header-search-input" placeholder="Search supplier name, contact, or email..." onkeyup="handleSearch()" value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="action-buttons">
            <button class="btn btn-secondary-modern btn-modern" onclick="window.history.back()">
              <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <?php if (in_array(strtolower($user_type), ['admin', 'purchasing officer', 'purchasing staff', 'purchasingstaff'])): ?>
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
              <div class="stats-number" id="totalSuppliersCount"><?= $total_filtered ?></div>
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
                <?php if (in_array(strtolower($user_type), ['admin', 'purchasing officer', 'purchasing staff', 'purchasingstaff'])): ?>
                  <th><i class="fas fa-cogs me-2"></i>Actions</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody id="supplierTableBody">
              <?= $table_rows ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination Container -->
        <div id="paginationContainer">
          <?= $pagination_html ?>
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
        <div class="modal-header bg-success">
          <h5 class="modal-title" id="viewModalLabel">
            <i class="fas fa-eye me-2"></i>View Supplier Details
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php include '../modals/view_supplier.php'; ?>
        </div>
        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade modal-modern" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
      </div>
    </div>
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
    let searchTimeout;

    function handleSearch() {
      clearTimeout(searchTimeout);
      const searchValue = document.getElementById('supplierSearch').value;
      searchTimeout = setTimeout(function() {
        loadSuppliers(1, searchValue);
      }, 300);
    }

    function loadSuppliers(page = 1, searchTerm = null) {
      if (searchTerm === null) {
        searchTerm = document.getElementById('supplierSearch').value;
      }

      const tbody = document.getElementById('supplierTableBody');
      const pagination = document.getElementById('paginationContainer');
      const totalCountDisplay = document.getElementById('totalSuppliersCount');

      // Show loading state
      tbody.style.opacity = '0.5';

      const url = new URL(window.location.href);
      url.searchParams.set('ajax', '1');
      url.searchParams.set('page', page);
      if (searchTerm) {
        url.searchParams.set('search', searchTerm);
      } else {
        url.searchParams.delete('search');
      }

      fetch(url)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            tbody.innerHTML = data.table_rows;
            pagination.innerHTML = data.pagination;
            if (totalCountDisplay) totalCountDisplay.textContent = data.total_count;

            tbody.style.opacity = '1';

            // Update Browser URL
            const browserUrl = new URL(window.location.href);
            browserUrl.searchParams.set('page', page);
            if (searchTerm) {
              browserUrl.searchParams.set('search', searchTerm);
            } else {
              browserUrl.searchParams.delete('search');
            }
            window.history.pushState({}, '', browserUrl);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          tbody.style.opacity = '1';
          tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error loading suppliers. Please try again.</td></tr>';
        });
    }


    // Action Dropdown Logic
    function toggleActionDropdown(event, container) {
      event.preventDefault();
      event.stopPropagation();

      // Close all other dropdowns first
      document.querySelectorAll('.action-dropdown.active').forEach(dropdown => {
        if (dropdown !== container) {
          dropdown.classList.remove('active');
        }
      });

      container.classList.toggle('active');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
      if (!event.target.closest('.action-dropdown')) {
        document.querySelectorAll('.action-dropdown.active').forEach(dropdown => {
          dropdown.classList.remove('active');
        });
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('add') === '1') {
        const addModalEl = document.getElementById('addModal');
        const addModal = new bootstrap.Modal(addModalEl);
        addModal.show();

        addModalEl.addEventListener('hidden.bs.modal', function() {
          window.location.href = 'canvass_form.php';
        });
      }
    });
  </script>
</body>

</html>