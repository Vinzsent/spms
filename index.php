<?php
session_start();
include 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Store user data in session with proper keys
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['id']; // Store user ID separately
            $_SESSION['id'] = $user['id']; // Alternative key for compatibility
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['name'] = $user['name'] ?? $user['first_name'] . ' ' . $user['last_name'] ?? $user['email'];
            $_SESSION['email'] = $user['email'];
            
            // Debug: Log session data
            error_log('Login successful - User ID: ' . $user['id'] . ', User Type: ' . $user['user_type']);
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SPMS - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
      max-width: 450px;
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
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
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
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
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
      border-top: 1px solid rgba(0,0,0,0.1);
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
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }
  </style>
</head>
<body>
  <!-- Floating Background Shapes -->
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1><i class="fas fa-shield-alt me-2"></i>AMS</h1>
        <p>Asset Management System</p>
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
            <label for="email" class="form-label">
              <i class="fas fa-envelope me-2"></i>Email Address
            </label>
            <div class="input-group">
              <i class="fas fa-envelope input-icon"></i>
              <input type="email" 
                     name="email" 
                     id="email" 
                     class="form-control with-icon" 
                     placeholder="Enter your email address" 
                     required 
                     autocomplete="email">
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">
              <i class="fas fa-lock me-2"></i>Password
            </label>
            <div class="input-group">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" 
                     name="password" 
                     id="password" 
                     class="form-control with-icon" 
                     placeholder="Enter your password" 
                     required 
                     autocomplete="current-password">
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
    // Add form validation and enhance UX
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('loginForm');
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');

      // Auto-focus on email field
      emailInput.focus();

      // Add input validation
      emailInput.addEventListener('input', function() {
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