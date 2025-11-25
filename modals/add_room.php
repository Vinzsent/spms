<style>

  .header-text{
    text-align: center;
    font-size: 1.2rem;
    font-weight: 600;
  }
</style>

<form method="POST" action="../actions/add_room.php" class="add-room-form" id="addRoomForm">
  <!-- Room Information Section -->
  <div class="form-section mb-4">
    <h6 class="section-title header-text">
      <i class="fas fa-building me-2"></i>Room Information
    </h6>
    <div class="row g-3">
      <div class="col-md-6">
        <label for="building_name" class="form-label">
          <i class="fas fa-building me-1"></i>Building Name <span class="text-danger">*</span>
        </label>
        <input class="form-control" name="building_name" id="building_name" placeholder="e.g., Salusiano Building" required>
      </div>
      <div class="col-md-6">
        <label for="room_number" class="form-label">
          <i class="fas fa-door-open me-1"></i>Room Number <span class="text-danger">*</span>
        </label>
        <input class="form-control" name="room_number" id="room_number" placeholder="e.g., 301" required>
      </div>
    </div>
    
    <div class="row g-3 mt-2">
      <div class="col-md-12">
        <label for="floor" class="form-label">
          <i class="fas fa-layer-group me-1"></i>Floor <span class="text-danger">*</span>
        </label>
        <select class="form-select" name="floor" id="floor" required>
          <option value="">-- Select Floor --</option>
          <option value="R">First Floor (FF)</option>
          <option value="N">Second Floor (SF)</option>
          <option value="O">Third Floor (TF)</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Inventory Items Section -->
  <div class="form-section mb-4">
    <h6 class="section-title header-text">
      <i class="fas fa-boxes me-2"></i>Inventory Items
    </h6>
    <div class="row g-3">
      <div class="col-md-6">
        <label for="fluorescent_light" class="form-label">
          <i class="fas fa-lightbulb me-1"></i>Fluorescent Light
        </label>
        <input type="number" class="form-control" name="fluorescent_light" id="fluorescent_light" placeholder="0" min="0" value="0">
      </div>
      <div class="col-md-6">
        <label for="electric_fans_wall" class="form-label">
          <i class="fas fa-fan me-1"></i>Electric Fans - Wall
        </label>
        <input type="number" class="form-control" name="electric_fans_wall" id="electric_fans_wall" placeholder="0" min="0" value="0">
      </div>
    </div>
    
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label for="ceiling" class="form-label">
          <i class="fas fa-th me-1"></i>Ceiling
        </label>
        <input type="number" class="form-control" name="ceiling" id="ceiling" placeholder="0" min="0" value="0">
      </div>
      <div class="col-md-6">
        <label for="chairs_mono" class="form-label">
          <i class="fas fa-chair me-1"></i>Chairs - Mono
        </label>
        <input type="number" class="form-control" name="chairs_mono" id="chairs_mono" placeholder="0" min="0" value="0">
      </div>
    </div>
    
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label for="steel" class="form-label">
          <i class="fas fa-cube me-1"></i>Steel
        </label>
        <input type="number" class="form-control" name="steel" id="steel" placeholder="0" min="0" value="0">
      </div>
      <div class="col-md-6">
        <label for="plastic_mini" class="form-label">
          <i class="fas fa-cube me-1"></i>Plastic (mini)
        </label>
        <input type="number" class="form-control" name="plastic_mini" id="plastic_mini" placeholder="0" min="0" value="0">
      </div>
    </div>
    
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label for="teacher_table" class="form-label">
          <i class="fas fa-table me-1"></i>Teacher's Table
        </label>
        <input type="number" class="form-control" name="teacher_table" id="teacher_table" placeholder="0" min="0" value="0">
      </div>
      <div class="col-md-6">
        <label for="black_whiteboard" class="form-label">
          <i class="fas fa-chalkboard me-1"></i>Black/Whiteboard
        </label>
        <input type="number" class="form-control" name="black_whiteboard" id="black_whiteboard" placeholder="0" min="0" value="0">
      </div>
    </div>
    
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label for="platform" class="form-label">
          <i class="fas fa-layer-group me-1"></i>Platform
        </label>
        <input type="number" class="form-control" name="platform" id="platform" placeholder="0" min="0" value="0">
      </div>
      <div class="col-md-6">
        <label for="tv" class="form-label">
          <i class="fas fa-tv me-1"></i>TV
        </label>
        <input type="number" class="form-control" name="tv" id="tv" placeholder="0" min="0" value="0">
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="form-actions">
    <button type="submit" class="btn btn-primary w-100">
      <i class="fas fa-plus me-2"></i>Add Room
    </button>
  </div>
</form>

<style>
/* Color scheme variables matching the design */
:root {
    --primary-green: #073b1d;
    --dark-green: #073b1d;
    --light-green: #66BB6A;
    --accent-orange: #FF9800;
    --accent-yellow: #FFEB3B;
    --accent-red: #F44336;
    --text-dark: #073b1d;
    --text-light: #6c757d;
    --bg-light: #ffffff;
    --border-light: #dee2e6;
    --success-green: #28a745;
}

/* Form styling */
.add-room-form {
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
    display: block;
}

.form-control, .form-select {
    border: 1px solid var(--border-light);
    border-radius: 6px;
    padding: 0.75rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    width: 100%;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(7, 59, 29, 0.25);
    outline: none;
}

.form-control::placeholder {
    color: var(--text-light);
    opacity: 0.7;
}

/* Button styling with orange accent */
.btn-primary {
    background: linear-gradient(135deg, var(--accent-orange) 0%, #e55a2b 100%);
    border-color: var(--accent-orange);
    color: white;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 1rem;
    border: none;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #e55a2b 0%, #d94a1b 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
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

.w-100 {
    width: 100%;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.5rem;
}

.col-md-6, .col-md-12 {
    padding: 0 0.5rem;
    flex: 0 0 auto;
}

.col-md-6 {
    width: 50%;
}

.col-md-12 {
    width: 100%;
}

.g-3 {
    gap: 1rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.me-1 {
    margin-right: 0.25rem;
}

.me-2 {
    margin-right: 0.5rem;
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
    
    .col-md-6 {
        width: 100%;
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

