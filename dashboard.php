<?php 
$pageTitle = 'Admin Dashboard';
include 'includes/auth.php'; 
include 'includes/header.php'; 
?>
<?php include('./includes/navbar.php'); ?>
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
          <h5 class="card-title text-center">Add Transaction</h5>
          <p class="card-text">Record new supplier transactions.</p>
          <a href="pages/transaction_list.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>
    <!-- Settings Card -->
    <div class="col-md-4">
      <div class="card text-white bg-danger">
        <div class="card-body">
          <h5 class="card-title text-center">Settings</h5>
          <p class="card-text">Manage system settings and user preferences.</p>
          <a href="pages/users.php" class="btn btn-light">Go</a>
        </div>
      </div>
    </div>
  </div>
</div>

  <?php include 'includes/footer.php'; ?>
