<?php
session_start();
include 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Debug: Log the attempted username
  error_log('Login attempt with username: ' . $username);

  $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  // Debug: Log query result count
  error_log('Query returned ' . $result->num_rows . ' rows for username: ' . $username);

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      // Store user data in session with proper keys
      $_SESSION['user'] = $user;
      $_SESSION['user_id'] = $user['id']; // Store user ID separately
      $_SESSION['id'] = $user['id']; // Alternative key for compatibility
      $_SESSION['user_type'] = $user['user_type'];
      $_SESSION['name'] = $user['name'] ?? $user['first_name'] . ' ' . $user['last_name'] ?? $user['username'];
      $_SESSION['title'] = $user['title'];
      $_SESSION['username'] = $user['username'];

      // Set flag to show login success modal on dashboard
      $_SESSION['show_login_modal'] = true;

      // Debug: Log session data
      error_log('Login successful - User ID: ' . $user['id'] . ', User Type: ' . $user['user_type']);

      header("Location: dashboard.php");
      exit;
    } else {
      $error = "Incorrect password.";
      error_log('Password verification failed for username: ' . $username);
    }
  } else {
    $error = "User not found.";
    error_log('User not found for username: ' . $username);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DCC-DARTS - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="assets/css/dark-mode.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #073b1d;
      --secondary-color: #0d6efd;
      --accent-color: #198754;
      --text-dark: #2c3e50;
      --text-light: #6c757d;
      --bg-light: #f8f9fa;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      --border-radius: 15px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #073b1d 0%, #0a4d2a 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .login-container {
      width: 100%;
      max-width: 980px;
      animation: fadeInUp 0.8s ease-out;
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

    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .login-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, #0a4d2a 100%);
      color: white;
      padding: 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .login-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    .login-header h1 {
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      position: relative;
      z-index: 1;
    }

    .login-header p {
      font-size: 1rem;
      opacity: 0.9;
      position: relative;
      z-index: 1;
    }

    .login-body {
      padding: 2.5rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }

    .form-label {
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
      display: block;
    }

    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 10px;
      padding: 0.75rem 1rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: #fff;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(7, 59, 29, 0.25);
      outline: none;
    }

    .input-group {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-light);
      z-index: 2;
    }

    .form-control.with-icon {
      padding-left: 3rem;
    }

    .btn-login {
      background: linear-gradient(135deg, var(--primary-color) 0%, #0a4d2a 100%);
      border: none;
      border-radius: 10px;
      padding: 0.875rem 2rem;
      font-size: 1.1rem;
      font-weight: 600;
      color: white;
      transition: all 0.3s ease;
      width: 100%;
      position: relative;
      overflow: hidden;
    }

    .btn-login::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .btn-login:hover::before {
      left: 100%;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(7, 59, 29, 0.3);
    }

    .alert {
      border-radius: 10px;
      border: none;
      padding: 1rem;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }

    .alert-danger {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
    }

    .system-info {
      text-align: center;
      margin-top: 2rem;
      padding: 1.5rem;
      background: rgba(7, 59, 29, 0.05);
      border-radius: 10px;
      border: 1px solid rgba(7, 59, 29, 0.1);
    }

    .system-info h5 {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .system-info p {
      color: var(--text-light);
      font-size: 0.9rem;
      margin-bottom: 0;
    }

    .features {
      display: flex;
      justify-content: space-around;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .feature {
      text-align: center;
      flex: 1;
    }

    .feature i {
      font-size: 1.5rem;
      color: var(--primary-color);
      margin-bottom: 0.5rem;
    }

    .feature span {
      display: block;
      font-size: 0.8rem;
      color: var(--text-light);
      font-weight: 500;
    }

    @media (max-width: 576px) {
      .login-container {
        max-width: 100%;
      }

      .login-body {
        padding: 2rem 1.5rem;
      }

      .login-header {
        padding: 1.5rem;
      }

      .login-header h1 {
        font-size: 1.8rem;
      }
    }

    .floating-shapes {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: -1;
    }

    .shape {
      position: absolute;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .shape:nth-child(1) {
      width: 80px;
      height: 80px;
      top: 20%;
      left: 10%;
      animation-delay: 0s;
    }

    .shape:nth-child(2) {
      width: 120px;
      height: 120px;
      top: 60%;
      right: 10%;
      animation-delay: 2s;
    }

    .shape:nth-child(3) {
      width: 60px;
      height: 60px;
      bottom: 20%;
      left: 20%;
      animation-delay: 4s;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0px) rotate(0deg);
      }

      50% {
        transform: translateY(-20px) rotate(180deg);
      }
    }
  </style>
  <!-- Design refresh overrides (preserving color scheme) -->
  <style>
    /* two-column layout on larger screens */
    .login-card {
      display: grid;
      grid-template-columns: 0.95fr 1.05fr;
      min-height: 520px;
    }

    @media (max-width: 768px) {
      .login-card {
        display: block;
      }

      .login-container {
        max-width: 100%;
      }
    }

    /* left panel embellishments */
    .login-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100%;
      padding: 2.5rem 2rem;
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
    }

    .brand-badge {
      width: 84px;
      height: 84px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      margin: 0 0 0.75rem 0;
      background: rgba(255, 255, 255, 0.12);
      border: 2px solid rgba(255, 255, 255, 0.25);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) inset;
    }

    .brand-badge i {
      font-size: 1.5rem;
      color: #fff;
    }

    .login-body {
      position: relative;
    }

    .helper-links {
      display: flex;
      gap: 0.75rem;
      align-items: center;
      margin: 0.5rem 0 1.25rem;
    }

    .helper-links .form-check {
      margin: 0;
    }

    .helper-links a {
      margin-left: auto;
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 600;
      white-space: nowrap;
    }

    .helper-links a:hover {
      text-decoration: underline;
    }

    /* password toggle */
    .toggle-password {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      z-index: 2;
      background: none;
      border: 0;
      color: var(--text-light);
      padding: 0;
    }

    .toggle-password:hover {
      color: var(--primary-color);
    }

    /* theme toggle */
    .theme-toggle {
      position: fixed;
      top: 16px;
      right: 16px;
      z-index: 10;
      border-radius: 999px;
      box-shadow: var(--shadow);
    }

    .datetime-container {
      text-align: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f4f4;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      width: 250px;
      margin: 0 auto;
    }

    #date {
      font-size: 1.2em;
      margin-bottom: 10px;
      color: #333;
    }

    #time {
      font-size: 2em;
      font-weight: bold;
      background: linear-gradient(135deg, #1a5f3c, #2d7a4d);;
    }
  </style>
</head>

<body>
  <button id="themeToggle" class="theme-toggle btn btn-light btn-sm" aria-label="Toggle theme">
    <i class="fas fa-moon"></i>
  </button>
  <!-- Floating Background Shapes -->
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="brand-badge"><i class="fas fa-shield-alt"></i></div>
        <h1><span class="visually-hidden">DCC-DARTS</span>DCC-DARTS</h1>
        <p>Digital Asset Repository and Tracking System</p>

        <div class="datetime-container">
          <div id="date"></div>
          <div id="time"></div>
        </div>

      </div>

      <div class="login-body">
        <?php if ($error): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
          <div class="form-group">
            <label for="username" class="form-label">
              <i class="fas fa-envelope me-2"></i>Username
            </label>
            <div class="input-group">
              <i class="fas fa-envelope input-icon"></i>
              <input type="text"
                name="username"
                id="username"
                class="form-control with-icon"
                placeholder="Enter your username"
                required
                autocomplete="username">
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">
              <i class="fas fa-lock me-2"></i>Password
            </label>
            <div class="input-group" style="position: relative;">
              <i class="fas fa-lock input-icon"></i>
              <input type="password"
                name="password"
                id="password"
                class="form-control with-icon"
                placeholder="Enter your password"
                required
                autocomplete="current-password">
              <button type="button" class="toggle-password" aria-label="Show password">
                <i class="far fa-eye"></i>
              </button>
            </div>
            <div class="helper-links">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember me</label>
              </div>
              <a href="#" tabindex="-1">Forgot password?</a>
            </div>
          </div>

          <button type="submit" class="btn btn-login">
            <i class="fas fa-sign-in-alt me-2"></i>
            Sign In
          </button>
        </form>

        <div class="system-info">
          <h5><i class="fas fa-info-circle me-2"></i>System Information</h5>
          <p>Secure access to the Asset Management System</p>
        </div>

        <div class="features">
          <div class="feature">
            <i class="fas fa-shield-alt"></i>
            <span>Secure</span>
          </div>
          <div class="feature">
            <i class="fas fa-tachometer-alt"></i>
            <span>Fast</span>
          </div>
          <div class="feature">
            <i class="fas fa-users"></i>
            <span>Reliable</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function updateDateTime() {
      const now = new Date();
      const date = now.toLocaleDateString('en-PH', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
      const time = now.toLocaleTimeString('en-PH', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });

      document.getElementById('date').textContent = date;
      document.getElementById('time').textContent = time;
    }

    setInterval(updateDateTime, 1000);
    updateDateTime(); // Initial call
  </script>


  <script>
    // Add form validation and enhance UX
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('loginForm');
      const usernameInput = document.getElementById('username');
      const passwordInput = document.getElementById('password');
      const togglePasswordBtn = document.querySelector('.toggle-password');
      const themeToggle = document.getElementById('themeToggle');

      // Auto-focus on username field
      usernameInput.focus();

      // Add input validation
      usernameInput.addEventListener('input', function() {
        if (this.validity.valid) {
          this.classList.remove('is-invalid');
          this.classList.add('is-valid');
        } else {
          this.classList.remove('is-valid');
          this.classList.add('is-invalid');
        }
      });

      passwordInput.addEventListener('input', function() {
        if (this.value.length >= 1) {
          this.classList.remove('is-invalid');
          this.classList.add('is-valid');
        } else {
          this.classList.remove('is-valid');
          this.classList.add('is-invalid');
        }
      });

      // Password visibility toggle
      if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function() {
          const isHidden = passwordInput.getAttribute('type') === 'password';
          passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
          this.innerHTML = isHidden ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>';
          this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
      }

      // Theme toggle: toggles data-bs-theme and persists in localStorage
      const root = document.documentElement;
      const storedTheme = localStorage.getItem('theme');
      if (storedTheme === 'dark') {
        root.setAttribute('data-bs-theme', 'dark');
        themeToggle?.classList.replace('btn-light', 'btn-dark');
        themeToggle?.querySelector('i')?.classList.replace('fa-moon', 'fa-sun');
      }
      themeToggle?.addEventListener('click', function() {
        const isDark = root.getAttribute('data-bs-theme') === 'dark';
        const next = isDark ? 'light' : 'dark';
        root.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
        if (next === 'dark') {
          this.classList.replace('btn-light', 'btn-dark');
          this.querySelector('i')?.classList.replace('fa-moon', 'fa-sun');
        } else {
          this.classList.replace('btn-dark', 'btn-light');
          this.querySelector('i')?.classList.replace('fa-sun', 'fa-moon');
        }
      });

      // Form submission with loading state
      form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
        submitBtn.disabled = true;

        // Re-enable after 3 seconds if no redirect
        setTimeout(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }, 3000);
      });

      // Add keyboard shortcuts
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && (document.activeElement === emailInput || document.activeElement === passwordInput)) {
          form.submit();
        }
      });
    });
  </script>
</body>

</html>