<?php include 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      transition: background 0.3s, color 0.3s;
    }
    .dark-mode {
      background-color: #121212;
      color: #ffffff;
    }
    .dark-mode .card {
      background-color: #1f1f1f;
      color: #fff;
    }
    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 10px;
    }
    .role-badge {
      font-size: 0.75rem;
    }
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: red;
      color: white;
      border-radius: 50%;
      font-size: 0.7rem;
      padding: 2px 6px;
    }
  </style>
</head>
<body>
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

  <!-- Dark Mode Toggle -->
  <script>
    function toggleDarkMode() {
      document.body.classList.toggle('dark-mode');
    }
  </script>
</body>
</html>
