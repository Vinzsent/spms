<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success px-3">
  <a class="navbar-brand" href="#">Supplier Purchase Management System</a>
  <div class="ms-auto d-flex align-items-center">
    <div class="d-flex align-items-center me-3 position-relative">
      <img src="/spms/assets/images/user.png" alt="Profile" class="avatar rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
      <div class="d-none d-md-block text-white">
        <div><?= htmlspecialchars($_SESSION['user']['first_name']); ?></div>
        <span class="badge bg-light text-dark role-badge">Admin</span>
      </div>
      <!--<span class="notification-badge"></span>-->
    </div>
    <button id="darkModeToggle" onclick="toggleDarkMode()" class="btn btn-outline-light me-2" aria-label="Toggle dark mode">🌙</button>
    <a href="/spms/logout.php" class="btn btn-outline-light">🔓 Logout</a>
  </div>
</nav>
<script src="/spms/assets/js/dark-mode.js"></script>