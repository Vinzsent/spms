<?php
$pageTitle = 'Supply Offices Request';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Get user information from session with multiple fallbacks
$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['user']['id'] ?? '';

// Check if user is Supply In-charge or Admin
$allowed_roles = ['supply in-charge', 'admin'];
if (!in_array(strtolower(trim($user_type)), $allowed_roles)) {
  echo '<div class="container mt-5"><div class="alert alert-danger" role="alert">
          <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Access Denied</h4>
          <p>You do not have permission to view this page. This page is restricted to the <strong>Supply In-charge</strong> and <strong>Admin</strong> roles.</p>
          <hr>
          <button class="btn btn-primary" onclick="window.history.back()">Go Back</button>
        </div></div>';
  include '../includes/footer.php';
  exit;
}

// Get user name with fallbacks
$user_name = '';
if (isset($_SESSION['name'])) {
  $user_name = $_SESSION['name'];
} elseif (isset($_SESSION['user']['name'])) {
  $user_name = $_SESSION['user']['name'];
} elseif (isset($_SESSION['user']['first_name']) && isset($_SESSION['user']['last_name'])) {
  $user_name = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
} elseif (isset($_SESSION['email'])) {
  $user_name = $_SESSION['email'];
} else {
  $user_name = 'Unknown User';
}

$dashboard_link = '../dashboard.php';

$offices = [
  "Admin Office",
  "BSBA",
  "BSHM",
  "ELEMENTARY DEPT. BASIC EDUCATION",
  "JHS/ BASIC EDUCATION",
  "CELA OFFICE",
  "CES",
  "CJE",
  "CLINIC",
  "FINANCE/ ACCOUNTING",
  "GSO/ Security officer",
  "GUIDANCE/Chaplain",
  "HUMAN RESOURCE MANAGEMENT",
  "ITE Program",
  "MIS",
  "NSTP",
  "OSAS",
  "PHOTOCOPY ROOM",
  "PRESIDENT'S OFFICE",
  "Property Custodian",
  "REGISTRAR'S OFFICE",
  "SENIOR HIGH SCHOOL PROGRAM",
  "SUPPLY ROOM",
  "VPAA OFFICE",
  "OSSD",
  "MAIN LIBRARY",
  "BED LIBRARY"
];

// Sort alphabetically
sort($offices);

// Fetch request counts per office
$requestCounts = [];
$countQuery = "SELECT department_unit, COUNT(*) as total FROM supply_request GROUP BY department_unit";
$countResult = $conn->query($countQuery);
if ($countResult) {
  while ($row = $countResult->fetch_assoc()) {
    $requestCounts[$row['department_unit']] = $row['total'];
  }
}
?>


<?php include('../includes/navbar.php'); ?>

<style>
  :root {
    --pg: #073b1d;
    --pg2: #0a4f28;
    --acc: #EACA26;
    --bg: #f4f6f9;
    --bd: #dee2e6;
  }

  body {
    font-family: 'Segoe UI', sans-serif;
    background: var(--bg);
  }

  /* ─── Sidebar ─── */
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 240px;
    background: linear-gradient(135deg, var(--pg), var(--pg2));
    color: #fff;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0, 0, 0, .15);
    display: flex;
    flex-direction: column;
  }

  .sb-head {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, .1);
    margin-top: 60px;
  }

  .sb-head h3 {
    margin: 0;
    font-weight: 700;
    font-size: 1.35rem;
  }

  .sb-head small {
    opacity: .75;
    font-size: .78rem;
  }

  .sb-nav {
    padding: 14px 0;
    overflow-y: auto;
    flex: 1;
  }

  .sb-nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .nav-link {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    color: #fff;
    text-decoration: none;
    font-size: .875rem;
    border-left: 4px solid transparent;
    transition: .2s;
  }

  .nav-link:hover,
  .nav-link.active {
    background: rgba(255, 255, 255, .1);
    border-left-color: var(--acc);
    color: #fff;
  }

  .nav-link i {
    width: 18px;
    margin-right: 11px;
    text-align: center;
  }

  /* ─── Page Layout ─── */
  .wrap {
    margin-left: 280px;
    min-height: 100vh;
    padding-top: 20px;
  }

  .office-card {
    transition: all 0.3s ease;
    cursor: pointer;
    border-radius: 8px;
    border-left: 5px solid #1a5f3c;
    height: 100%;
    background-color: #fff;
  }

  .office-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    background-color: #f8f9fa;
  }

  .office-card .card-body {
    display: flex;
    align-items: center;
  }

  .office-icon {
    font-size: 24px;
    color: #1a5f3c;
    margin-right: 15px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(26, 95, 60, 0.1);
    border-radius: 8px;
  }

  .office-title {
    font-size: 14px;
    font-weight: bold;
    color: #333;
    margin: 0;
  }
</style>

<?php include '../includes/sidebar.php'; ?>

<div class="wrap">
  <div class="container-fluid px-4" style="margin-top: 60px;">
    <!-- User Information Display -->
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header" style="background-color: #1a5f3c; color: white; padding: 10px 15px; border-radius: 8px 8px 0 0;">
            <h6 class="mb-0" style="font-size: 14px;"><i class="fas fa-user-circle me-2"></i>Current User Information</h6>
          </div>
          <div class="card-body" style="padding: 15px;">
            <div class="row align-items-center">
              <div class="col-md-12">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <i class="fas fa-user-circle fa-2x" style="color: #1a5f3c;"></i>
                  </div>
                  <div>
                    <h5 class="mb-1" style="font-size: 16px; font-weight: 600;"><?= htmlspecialchars($user_name) ?></h5>
                    <p class="mb-0 text-muted" style="font-size: 12px;">
                      <?php if (!empty($user_type)): ?>
                        <strong>Position:</strong> <?= htmlspecialchars(strtoupper($user_type)) ?>
                      <?php endif; ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 style="font-size: 22px; color: #1a5f3c; font-weight: 700;"><i class="fas fa-building me-2"></i>Office Requisition Dashboard</h3>
      <div>
        <a href="<?= htmlspecialchars($dashboard_link) ?>" class="btn btn-sm btn-outline-secondary" style="font-size: 13px; padding: 6px 12px; border-radius: 5px;">
          <i class="fas fa-home me-1"></i> Home
        </a>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
      <div class="card-body p-4">
        <div class="row align-items-center mb-4">
          <div class="col-md-6 mb-3 mb-md-0">
            <p class="mb-0 text-muted" style="font-size: 14px; font-weight: 500;">Select an office to manage supply requisitions and issuances.</p>
          </div>
          <div class="col-md-6">
            <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
              <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input type="text" id="officeSearch" class="form-control border-start-0 py-2" placeholder="Search offices..." style="box-shadow: none; font-size: 14px;">
            </div>
          </div>
        </div>

        <div class="row g-4" id="officesGrid">
          <?php foreach ($offices as $office):
            $urlEncodedOffice = urlencode($office);
            // Derive an icon based on name
            $icon = 'fa-door-open';
            $officeLower = strtolower($office);
            if (strpos($officeLower, 'admin') !== false) $icon = 'fa-user-tie';
            else if (strpos($officeLower, 'library') !== false) $icon = 'fa-book';
            else if (strpos($officeLower, 'clinic') !== false) $icon = 'fa-notes-medical';
            else if (strpos($officeLower, 'finance') !== false || strpos($officeLower, 'accounting') !== false) $icon = 'fa-calculator';
            else if (strpos($officeLower, 'security') !== false || strpos($officeLower, 'custodian') !== false) $icon = 'fa-shield-alt';
            else if (strpos($officeLower, 'mis') !== false || strpos($officeLower, 'ite') !== false) $icon = 'fa-desktop';
          ?>
            <div class="col-xl-3 col-lg-4 col-md-6 office-item">
              <a href="office_supply_requests.php?office=<?= $urlEncodedOffice ?>" class="text-decoration-none">
                <div class="card office-card border-0 shadow-sm">
                  <div class="card-body py-4">
                    <div class="office-icon">
                      <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                      <h5 class="office-title"><?= htmlspecialchars($office) ?></h5>
                      <?php
                      $count = $requestCounts[$office] ?? 0;
                      $badgeClass = ($count > 0) ? 'bg-success text-white' : 'bg-light text-muted';
                      ?>
                      <div class="mt-1 text-muted small">
                        <i class="fas fa-file-alt me-1"></i> <?= $count ?> Request<?= $count != 1 ? 's' : '' ?>
                      </div>
                    </div>


                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="text-center py-5 d-none">
          <div class="mb-3">
            <i class="fas fa-search fa-3x text-muted opacity-50"></i>
          </div>
          <h5 class="text-muted fw-bold">No offices found</h5>
          <p class="text-muted small">We couldn't find any office matching your search.</p>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  $(document).ready(function() {
    // Live Search Functionality
    $('#officeSearch').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      var visibleCount = 0;

      $('.office-item').each(function() {
        var text = $(this).text().toLowerCase();
        if (text.indexOf(value) > -1) {
          $(this).show();
          visibleCount++;
        } else {
          $(this).hide();
        }
      });

      if (visibleCount === 0) {
        $('#noResults').removeClass('d-none');
      } else {
        $('#noResults').addClass('d-none');
      }
    });
  });
</script>