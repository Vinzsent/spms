<?php
$pageTitle = 'DARTS';
include 'includes/auth.php';
include 'includes/header.php';

// Get user type from session
$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
?>
<?php include('./includes/navbar.php'); ?>

<style>
  :root {
    --primary-color: #073b1d;
    --primary-light: #0a4f28;
    --primary-dark: #052915;
    --accent-color: #28a745;
    --text-light: #ffffff;
    --text-dark: #333333;
    --card-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    --card-hover-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    --border-radius: 12px;
    --transition: all 0.3s ease;
  }

  body {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .dashboard-container {
    padding: 2rem 0;
    margin-top: 80px;
  }

  .dashboard-header {
    text-align: center;
    margin-bottom: 3rem;
    color: var(--text-light);
  }

  .dashboard-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  }

  .dashboard-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
  }

  .menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .menu-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    border: none;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
  }

  .menu-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--accent-color), #17a2b8);
    transform: scaleX(0);
    transition: var(--transition);
  }

  .menu-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--card-hover-shadow);
  }

  .menu-card:hover::before {
    transform: scaleX(1);
  }

  .menu-card .card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
    color: white;
  }

  .menu-card .card-title {
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text-dark);
    line-height: 1.3;
  }

  .menu-card .card-description {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    font-size: 0.95rem;
  }

  .menu-card .card-button {
    background: #fd7e14;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: var(--transition);
    box-shadow: 0 4px 15px rgba(7, 59, 29, 0.3);
  }

  .menu-card .card-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(7, 59, 29, 0.4);
    color: white;
    text-decoration: none;
  }

  /* Card color themes */
  .card-success .card-icon {
    background: linear-gradient(135deg, #28a745, #20c997);
  }

  .card-warning .card-icon {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
  }

  .card-primary .card-icon {
    background: linear-gradient(135deg, #007bff, #6610f2);
  }

  .card-info .card-icon {
    background: linear-gradient(135deg, #17a2b8, #6f42c1);
  }

  .card-dark .card-icon {
    background: linear-gradient(135deg, #343a40, #495057);
  }

  .card-danger .card-icon {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
  }

  /* Modal Styles */
  .modal-custom {
    display: none;
    position: fixed;
    z-index: 1050;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
  }

  .modal-content-custom {
    background: white;
    margin: 5% auto;
    padding: 2.5rem;
    border-radius: var(--border-radius);
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border: none;
    position: relative;
  }

  .modal-content-custom h4 {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 1rem;
  }

  .modal-content-custom .form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: var(--transition);
  }

  .modal-content-custom .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(7, 59, 29, 0.25);
  }

  .btn-primary-custom {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border: none;
    border-radius: 25px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: var(--transition);
  }

  .btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(7, 59, 29, 0.4);
  }

  /* Request Type Modal Styles */
  .request-type-modal {
    max-width: 600px;
    padding: 2rem;
  }

  .request-type-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    margin: 0 auto 1.5rem;
  }

  .request-type-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .request-option {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    cursor: pointer;
    transition: var(--transition);
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
  }

  .request-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--accent-color), #17a2b8);
    transform: scaleX(0);
    transition: var(--transition);
  }

  .request-option:hover {
    border-color: var(--primary-color);
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(7, 59, 29, 0.15);
  }

  .request-option:hover::before {
    transform: scaleX(1);
  }

  .request-option:hover .option-icon {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    transform: scale(1.1);
  }

  .request-option:hover .option-arrow {
    color: var(--primary-color);
    transform: translateX(5px);
  }

  .option-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #6c757d, #495057);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-right: 1rem;
    transition: var(--transition);
    flex-shrink: 0;
  }

  .option-content {
    flex: 1;
  }

  .option-content h5 {
    color: var(--text-dark);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
  }

  .option-content p {
    color: #6c757d;
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.4;
  }

  .option-arrow {
    color: #6c757d;
    font-size: 1.2rem;
    transition: var(--transition);
  }

  .modal-footer-custom {
    text-align: center;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
  }

  /* Responsive design for request type modal */
  @media (max-width: 768px) {
    .request-type-modal {
      margin: 10% auto;
      padding: 1.5rem;
    }

    .request-option {
      padding: 1rem;
    }

    .option-icon {
      width: 50px;
      height: 50px;
      font-size: 1.2rem;
    }

    .option-content h5 {
      font-size: 1rem;
    }

    .option-content p {
      font-size: 0.85rem;
    }
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .dashboard-header h1 {
      font-size: 2rem;
    }

    .menu-grid {
      grid-template-columns: 1fr;
      gap: 1rem;
      padding: 0 0.5rem;
    }

    .menu-card {
      padding: 1.5rem;
    }

    .menu-card .card-title {
      font-size: 1.2rem;
    }
  }

  /* Loading animation for cards */
  .menu-card {
    animation: fadeInUp 0.6s ease-out;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Stagger animation for cards */
  .menu-card:nth-child(1) {
    animation-delay: 0.1s;
  }

  .menu-card:nth-child(2) {
    animation-delay: 0.2s;
  }

  .menu-card:nth-child(3) {
    animation-delay: 0.3s;
  }

  .menu-card:nth-child(4) {
    animation-delay: 0.4s;
  }

  .menu-card:nth-child(5) {
    animation-delay: 0.5s;
  }

  .menu-card:nth-child(6) {
    animation-delay: 0.6s;
  }

  .menu-card:nth-child(7) {
    animation-delay: 0.7s;
  }

  .menu-card:nth-child(8) {
    animation-delay: 0.8s;
  }

  .menu-card:nth-child(9) {
    animation-delay: 0.9s;
  }

  .menu-card:nth-child(10) {
    animation-delay: 1.0s;
  }

  /* Dark mode overrides specific to this dashboard */
  [data-bs-theme="dark"] body {
    background: linear-gradient(135deg, #121416 0%, #0d1113 100%);
  }

  [data-bs-theme="dark"] .menu-card {
    background: rgba(44, 48, 52, 0.95);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
  }

  [data-bs-theme="dark"] .menu-card .card-title {
    color: var(--bs-body-color);
  }

  [data-bs-theme="dark"] .menu-card .card-description {
    color: var(--bs-secondary-color);
  }

  [data-bs-theme="dark"] .modal-content-custom {
    background-color: #2c3034;
    color: #dee2e6;
    border: 1px solid #495057;
  }

  [data-bs-theme="dark"] .modal-content-custom h4 {
    color: #dee2e6;
  }

  /* Ensure muted text inside modal is readable in dark mode */
  [data-bs-theme="dark"] .modal-content-custom .text-muted {
    color: var(--bs-secondary-color) !important;
  }

  [data-bs-theme="dark"] .modal-content-custom .form-control {
    background-color: #2c3034;
    color: #dee2e6;
    border-color: #495057;
  }

  [data-bs-theme="dark"] .request-option {
    background: #2c3034;
    border-color: #495057;
  }

  [data-bs-theme="dark"] .request-option:hover {
    background: #3d4146;
    border-color: #80bdff;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
  }

  [data-bs-theme="dark"] .option-arrow {
    color: #adb5bd;
  }

  [data-bs-theme="dark"] .option-content p {
    color: #adb5bd;
  }

  /* Make request option titles readable in dark mode */
  [data-bs-theme="dark"] .option-content h5 {
    color: var(--bs-body-color);
  }
  .modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  animation: fadeIn 0.5s ease-in-out;
}

.modal-content {
  background: #ffffff;
  padding: 30px 40px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  position: relative;
  max-width: 400px;
  width: 90%;
  animation: slideUp 0.4s ease-out;
}

.modal-icon {
  font-size: 40px;
  color: #28a745;
  margin-bottom: 15px;
}

.modal-content h2 {
  margin: 0;
  font-size: 24px;
  color: #333;
}

.modal-content p {
  margin-top: 10px;
  font-size: 16px;
  color: #555;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 16px;
  font-size: 22px;
  color: #888;
  cursor: pointer;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

</style>

<div class="dashboard-container">
  <div class="dashboard-header">
    <h1>Welcome to DARTS</h1>
    <p>Manage your assets efficiently with our comprehensive tools</p>
  </div>

  <div class="menu-grid">
    <?php if (strtolower($user_type) === 'faculty' || strtolower($user_type) === 'staff'): ?>
      <!-- Supply Requisition Card ONLY for Faculty -->
      <div class="menu-card">
        <div class="card-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
          <i class="fas fa-clipboard-list"></i>
        </div>
        <h3 class="card-title">Supply Requisition</h3>
        <p class="card-description">Request items and track approvals. Managers can approve, reject, or monitor all requests in real-time.</p>
        <button onclick="showRequestTypeModal()" class="card-button">Access</button>
      </div>

      <div class="menu-card">
        <div class="card-icon" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
          <i class="fas fa-boxes"></i>
        </div>
        <h3 class="card-title">Property Requisition</h3>
        <p class="card-description">Request property items and track approvals. Choose between consumable and non-consumable items.</p>
        <button onclick="showPropertyRequestTypeModal()" class="card-button">Access</button>
      </div>

    <?php elseif (strtolower($user_type) === 'immediate head'): ?>
      <!-- Supply Requisition Card ONLY for Immediate Head -->
      <!-- Assignment Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <h3 class="card-title">Assignment & Issuance</h3>
        <p class="card-description">Handle asset assignments and supply issuance with quantity tracking and user supply status.</p>
        <a href="pages/issuance.php" class="card-button">Access</a>
      </div>

      <?php elseif (strtolower($user_type) === 'accounting officer'): ?>
      <!-- Asset Registration Card ONLY for Accounting Officer -->
      <!-- Asset Registration Card -->
      <div class="menu-card card-dark">
        <div class="card-icon">
          <i class="fas fa-tags"></i>
        </div>
        <h3 class="card-title">Asset Registration</h3>
        <p class="card-description">Register assets with specifications, values, and documents. Generate tags or barcodes automatically.</p>
        <a href="pages/assets.php" class="card-button">Access</a>
      </div>

      <!-- Procurement Card -->
      <div class="menu-card card-warning">
        <div class="card-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <h3 class="card-title">Procurement</h3>
        <p class="card-description">Log purchased items with supplier details, costs, and receipts. Mark items as received when delivered.</p>
        <a href="pages/procurement.php" class="card-button">Access</a>
      </div>

    <?php elseif (strtolower($user_type) === 'school president'): ?>
      <!-- Supply Requisition Card ONLY for School President -->
      <!-- Assignment Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <h3 class="card-title">Assignment & Issuance</h3>
        <p class="card-description">Handle asset assignments and supply issuance with quantity tracking and user supply status.</p>
        <a href="pages/issuance.php" class="card-button">Access</a>
      </div>

    <?php elseif (strtolower($user_type) === 'vp for finance & administration'): ?>
      <!-- Supply Requisition Card ONLY for School President -->
      <!-- Assignment Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <h3 class="card-title">Assignment & Issuance</h3>
        <p class="card-description">Handle asset assignments and supply issuance with quantity tracking and user supply status.</p>
        <a href="pages/issuance.php" class="card-button">Access</a>
      </div>

      <?php elseif (strtolower($user_type) === 'purchasing officer'): ?>
      <!-- Supply Requisition Card ONLY for School President -->

         <!-- Assignment Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <h3 class="card-title">Assignment & Issuance</h3>
        <p class="card-description">Handle asset assignments and supply issuance with quantity tracking and user supply status.</p>
        <a href="pages/issuance.php" class="card-button">Access</a>
      </div>

      <!-- Procurement Card -->
      <div class="menu-card card-warning">
        <div class="card-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <h3 class="card-title">Procurement</h3>
        <p class="card-description">Log purchased items with supplier details, costs, and receipts. Mark items as received when delivered.</p>
        <a href="pages/procurement.php" class="card-button">Access</a>
      </div>

      <?php elseif (strtolower($user_type) === 'property custodian'): ?>
      <!-- Supply Requisition Card ONLY for School President -->

         <!-- Assignment Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <h3 class="card-title">Assignment & Issuance</h3>
        <p class="card-description">Handle asset assignments and supply issuance with quantity tracking and user supply status.</p>
        <a href="pages/issuance.php" class="card-button">Access</a>
      </div>

      <!-- Inventory Card -->
      <div class="menu-card card-info">
        <div class="card-icon">
          <i class="fas fa-warehouse"></i>
        </div>
        <h3 class="card-title">Property Inventory Management</h3>
        <p class="card-description">Track real-time stock levels with detailed logs. Get alerts for low inventory and manage supplies.</p>
        <a href="pages/property_inventory.php" class="card-button">Access</a>
      </div>

    <?php elseif (strtolower($user_type) === 'supply in-charge'): ?>
      <!-- Supply Requisition Card ONLY for School President -->

      <!-- Assignment Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <h3 class="card-title">Assignment & Issuance</h3>
        <p class="card-description">Handle asset assignments and supply issuance with quantity tracking and user supply status.</p>
        <a href="pages/issuance.php" class="card-button">Access</a>
      </div>      
      <!-- Inventory Card -->
      <div class="menu-card card-info">
        <div class="card-icon">
          <i class="fas fa-warehouse"></i>
        </div>
        <h3 class="card-title">Supply Inventory Management</h3>
        <p class="card-description">Track real-time stock levels with detailed logs. Get alerts for low inventory and manage supplies.</p>
        <a href="pages/inventory.php" class="card-button">Access</a>
      </div>

    <?php else: ?>
      <!-- All other cards for non-Faculty users -->
      <!-- Supply Requisition Card -->
      <div class="menu-card card-success">
        <div class="card-icon">
          <i class="fas fa-clipboard-list"></i>
        </div>
        <h3 class="card-title">Supply Requisition</h3>
        <p class="card-description">Request items and track approvals. Managers can approve, reject, or monitor all requests in real-time.</p>
        <button onclick="showRequestTypeModal()" class="card-button">Access</button>
      </div>

    <!-- Property Requisition Card -->
      <div class="menu-card">
        <div class="card-icon" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
          <i class="fas fa-boxes"></i>
        </div>
        <h3 class="card-title">Property Requisition</h3>
        <p class="card-description">Request property items and track approvals. Choose between consumable and non-consumable items.</p>
        <button onclick="showPropertyRequestTypeModal()" class="card-button">Access</button>
      </div>

      <!-- Assignment Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-user-check"></i>
        </div>
        <h3 class="card-title">Assignment & Issuance</h3>
        <p class="card-description">Handle asset assignments and supply issuance with quantity tracking and user supply status.</p>
        <a href="pages/issuance.php" class="card-button">Access</a>
      </div>
      <!-- Procurement Card -->
      <div class="menu-card card-warning">
        <div class="card-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <h3 class="card-title">Procurement</h3>
        <p class="card-description">Log purchased items with supplier details, costs, and receipts. Mark items as received when delivered.</p>
        <a href="pages/procurement.php" class="card-button">Access</a>
      </div>
      <!-- Received Items Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-box-open"></i>
        </div>
        <h3 class="card-title">Received Items</h3>
        <p class="card-description">Record and track new supplier transactions. Manage incoming inventory and update stock levels.</p>
        <a href="pages/transaction_list.php" class="card-button">Access</a>
      </div>
      <!-- Inventory Card -->
      <div class="menu-card card-info">
        <div class="card-icon">
          <i class="fas fa-warehouse"></i>
        </div>
        <h3 class="card-title">Supply Inventory Management</h3>
        <p class="card-description">Track real-time stock levels with detailed logs. Get alerts for low inventory and manage consumables supplies.</p>
        <a href="pages/inventory.php" class="card-button">Access</a>
      </div>
      <!-- Property Inventory Card -->
      <div class="menu-card card-info">
        <div class="card-icon">
          <i class="fas fa-warehouse"></i>
        </div>
        <h3 class="card-title">Property Inventory Management</h3>
        <p class="card-description">Track real-time stock levels with detailed logs. Get alerts for low inventory and manage property supplies.</p>
        <a href="pages/property_inventory.php" class="card-button">Access</a>
      </div>
      <!-- Asset Registration Card -->
      <div class="menu-card card-dark">
        <div class="card-icon">
          <i class="fas fa-tags"></i>
        </div>
        <h3 class="card-title">Asset Registration</h3>
        <p class="card-description">Register assets with specifications, values, and documents. Generate tags or barcodes automatically.</p>
        <a href="pages/assets.php" class="card-button">Access</a>
      </div>
      <!-- Maintenance Card -->
      <div class="menu-card card-success">
        <div class="card-icon">
          <i class="fas fa-tools"></i>
        </div>
        <h3 class="card-title">Maintenance</h3>
        <p class="card-description">Schedule and record asset maintenance with complete service history and cost tracking.</p>
        <a href="pages/supply_request.php" class="card-button">Access</a>
      </div>
      <!-- Audit Card -->
      <div class="menu-card card-warning">
        <div class="card-icon">
          <i class="fas fa-search"></i>
        </div>
        <h3 class="card-title">Audit</h3>
        <p class="card-description">Conduct inventory audits by comparing system data with physical counts for accuracy.</p>
        <a href="pages/supply_request.php" class="card-button">Access</a>
      </div>
      <!-- Disposal Card -->
      <div class="menu-card card-info">
        <div class="card-icon">
          <i class="fas fa-trash-alt"></i>
        </div>
        <h3 class="card-title">Disposal</h3>
        <p class="card-description">Manage disposal of obsolete items with approval workflows and reason documentation.</p>
        <a href="pages/supply_request.php" class="card-button">Access</a>
      </div>
      <!-- Reports Card -->
      <div class="menu-card card-dark">
        <div class="card-icon">
          <i class="fas fa-chart-bar"></i>
        </div>
        <h3 class="card-title">Reports</h3>
        <p class="card-description">Generate comprehensive reports on requisitions, inventory, maintenance, and disposals.</p>
        <a href="pages/supply_request.php" class="card-button">Access</a>
      </div>
      <!-- Notifications Card -->
      <div class="menu-card card-primary">
        <div class="card-icon">
          <i class="fas fa-bell"></i>
        </div>
        <h3 class="card-title">Notifications</h3>
        <p class="card-description">View and manage all your notifications for supply requests, approvals, and issuances.</p>
        <a href="pages/notifications.php" class="card-button">View Notifications</a>
      </div>
      <!-- Settings Card -->
      <div class="menu-card card-danger">
        <div class="card-icon">
          <i class="fas fa-cog"></i>
        </div>
        <h3 class="card-title">System Settings</h3>
        <p class="card-description">Manage system settings and user preferences. Administrative access required.</p>
        <button onclick="showPasswordModal()" class="card-button">Access Settings</button>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($_SESSION['show_login_modal'])): ?>
<div id="loginSuccessModal" class="modal-overlay">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <i class="fas fa-check-circle modal-icon"></i>
    <h2>Welcome, <?= htmlspecialchars(explode(' ', $_SESSION['title'])[0]) ?> <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>!</h2>
    <p class="text-center">You have successfully logged in to DCC-DARTS.</p>
    <button class="btn btn-success" onclick="closeModal()">Close</button>
  </div>
</div>
<?php unset($_SESSION['show_login_modal']); ?>
<?php endif; ?>


<!-- Request Type Selection Modal -->
<div id="requestTypeModal" class="modal-custom">
  <div class="modal-content-custom request-type-modal">
    <div class="text-center mb-4">
      <div class="request-type-icon mx-auto mb-3">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <h4>Select Request Type</h4>
      <p class="text-muted">Choose the type of request you want to submit</p>
    </div>
    
    <div class="request-type-options">
      <div class="request-option" onclick="selectSupplyRequestType('consumables')">
        <div class="option-icon">
          <i class="fas fa-building"></i>
        </div>
        <div class="option-content">
          
        <h5>Consumables</h5>
          <p>Request, transfer, and issue for supplies, materials, and other consumable items</p>
        </div>
        <div class="option-arrow">
          <i class="fas fa-chevron-right"></i>
        </div>
      </div>
      
      <div class="request-option" onclick="selectSupplyRequestType('nonconsumables')">
        <div class="option-icon">
          <i class="fas fa-box-open"></i>
        </div>
        <div class="option-content">
        <h5>Non Consumables</h5>
        <p>Request, transfer, and issue for equipment, furniture, and other durable assets</p>
        </div>
        <div class="option-arrow">
          <i class="fas fa-chevron-right"></i>
        </div>
      </div>
    </div>
    
    <div class="modal-footer-custom">
      <button type="button" class="btn btn-outline-secondary" onclick="hideRequestTypeModal()">
        <i class="fas fa-times me-1"></i>Cancel
      </button>
    </div>
  </div>
</div>


<!-- Property Request Type Selection Modal -->
<div id="propertyRequestTypeModal" class="modal-custom">
  <div class="modal-content-custom request-type-modal">
    <div class="text-center mb-4">
      <div class="request-type-icon mx-auto mb-3">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <h4>Select Request Type</h4>
      <p class="text-muted">Choose the type of request you want to submit</p>
    </div>
    
    <div class="request-type-options">
      <div class="request-option" onclick="selectRequestType('consumables')">
        <div class="option-icon">
          <i class="fas fa-building"></i>
        </div>
        <div class="option-content">
          <h5>Consumables</h5>
          <p>Request for equipment, furniture, and other durable assets</p>
        </div>
        <div class="option-arrow">
          <i class="fas fa-chevron-right"></i>
        </div>
      </div>
      
      <div class="request-option" onclick="selectRequestType('nonconsumables')">
        <div class="option-icon">
          <i class="fas fa-box-open"></i>
        </div>
        <div class="option-content">
          <h5>Non-Consumables</h5>
          <p>Request for supplies, materials, and other consumable items</p>
        </div>
        <div class="option-arrow">
          <i class="fas fa-chevron-right"></i>
        </div>
      </div>
    </div>
    
    <div class="modal-footer-custom">
      <button type="button" class="btn btn-outline-secondary" onclick="hidePropertyRequestTypeModal()">
        <i class="fas fa-times me-1"></i>Cancel
      </button>
    </div>
  </div>
</div>

<!-- Password Modal -->
<div id="passwordModal" class="modal-custom">
  <div class="modal-content-custom">
    <div class="text-center mb-4">
      <div class="card-icon mx-auto mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #dc3545, #e83e8c);">
        <i class="fas fa-lock" style="font-size: 2rem;"></i>
      </div>
      <h4>Admin Access Required</h4>
      <p class="text-muted">Please enter the admin password to access system settings</p>
    </div>
    <form id="passwordForm" method="POST" action="actions/verify_admin_password.php">
      <div class="mb-4">
        <label for="adminPassword" class="form-label fw-bold">Admin Password:</label>
        <input type="password" class="form-control" id="adminPassword" name="admin_password" required placeholder="Enter admin password">
      </div>
      <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-secondary" onclick="hidePasswordModal()">Cancel</button>
        <button type="submit" class="btn btn-primary-custom text-white">Access Settings</button>
      </div>
    </form>
  </div>
</div>

<script>
function closeModal() {
  const modal = document.getElementById('loginSuccessModal');
  if (modal) modal.style.display = 'none';
}

// Auto-close after 5 seconds
setTimeout(closeModal, 5000);

// Property Request Type Modal Functions
function showPropertyRequestTypeModal() {
  const modal = document.getElementById('propertyRequestTypeModal');
  if (modal) {
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }
}

function hidePropertyRequestTypeModal() {
  const modal = document.getElementById('propertyRequestTypeModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
  }
}

function selectRequestType(type) {
  // Store the selected type in sessionStorage
  sessionStorage.setItem('selectedRequestType', type);
  
  // Redirect to the appropriate page based on selection
  if (type === 'property') {
    window.location.href = 'supply_request.php?type=consumables';
  } else {
    window.location.href = 'supply_request.php?type=non-consumables';
  }
}

// Close modal when clicking outside the modal content
window.addEventListener('click', function(event) {
  const modal = document.getElementById('propertyRequestTypeModal');
  if (event.target === modal) {
    hidePropertyRequestTypeModal();
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    hidePropertyRequestTypeModal();
  }
});
</script>

<script>
  // Request Type Modal Functions
  function showRequestTypeModal() {
    document.getElementById('requestTypeModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function hideRequestTypeModal() {
    document.getElementById('requestTypeModal').style.display = 'none';
    document.body.style.overflow = 'auto';
  }
  
  // Supply: handle request type selection and redirect
  function selectSupplyRequestType(type) {
    // Persist selection
    sessionStorage.setItem('selectedRequestType', type);
    // Redirect to supply request page with selected type
    window.location.href = 'pages/supply_request.php?type=' + encodeURIComponent(type);
  }

  function selectRequestType(type) {
    // Store the selected request type in sessionStorage
    sessionStorage.setItem('selectedRequestType', type);
    
    // Redirect to supply request page with the selected type
    window.location.href = 'pages/supply_request.php?type=' + type;
  }

  // Password Modal Functions
  function showPasswordModal() {
    document.getElementById('passwordModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function hidePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('adminPassword').value = '';
    document.body.style.overflow = 'auto';
  }

  // Close modals when clicking outside
  window.onclick = function(event) {
    var requestModal = document.getElementById('propertyRequestTypeModal');
    var passwordModal = document.getElementById('passwordModal');
    
    if (event.target == requestModal) {
      hidePropertyRequestTypeModal();
    }
    if (event.target == passwordModal) {
      hidePasswordModal();
    }
  }

  // Close modals with Escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      hidePropertyRequestTypeModal();
      hidePasswordModal();
    }
  });

   // Request Type Modal Functions
   function showPropertyRequestTypeModal() {
    document.getElementById('propertyRequestTypeModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function hidePropertyRequestTypeModal() {
    document.getElementById('propertyRequestTypeModal').style.display = 'none';
    document.body.style.overflow = 'auto';
  }

  function selectRequestType(type) {
    // Store the selected request type in sessionStorage
    sessionStorage.setItem('selectedRequestType', type);
    
    // Redirect to supply request page with the selected type
    window.location.href = 'pages/property_request.php?type=' + type;
  }

  // Password Modal Functions
  function showPasswordModal() {
    document.getElementById('passwordModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function hidePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('adminPassword').value = '';
    document.body.style.overflow = 'auto';
  }

  // Close modals when clicking outside
  window.onclick = function(event) {
    var requestModal = document.getElementById('requestTypeModal');
    var passwordModal = document.getElementById('passwordModal');
    
    if (event.target == requestModal) {
      hideRequestTypeModal();
    }
    if (event.target == passwordModal) {
      hidePasswordModal();
    }
  }

  // Close modals with Escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      hideRequestTypeModal();
      hidePasswordModal();
    }
  });

  // Add smooth scrolling
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({
        behavior: 'smooth'
      });
    });
  });

  // Add click animation to request options
  document.addEventListener('DOMContentLoaded', function() {
    const requestOptions = document.querySelectorAll('.request-option');
    
    requestOptions.forEach(option => {
      option.addEventListener('click', function() {
        // Add click animation
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
          this.style.transform = '';
        }, 150);
      });
    });
  });
</script>

<?php include 'includes/footer.php'; ?>