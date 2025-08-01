<?php
$pageTitle = 'Admin Dashboard';
include 'includes/auth.php';
include 'includes/header.php';
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
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
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
  .card-success .card-icon { background: linear-gradient(135deg, #28a745, #20c997); }
  .card-warning .card-icon { background: linear-gradient(135deg, #ffc107, #fd7e14); }
  .card-primary .card-icon { background: linear-gradient(135deg, #007bff, #6610f2); }
  .card-info .card-icon { background: linear-gradient(135deg, #17a2b8, #6f42c1); }
  .card-dark .card-icon { background: linear-gradient(135deg, #343a40, #495057); }
  .card-danger .card-icon { background: linear-gradient(135deg, #dc3545, #e83e8c); }

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
  .menu-card:nth-child(1) { animation-delay: 0.1s; }
  .menu-card:nth-child(2) { animation-delay: 0.2s; }
  .menu-card:nth-child(3) { animation-delay: 0.3s; }
  .menu-card:nth-child(4) { animation-delay: 0.4s; }
  .menu-card:nth-child(5) { animation-delay: 0.5s; }
  .menu-card:nth-child(6) { animation-delay: 0.6s; }
  .menu-card:nth-child(7) { animation-delay: 0.7s; }
  .menu-card:nth-child(8) { animation-delay: 0.8s; }
  .menu-card:nth-child(9) { animation-delay: 0.9s; }
  .menu-card:nth-child(10) { animation-delay: 1.0s; }
</style>

<div class="dashboard-container">
  <div class="dashboard-header">
    <h1>Welcome to Asset Management Dashboard</h1>
    <p>Manage your supply and procurement system efficiently with our comprehensive tools</p>
  </div>

  <div class="menu-grid">
    <!-- Supply Requisition Card -->
    <div class="menu-card card-success">
      <div class="card-icon">
        <i class="fas fa-clipboard-list"></i>
      </div>
      <h3 class="card-title">Supply Requisition</h3>
      <p class="card-description">Request items and track approvals. Managers can approve, reject, or monitor all requests in real-time.</p>
      <a href="pages/supply_request.php" class="card-button">Access Module</a>
    </div>

    <!-- Procurement Card -->
    <div class="menu-card card-warning">
      <div class="card-icon">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <h3 class="card-title">Procurement</h3>
      <p class="card-description">Log purchased items with supplier details, costs, and receipts. Mark items as received when delivered.</p>
      <a href="pages/procurement.php" class="card-button">Access Module</a>
    </div>

    <!-- Received Items Card -->
    <div class="menu-card card-primary">
      <div class="card-icon">
        <i class="fas fa-box-open"></i>
      </div>
      <h3 class="card-title">Received Items</h3>
      <p class="card-description">Record and track new supplier transactions. Manage incoming inventory and update stock levels.</p>
      <a href="pages/transaction_list.php" class="card-button">Access Module</a>
    </div>

    <!-- Inventory Card -->
    <div class="menu-card card-info">
      <div class="card-icon">
        <i class="fas fa-warehouse"></i>
      </div>
      <h3 class="card-title">Inventory Management</h3>
      <p class="card-description">Track real-time stock levels with detailed logs. Get alerts for low inventory and manage supplies.</p>
      <a href="pages/inventory.php" class="card-button">Access Module</a>
    </div>

    <!-- Asset Registration Card -->
    <div class="menu-card card-dark">
      <div class="card-icon">
        <i class="fas fa-tags"></i>
      </div>
      <h3 class="card-title">Asset Registration</h3>
      <p class="card-description">Register assets with specifications, values, and documents. Generate tags or barcodes automatically.</p>
      <a href="pages/supply_request.php" class="card-button">Access Module</a>
    </div>

    <!-- Assignment Card -->
    <div class="menu-card card-primary">
      <div class="card-icon">
        <i class="fas fa-user-check"></i>
      </div>
      <h3 class="card-title">Assignment & Issuance</h3>
      <p class="card-description">Handle asset assignments and supply issuance with quantity tracking and user management.</p>
      <a href="pages/supply_request.php" class="card-button">Access Module</a>
    </div>

    <!-- Maintenance Card -->
    <div class="menu-card card-success">
      <div class="card-icon">
        <i class="fas fa-tools"></i>
      </div>
      <h3 class="card-title">Maintenance</h3>
      <p class="card-description">Schedule and record asset maintenance with complete service history and cost tracking.</p>
      <a href="pages/supply_request.php" class="card-button">Access Module</a>
    </div>

    <!-- Audit Card -->
    <div class="menu-card card-warning">
      <div class="card-icon">
        <i class="fas fa-search"></i>
      </div>
      <h3 class="card-title">Audit</h3>
      <p class="card-description">Conduct inventory audits by comparing system data with physical counts for accuracy.</p>
      <a href="pages/supply_request.php" class="card-button">Access Module</a>
    </div>

    <!-- Disposal Card -->
    <div class="menu-card card-info">
      <div class="card-icon">
        <i class="fas fa-trash-alt"></i>
      </div>
      <h3 class="card-title">Disposal</h3>
      <p class="card-description">Manage disposal of obsolete items with approval workflows and reason documentation.</p>
      <a href="pages/supply_request.php" class="card-button">Access Module</a>
    </div>

    <!-- Reports Card -->
    <div class="menu-card card-dark">
      <div class="card-icon">
        <i class="fas fa-chart-bar"></i>
      </div>
      <h3 class="card-title">Reports</h3>
      <p class="card-description">Generate comprehensive reports on requisitions, inventory, maintenance, and disposals.</p>
      <a href="pages/supply_request.php" class="card-button">Access Module</a>
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
function showPasswordModal() {
    document.getElementById('passwordModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function hidePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('adminPassword').value = '';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('passwordModal');
    if (event.target == modal) {
        hidePasswordModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hidePasswordModal();
    }
});

// Add smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>