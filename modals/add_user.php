<form method="POST" action="../actions/add_user.php" class="add-user-form">
  <!-- Personal Information Section -->
  <div class="form-section mb-4">
    <h6 class="section-title">
      <i class="fas fa-user me-2"></i>Personal Information
    </h6>
    <div class="row g-3">
      <div class="col-md-6">
        <label for="add-title" class="form-label">
          <i class="fas fa-id-badge me-1"></i>Title
        </label>
        <input class="form-control" name="title" id="add-title" placeholder="Mr., Mrs., Dr., etc.">
      </div>
      <div class="col-md-6">
        <label for="add-suffix" class="form-label">
          <i class="fas fa-id-badge me-1"></i>Suffix
        </label>
        <input class="form-control" name="suffix" id="add-suffix" placeholder="Jr., Sr., III, etc.">
      </div>
    </div>
    
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label for="add-firstname" class="form-label">
          <i class="fas fa-user me-1"></i>First Name <span class="text-danger">*</span>
        </label>
        <input class="form-control" name="first_name" id="add-firstname" placeholder="First Name" required>
      </div>
      <div class="col-md-6">
        <label for="add-middlename" class="form-label">
          <i class="fas fa-user me-1"></i>Middle Name
        </label>
        <input class="form-control" name="middle_name" id="add-middlename" placeholder="Optional">
      </div>
      <div class="col-md-6">
        <label for="add-lastname" class="form-label">
          <i class="fas fa-user me-1"></i>Last Name <span class="text-danger">*</span>
        </label>
        <input class="form-control" name="last_name" id="add-lastname" placeholder="Last Name" required>
      </div>
    </div>
    
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label for="add-academic" class="form-label">
          <i class="fas fa-graduation-cap me-1"></i>Academic Title
        </label>
        <input class="form-control" name="academic_title" id="add-academic" placeholder="Ph.D., M.D., etc.">
      </div>
      <div class="col-md-6">
        <label for="add-usertype" class="form-label">
          <i class="fas fa-briefcase me-1"></i>Position <span class="text-danger">*</span>
        </label>
        <select class="form-select" name="user_type" id="add-usertype" required>
          <option value="">-- Select Position --</option>
          <option value="Admin">Administrator</option>
          <option value="Immediate Head">Immediate Head</option>
          <option value="Immediate Head - CELA">Immediate Head - CELA</option>
          <option value="Immediate Head - CJE">Immediate Head - CJE</option>
          <option value="Immediate Head - CBM">Immediate Head - CBM</option>
          <option value="Immediate Head - ITE">Immediate Head - ITE</option>
          <option value="Immediate Head - HRM">Immediate Head - HME</option>
          <option value="Faculty">Faculty</option>
          <option value="Faculty - CELA">Faculty - CELA</option>
          <option value="Faculty - CJE">Faculty - CJE</option>
          <option value="Faculty - CBM">Faculty - CBM</option>
          <option value="Faculty - ITE">Faculty - ITE</option>
          <option value="Faculty - HRM">Faculty - HME</option>
          <option value="Staff">Staff</option>
          <option value="MIS Head">MIS Head</option>
          <option value="MIS Computer Programmer">MIS Computer Programmer</option>
          <option value="School President">School President</option>
          <option value="Supply In-charge">Supply In-charge</option>
          <option value="Property Custodian">Property Custodian</option>
          <option value="Purchasing Officer">Purchasing Officer</option>
          <option value="VP for Finance & Administration">VP for Finance & Administration</option>
          <option value="Finance Officer">Finance Officer</option>
          <option value="VP for Academic Affairs">VP for Academic Affairs</option>
          <option value="Admistrative Officer">Admistrative Officer</option>
          <option value="Accounting Officer">Accounting Officer</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Account Information Section -->
  <div class="form-section mb-4">
    <h6 class="section-title">
      <i class="fas fa-envelope me-2"></i>Account Information
    </h6>
    <div class="row g-3">
      <div class="col-md-12">
        <label for="add-username" class="form-label">
          <i class="fas fa-envelope me-1"></i>Username <span class="text-danger">*</span>
        </label>
        <input class="form-control" type="text" name="username" id="add-username" placeholder="user@example.com" required>
      </div>
    </div>
    
    <div class="row g-3 mt-2">
      <div class="col-md-12">
        <label for="add-password" class="form-label">
          <i class="fas fa-lock me-1"></i>Password <span class="text-danger">*</span>
        </label>
        <input class="form-control" type="password" name="password" id="add-password" placeholder="Enter a secure password" required>
        <div class="form-text">
          <i class="fas fa-info-circle me-1"></i>
          Password must be at least 6 characters long.
        </div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="form-actions">
    <button type="submit" class="btn btn-primary w-100">
      <i class="fas fa-user-plus me-2"></i>Register User
    </button>
  </div>
</form>

<style>
/* Color scheme variables */
:root {
    --primary-green: #1a5f3c;
    --secondary-green: #2d7a4d;
    --accent-orange: #fd7e14;
    --accent-red: #dc3545;
    --accent-blue: #0d6efd;
    --text-dark: #212529;
    --text-light: #6c757d;
    --bg-light: #ffffff;
    --border-light: #dee2e6;
    --success-green: #28a745;
}

/* Form styling */
.add-user-form {
    color: var(--text-dark);
}

.form-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border-left: 4px solid var(--primary-green);
}

.section-title {
    color: var(--primary-green);
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--border-light);
}

.form-label {
    font-weight: 500;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border: 1px solid var(--border-light);
    border-radius: 6px;
    padding: 0.75rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(26, 95, 60, 0.25);
    outline: none;
}

.form-control::placeholder {
    color: var(--text-light);
    opacity: 0.7;
}

.form-text {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-top: 0.25rem;
}

/* Button styling */
.btn-primary {
    background: var(--accent-orange);
    border-color: var(--accent-orange);
    color: white;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.btn-primary:hover {
    background: #e8690b;
    border-color: #e8690b;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(253, 126, 20, 0.3);
}

.btn-primary:active {
    transform: translateY(0);
}

/* Required field indicator */
.text-danger {
    color: var(--accent-red) !important;
}

/* Form actions */
.form-actions {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-light);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-section {
        padding: 1rem;
    }
    
    .section-title {
        font-size: 1rem;
    }
    
    .btn-primary {
        padding: 0.6rem 1.2rem;
        font-size: 0.95rem;
    }
}

/* Animation for form sections */
.form-section {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Icon styling */
.form-label i {
    color: var(--primary-green);
    width: 16px;
}

.section-title i {
    color: var(--primary-green);
}
</style>