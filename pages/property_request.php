<?php
$pageTitle = 'Property Request';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Get user information from session with multiple fallbacks
$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['user']['id'] ?? '';

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

$dashboard_link = ($user_type == 'Admin') ? '../admin_dashboard.php' : '../dashboard.php';

// Get request type from URL parameter or session storage
$request_type = $_GET['type'] ?? '';
if (empty($request_type)) {
  // Try to get from session storage via JavaScript
  echo "<script>
        var requestType = sessionStorage.getItem('selectedRequestType');
        if (requestType) {
            window.location.href = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'type=' + requestType;
        }
    </script>";
}

// Debug: Log session variables to help troubleshoot
error_log('Supply Request Page - Session variables: ' . print_r($_SESSION, true));
error_log('Supply Request Page - User ID: ' . $user_id . ', User Name: ' . $user_name . ', User Type: ' . $user_type);

$sql = "SELECT * FROM supply_request WHERE status IS NULL";
$result = $conn->query($sql);

// Fetch all category data for dropdown
$categories_query = "
    SELECT 
        at.id as category_id,
        at.name as main_category,
        sc.name as subcategory,
        ssc.name as sub_subcategory,
        sssc.name as sub_sub_subcategory
    FROM account_types at
    LEFT JOIN account_subcategories sc ON at.id = sc.parent_id
    LEFT JOIN account_sub_subcategories ssc ON sc.id = ssc.subcategory_id
    LEFT JOIN account_sub_sub_subcategories sssc ON ssc.id = sssc.sub_subcategory_id
    WHERE at.id BETWEEN 14 AND 29
    ORDER BY at.id, sc.name, ssc.name, sssc.name
";
$categories_result = $conn->query($categories_query);

// Organize categories hierarchically
$organized_categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $main = $row['main_category'];
        if (!isset($organized_categories[$main])) {
            $organized_categories[$main] = [];
        }
        
        if (!empty($row['subcategory'])) {
            $sub = $row['subcategory'];
            if (!in_array($sub, $organized_categories[$main])) {
                $organized_categories[$main][] = $sub;
            }
        }
        
        if (!empty($row['sub_subcategory'])) {
            $subsub = $row['sub_subcategory'];
            if (!in_array($subsub, $organized_categories[$main])) {
                $organized_categories[$main][] = $subsub;
            }
        }
        
        if (!empty($row['sub_sub_subcategory'])) {
            $subsubsub = $row['sub_sub_subcategory'];
            if (!in_array($subsubsub, $organized_categories[$main])) {
                $organized_categories[$main][] = $subsubsub;
            }
        }
    }
}

// Display session messages
if (isset($_SESSION['message'])) {
  echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
          ' . htmlspecialchars($_SESSION['message']) . '
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
  unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
  echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
          ' . htmlspecialchars($_SESSION['error']) . '
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
  unset($_SESSION['error']);
}
?>

<style>
   /* Center only specific optgroup labels in category dropdown */
  #accountSelect optgroup[label="Assets"],
  #accountSelect optgroup[label="Expenses"] {
    text-align: center;
    font-weight: bold;
    font-size: 14px;
    color: #1a5f3c;
    background-color: #f8f9fa;
    padding: 8px 0;
  }

  /* Keep other optgroups left-aligned */
  #accountSelect optgroup:not([label="Assets"]):not([label="Expenses"]) {
    text-align: left;
    font-weight: bold;
    font-size: 14px;
    color: #1a5f3c;
    background-color: #f8f9fa;
    padding: 8px 0;
  }

  #categorySelect option {
    text-align: left;
    padding-left: 20px;
    font-weight: normal;
    color: #333;
  }

  /* Export buttons responsive styling */
  #exportContainer {
    min-height: 60px;
    padding: 8px;
    transition: all 0.2s ease;
  }

  #exportContainer:hover {
    border-color: #adb5bd !important;
    background-color: #e9ecef !important;
  }

  #exportContainer .btn {
    margin: 2px;
    white-space: nowrap;
    font-size: 11px;
    padding: 4px 8px;
    min-width: 40px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  /* Responsive adjustments for smaller screens */
  @media (max-width: 768px) {
    #exportContainer {
      justify-content: center !important;
      flex-wrap: wrap;
    }
    
    #exportContainer .btn {
      margin: 1px;
      font-size: 10px;
      padding: 3px 6px;
      min-width: 36px;
      height: 28px;
    }
  }

  @media (max-width: 576px) {
    #exportContainer {
      flex-direction: column;
      align-items: center;
      width: 100%;
    }
    
    #exportContainer .btn {
      width: 100%;
      max-width: 120px;
      margin: 2px 0;
      height: 32px;
    }
  }

  /* Ensure buttons don't overlap on very small screens */
  @media (max-width: 480px) {
    #exportContainer {
      gap: 5px !important;
    }
    
    #exportContainer .btn {
      margin: 0;
    }
  }
</style>

<?php include('../includes/navbar.php'); ?>
<div class="container" style="margin-top: 100px;">

  <!-- User Information Display -->
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card border-dark">
        <div class="card-header" style="background-color: #1a5f3c; color: white; padding: 2px 6px;">
          <h6 class="mb-0" style="font-size: 12px;"><i class="fas fa-user-circle me-2"></i>Current User Information</h6>
        </div>
        <div class="card-body" style="padding: 2px 6px;">
          <div class="row align-items-center">
            <div class="col-md-8">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fas fa-user-circle fa-lg" style="color: #1a5f3c;"></i>
                </div>
                <div>
                  <h5 class="mb-1" style="font-size: 12px;"><?= htmlspecialchars($user_name) ?></h5>
                  <p class="mb-1 text-muted" style="font-size: 10px;">
                    <?php if (!empty($user_type)): ?>
                      | <strong>Position:</strong> <?= htmlspecialchars(strtoupper($user_type)) ?>
                    <?php endif; ?>
                  </p>
                  <?php if (empty($user_id)): ?>
                    <div class="mt-2">
                      <small class="text-warning" style="font-size: 10px;">
                        <i class="fas fa-exclamation-triangle me-1"></i>Warning: User ID not found in session
                      </small>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-md-4 text-end">
              <button class="btn btn-sm" style="background-color: #fd7e14; color: white; font-size: 12px; padding: 2px 6px;" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                <i class="fas fa-plus me-2"></i>New Property Request
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <h3 style="font-size: 16px;">Property Request</h3>
    <div>
      <button class="btn btn-sm" style="background-color: #fd7e14; color: white; font-size: 12px; padding: 2px 6px;" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Previous</button>
    </div>
  </div>

  <!-- Request Type Display -->
  <?php if (!empty($request_type)): ?>
    <div class="alert alert-info text-center mb-4" role="alert" style="padding: 6px;">
      <i class="fas fa-info-circle me-2"></i>
      <strong style="font-size: 12px;">Request Type:</strong>
      <span class="badge bg-primary ms-2" style="font-size: 10px;">
        <?= ucfirst($request_type) ?>
      </span>
      <button type="button" class="btn btn-sm btn-outline-secondary ms-3" style="font-size: 12px; padding: 2px 6px;" onclick="changeRequestType()">
        <i class="fas fa-edit me-1"></i>Change Type
      </button>
    </div>
  <?php endif; ?>

  <h5 class="text-center" style="font-size: 14px;">List of employee property request</h5>
  <hr>
  
  <!-- Filter Row -->
  <div class="row align-items-end mb-2 g-2">
    <div class="col-lg-2 col-md-3 col-sm-6">
      <label for="dtSearch" class="form-label" style="font-size: 12px;">Search</label>
      <input type="search" id="dtSearch" class="form-control" style="font-size: 12px; padding: 2px 6px;" placeholder="Search...">
    </div>

    <div class="col-lg-2 col-md-3 col-sm-6">
      <label for="filterDateStart" class="form-label" style="font-size: 12px;">Date Range (From)</label>
      <input type="date" id="filterDateStart" class="form-control" style="font-size: 12px; padding: 2px 6px;">
    </div>

    <div class="col-lg-2 col-md-3 col-sm-6">
      <label for="filterDateEnd" class="form-label" style="font-size: 12px;">Date Range (To)</label>
      <input type="date" id="filterDateEnd" class="form-control" style="font-size: 12px; padding: 2px 6px;">
    </div>

    <div class="col-lg-3 col-md-3 col-sm-12">
      <label for="accountSelect" style="font-size: 12px;">Select Account Category:</label>
      <select id="accountSelect" name="accountSelect" class="form-select" style="font-size: 12px; padding: 2px 6px;">
        <option value="">Select Category</option>
                    <?php
                    // Use the same organized categories from the main page
                    if (isset($organized_categories) && !empty($organized_categories)) {
                        foreach ($organized_categories as $main_category => $subcategories) {
                            echo '<optgroup label="' . htmlspecialchars($main_category) . '">';
                            foreach ($subcategories as $subcategory) {
                                echo '<option value="' . htmlspecialchars($subcategory) . '">' . htmlspecialchars($subcategory) . '</option>';
                            }
                            echo '</optgroup>';
                        }
                    } else {
                        // Fallback options if no data available - display as bold headers only
                        echo '<optgroup label="Property and Equipment"></optgroup>';
                        echo '<optgroup label="Intangible Assets"></optgroup>';
                        echo '<optgroup label="Office Supplies"></optgroup>';
                        echo '<optgroup label="Medical Supplies"></optgroup>';
                    }
                    ?>
      </select>
    </div>

    <div class="col-lg-3 col-md-12 col-sm-12">
      <label class="form-label d-block" style="font-size: 12px;">Export</label>
      <div id="exportContainer" class="d-flex flex-wrap gap-1 justify-content-start"></div>
    </div>
  </div>

  <!-- Quick Date Filters -->
  <div class="row mb-2">
    <div class="col-12">
      <label class="form-label" style="font-size: 12px;">Quick Date Filters:</label>
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-primary btn-sm" style="font-size: 12px; padding: 2px 6px;" id="currentMonth">Current Month</button>
        <button type="button" class="btn btn-outline-primary btn-sm" style="font-size: 12px; padding: 2px 6px;" id="currentYear">Current Year</button>
        <button type="button" class="btn btn-outline-primary btn-sm" style="font-size: 12px; padding: 2px 6px;" id="lastMonth">Last Month</button>
        <button type="button" class="btn btn-outline-primary btn-sm" style="font-size: 12px; padding: 2px 6px;" id="lastYear">Last Year</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" style="font-size: 12px; padding: 2px 6px;" id="clearDateFilter">Clear Date Filter</button>
      </div>
    </div>
  </div>
  <hr>

  <!-- Supply Request Table -->
  <div class="table-responsive">
    <table id="transactionsTable" class="table table-bordered table-striped table-sm">
      <thead>
        <tr>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Date Requested</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Date Needed</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Quantity</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Unit</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Item Name</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Description</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Unit Cost</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Total Cost</th>
          <th class="text-center" style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 16px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $total_sum = 0;
        while ($row = $result->fetch_assoc()):
          $quantity = (float)($row['quantity_requested'] ?? 0);
          $unit_cost = (float)($row['unit_cost'] ?? 0);
          $computed_total_cost = $quantity * $unit_cost;
          $total_sum += $computed_total_cost;
        ?>
          <tr style="font-size: 11px;">
            <td style="padding: 4px 6px;"><?= $row['date_requested'] ?></td>
            <td style="padding: 4px 6px;"><?= htmlspecialchars($row['date_needed']) ?></td>
            <td style="padding: 4px 6px;"><?= htmlspecialchars($row['quantity_requested']) ?></td>
            <td style="padding: 4px 6px;"><?= htmlspecialchars($row['unit']) ?></td>
            <td style="padding: 4px 6px;"><?= htmlspecialchars($row['item_name']) ?></td>
            <td style="padding: 4px 6px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= $row['request_description'] ?></td>
            <td style="padding: 4px 6px;">
              ₱<?= !empty($unit_cost)
                  ? number_format($unit_cost, 2)
                  : ' <span style="color: red; font-weight: bold; font-size: 12px;">No Cost</span>'; ?>
            </td>
            <td style="padding: 4px 6px;">
              ₱<?= !empty($computed_total_cost)
                  ? number_format($computed_total_cost, 2)
                  : ' <span style="color: red; font-weight: bold; font-size: 12px;">No Cost</span>'; ?>
            </td>
            <td style="padding: 4px 6px;">
              <button
                class="btn btn-xs viewBtn" 
                style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 2px 6px; border-radius: 3px;"
                data-request-id="<?= $row['request_id'] ?>"
                data-date-requested="<?= htmlspecialchars($row['date_requested']) ?>"
                data-date-needed="<?= htmlspecialchars($row['date_needed']) ?>"
                data-department-unit="<?= trim($row['department_unit']) ?>"
                data-purpose="<?= trim($row['purpose']) ?>"
                data-sales-type="<?= htmlspecialchars($row['sales_type']) ?>"
                data-category="<?= htmlspecialchars($row['category']) ?>"
                data-item-name="<?= htmlspecialchars($row['item_name']) ?>"
                data-brand="<?= htmlspecialchars($row['brand']) ?>"
                data-color="<?= htmlspecialchars($row['color']) ?>"
                data-quantity-requested="<?= htmlspecialchars($row['quantity_requested']) ?>"
                data-unit="<?= $row['unit'] ?>"
                data-request-description="<?= htmlspecialchars($row['request_description']) ?>"
                data-quality-issued="<?= htmlspecialchars($row['quality_issued']) ?>"
                data-unit-cost="<?= $unit_cost ?>"
                data-total-cost="<?= $computed_total_cost ?>"
                data-bs-toggle="modal"
                data-bs-target="#viewSupplyModal">
                <i class="fas fa-eye" style="font-size: 12px;"></i> View
              </button>

              <button
                class="btn btn-xs editBtn" 
                style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white; font-size: 12px; padding: 2px 6px; border-radius: 3px;"
                data-request-id="<?= $row['request_id'] ?>"
                data-date-requested="<?= htmlspecialchars($row['date_requested']) ?>"
                data-date-needed="<?= htmlspecialchars($row['date_needed']) ?>"
                data-department-unit="<?= trim($row['department_unit']) ?>"
                data-purpose="<?= trim($row['purpose']) ?>"
                data-sales-type="<?= htmlspecialchars($row['sales_type']) ?>"
                data-category="<?= htmlspecialchars($row['category']) ?>"
                data-item-name="<?= htmlspecialchars($row['item_name']) ?>"
                data-brand="<?= htmlspecialchars($row['brand']) ?>"
                data-color="<?= htmlspecialchars($row['color']) ?>"
                data-quantity-requested="<?= htmlspecialchars($row['quantity_requested']) ?>"
                data-unit="<?= $row['unit'] ?>"
                data-request-description="<?= htmlspecialchars($row['request_description']) ?>"
                data-quality-issued="<?= htmlspecialchars($row['quality_issued']) ?>"
                data-unit-cost="<?= $unit_cost ?>"
                data-total-cost="<?= $computed_total_cost ?>"
                data-bs-toggle="modal"
                data-bs-target="#editSupplyModal">
                <i class="fas fa-edit" style="font-size: 12px;"></i> Edit
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
      <tfoot>
        <tr style="font-size: 12px;">
          <td colspan="7" class="text-end fw-bold" style="padding: 6px;">Total:</td>
          <td class="fw-bold" id="grandTotalCell" style="padding: 6px;">₱<?= number_format($total_sum, 2) ?></td>
          <td style="padding: 6px;"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php include '../modals/add_property_request.php'; ?>
<?php include '../modals/view_property_request.php'; ?>
<?php include '../modals/edit_property_request.php'; ?>
<?php include '../includes/footer.php'; ?>

<!-- Add missing JavaScript dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
  // Function to change request type
  function changeRequestType() {
    // Clear the current request type
    sessionStorage.removeItem('selectedRequestType');
    // Redirect back to dashboard to show the modal again
    window.location.href = '../dashboard.php';
  }

  // Auto-redirect if no request type is selected
  <?php if (empty($request_type)): ?>
    document.addEventListener('DOMContentLoaded', function() {
      // Check if we have a request type in session storage
      var requestType = sessionStorage.getItem('selectedRequestType');
      if (!requestType) {
        // Check if user has roles that should have direct access
        var userType = '<?= strtolower($user_type) ?>';
        var allowedRoles = ['vp for finance & administration', 'purchasing officer', 'supply in-charge', 'property custodian', 'immediate head', 'school president'];
        
        if (allowedRoles.includes(userType)) {
          // Allow direct access for these roles - don't redirect
          console.log('Direct access allowed for role: ' + userType);
        } else {
          // If no request type and not an allowed role, redirect back to dashboard
          setTimeout(function() {
            window.location.href = '../dashboard.php';
          }, 1000);
        }
      }
    });
  <?php endif; ?>

  // Store the current request type for form submission
  <?php if (!empty($request_type)): ?>
    document.addEventListener('DOMContentLoaded', function() {
      // Add hidden input to the add supply modal form
      const addSupplyForm = document.querySelector('#addSupplyModal form');
      if (addSupplyForm) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'request_type';
        hiddenInput.value = '<?= $request_type ?>';
        addSupplyForm.appendChild(hiddenInput);
      }
    });
  <?php endif; ?>

  $(document).ready(function() {
    let table;
    try {
      table = $('#transactionsTable').DataTable({
      dom: 'Brtip',
      buttons: [        {
          extend: 'excelHtml5',
          text: '<i class="fa-solid fa-file-excel"></i>',
          className: 'btn btn-sm btn-outline-success',
          titleAttr: 'Export to Excel',
          customize: function(xlsx) {
            let sheet = xlsx.xl.worksheets['sheet1.xml'];
            $('row c[r]', sheet).attr('s', '0');
          }
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fa-solid fa-file-pdf"></i>',
          title: 'Supply Request Report',
          className: 'btn btn-sm btn-outline-danger',
          titleAttr: 'Export to PDF',
          footer: true,
          exportOptions: {
            columns: ':not(:last-child)' // Exclude the last column (Action)
          },
          customize: function(doc) {
            // Set document properties
            doc.defaultStyle.fontSize = 8;
            doc.styles.tableHeader.fontSize = 9;
            doc.styles.tableHeader.bold = true;
            doc.styles.tableHeader.fillColor = '#1a5f3c';
            doc.styles.tableHeader.color = 'white';

            // Set table layout with borders
            doc.content[1].layout = {
              hLineWidth: function(i, node) {
                return 1;
              },
              vLineWidth: function(i, node) {
                return 1;
              },
              hLineColor: function(i, node) {
                return '#2d3748';
              },
              vLineColor: function(i, node) {
                return '#2d3748';
              },
              paddingLeft: function(i, node) {
                return 4;
              },
              paddingRight: function(i, node) {
                return 4;
              },
              paddingTop: function(i, node) {
                return 2;
              },
              paddingBottom: function(i, node) {
                return 2;
              }
            };

            // Make footer row bold
            if (doc.content[1].table.body) {
              const lastRowIndex = doc.content[1].table.body.length - 1;
              if (lastRowIndex >= 0) {
                doc.content[1].table.body[lastRowIndex].forEach(function(cell) {
                  cell.bold = true;
                  cell.fillColor = '#f8f9fa';
                });
              }
            }

            // Set page orientation to landscape for better table fit
            doc.pageOrientation = 'landscape';
            doc.pageMargins = [20, 20, 20, 20];
          }
        },
        {
          extend: 'print',
          text: '<i class="fa-solid fa-print text-white"></i>',
          title: 'Property Request List',
          className: 'btn btn-sm btn-outline-secondary',
          titleAttr: 'Print Report',
          footer: true, // <-- include the footer (TOTAL row)
          exportOptions: {
            columns: ':not(:last-child)' // Exclude the last column (Action)
          },
          customize: function(win) {
            const table = $(win.document.body).find('table');
            table.removeClass('table-bordered table-striped table-dark').addClass('table');

            // Add custom styling for the print view
            $(win.document.head).append(
              '<style>' +
              'table { width: 100%; border-collapse: collapse; }' +
              'th, td { border: 1px solid #000; padding: 8px; text-align: left; }' +
              'tfoot th { font-weight: bold; }' +
              '</style>'
            );

            // Get the footer row and total amount
            const footerRow = table.find('tfoot tr');
            const totalAmount = footerRow.find('th').last().text();

            // Clear the footer cells
            footerRow.find('th').html('');

            // Set the content of the footer
            footerRow.find('th').eq(-2).text('Total:').css('text-align', 'right');
            footerRow.find('th').eq(-1).text(totalAmount);
          }
        }
      ],
      ordering: true,
      searching: true,
      paging: true,
      lengthChange: false
    });
    } catch (error) {
      console.error('Error initializing DataTable:', error);
      return; // Exit early if table initialization fails
    }

    // Only proceed if table was initialized successfully
    if (!table) {
      console.error('DataTable was not initialized, skipping further setup');
      return;
    }

    try {
    table.buttons().container().appendTo('#exportContainer');
    } catch (error) {
      console.error('Error setting up export buttons:', error);
    }

    function updateGrandTotal(table) {
      try {
      let total = 0;
      // Loop through only the filtered rows
      table.rows({
        search: 'applied'
      }).every(function() {
          try {
            // The "Total Cost" column is index 6 (0-based)
            const amountText = this.data()[6];
            
            // Check if amountText exists and is a string
            if (amountText && typeof amountText === 'string') {
              // Remove currency symbol, commas, and any HTML tags, then parse as float
              const cleanAmount = amountText.replace(/[₱,]/g, '').replace(/<[^>]*>/g, '');
              const amount = parseFloat(cleanAmount);
        if (!isNaN(amount)) total += amount;
            }
          } catch (rowError) {
            console.warn('Error processing row in updateGrandTotal:', rowError);
            // Continue processing other rows
          }
      });
        
      // Update the footer cell
      $('#grandTotalCell').text('₱' + total.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }));
      } catch (error) {
        console.error('Error in updateGrandTotal:', error);
        // Set a fallback value
        $('#grandTotalCell').text('₱0.00');
      }
    }

    // Initial total
    if (table) {
      try {
    updateGrandTotal(table);
      } catch (error) {
        console.error('Error updating initial grand total:', error);
      }
    }

    // After every filter/search, update the total
    if (table) {
    table.on('draw', function() {
        try {
      updateGrandTotal(table);
        } catch (error) {
          console.error('Error updating grand total after draw:', error);
        }
    });
    }

    // Date range filtering function
    function filterByDateRange() {
      if (!table) {
        console.error('Cannot filter: DataTable not initialized');
        return;
      }
      
      const startDate = $('#filterDateStart').val();
      const endDate = $('#filterDateEnd').val();

      // Custom filtering function for date range
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        const dateReceived = data[0]; // Date Requested column (index 0)

        if (!startDate && !endDate) {
          return true; // No filter applied
        }

        // Check if dateReceived exists and is valid
        if (!dateReceived || dateReceived === '') {
          return false; // Skip rows with no date
        }

        const transactionDate = new Date(dateReceived);
        
        // Check if the date is valid
        if (isNaN(transactionDate.getTime())) {
          return false; // Skip rows with invalid dates
        }

        if (startDate && endDate) {
          const start = new Date(startDate);
          const end = new Date(endDate);
          return transactionDate >= start && transactionDate <= end;
        } else if (startDate) {
          const start = new Date(startDate);
          return transactionDate >= start;
        } else if (endDate) {
          const end = new Date(endDate);
          return transactionDate <= end;
        }

        return true;
      });

      table.draw();
      try {
      updateGrandTotal(table);
      } catch (error) {
        console.error('Error updating grand total after date filter:', error);
      }
    }

    // Date range filter event handlers
    $('#filterDateStart, #filterDateEnd').on('change', function() {
      if (!table) {
        console.error('Cannot apply filter: DataTable not initialized');
        return;
      }
      
      try {
      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
      } catch (error) {
        console.error('Error applying date range filter:', error);
      }
    });

    // Quick date filter buttons
    $('#currentMonth').on('click', function() {
      if (!table) {
        console.error('Cannot apply filter: DataTable not initialized');
        return;
      }
      
      try {
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

      $('#filterDateStart').val(firstDay.toISOString().split('T')[0]);
      $('#filterDateEnd').val(lastDay.toISOString().split('T')[0]);

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
      } catch (error) {
        console.error('Error applying current month filter:', error);
      }
    });

    $('#currentYear').on('click', function() {
      if (!table) {
        console.error('Cannot apply filter: DataTable not initialized');
        return;
      }
      
      try {
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), 0, 1);
      const lastDay = new Date(now.getFullYear(), 11, 31);

      $('#filterDateStart').val(firstDay.toISOString().split('T')[0]);
      $('#filterDateEnd').val(lastDay.toISOString().split('T')[0]);

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
      } catch (error) {
        console.error('Error applying current year filter:', error);
      }
    });

    $('#lastMonth').on('click', function() {
      if (!table) {
        console.error('Cannot apply filter: DataTable not initialized');
        return;
      }
      
      try {
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
      const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);

      $('#filterDateStart').val(firstDay.toISOString().split('T')[0]);
      $('#filterDateEnd').val(lastDay.toISOString().split('T')[0]);

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
      } catch (error) {
        console.error('Error applying last month filter:', error);
      }
    });

    $('#lastYear').on('click', function() {
      if (!table) {
        console.error('Cannot apply filter: DataTable not initialized');
        return;
      }
      
      try {
      const now = new Date();
      const firstDay = new Date(now.getFullYear() - 1, 0, 1);
      const lastDay = new Date(now.getFullYear() - 1, 11, 31);

      $('#filterDateStart').val(firstDay.toISOString().split('T')[0]);
      $('#filterDateEnd').val(lastDay.toISOString().split('T')[0]);

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
      } catch (error) {
        console.error('Error applying last year filter:', error);
      }
    });

    $('#clearDateFilter').on('click', function() {
      if (!table) {
        console.error('Cannot clear filter: DataTable not initialized');
        return;
      }
      
      $('#filterDateStart').val('');
      $('#filterDateEnd').val('');

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      table.draw();
      try {
      updateGrandTotal(table);
      } catch (error) {
        console.error('Error updating grand total after clearing date filter:', error);
      }
    });

    // Category filter functionality
    $('#accountSelect').on('change', function() {
      if (!table) {
        console.error('Cannot filter: DataTable not initialized');
        return;
      }
      
      const selectedCategory = $(this).val();
      
      // Clear any previous search
      table.search('').columns().search('').draw();
      
      if (selectedCategory) {
        // Search in the Description column (index 4) since category info might be in the description
        table.column(4).search(selectedCategory, true, false).draw();
      } else {
        // If 'All Categories' is selected, clear the search
        table.column(4).search('').draw();
      }
      
      // Update the grand total after filtering
      try {
      updateGrandTotal(table);
      } catch (error) {
        console.error('Error updating grand total after category filter:', error);
      }
    });

    // Clear category filter when clicking the clear button if it exists
    $('button[data-clear-filter]').on('click', function() {
      if (!table) {
        console.error('Cannot clear filter: DataTable not initialized');
        return;
      }
      
      $('#accountSelect').val('').trigger('change');
    });

    $('#dtSearch').on('keyup change', function() {
      if (!table) {
        console.error('Cannot search: DataTable not initialized');
        return;
      }
      
      table.search(this.value).draw();
      try {
      updateGrandTotal(table);
      } catch (error) {
        console.error('Error updating grand total after search:', error);
      }
    });



    //view button
    $(document).on('click', '.viewBtn', function() {
      if (!table) {
        console.error('Cannot view: DataTable not initialized');
        return;
      }
      
      console.log('View button clicked!');
      
      // Debug: Check if modal exists in DOM
      console.log('Modal exists in DOM:', $('#viewSupplyModal').length > 0);
      console.log('Modal HTML:', $('#viewSupplyModal').html());
      
      // Get data attributes directly using data() method
      const dateRequested = $(this).data('date-requested');
      const dateNeeded = $(this).data('date-needed');
      const departmentUnit = $(this).data('department-unit');
      const purpose = $(this).data('purpose');
      const salesType = $(this).data('sales-type');
      const category = $(this).data('category');
      const itemName = $(this).data('item-name');
      const brand = $(this).data('brand');
      const color = $(this).data('color');
      const quantityRequested = $(this).data('quantity-requested');
      const unit = $(this).data('unit');
      const requestDescription = $(this).data('request-description');
      const qualityIssued = $(this).data('quality-issued');
      const unitCost = $(this).data('unit-cost');
      const totalCost = $(this).data('total-cost');
      
      console.log('Data retrieved:', {
        dateRequested, dateNeeded, departmentUnit, purpose, salesType, category,
        itemName, brand, color, quantityRequested, unit, requestDescription, qualityIssued, unitCost, totalCost
      });

      // Debug: Check if modal elements exist
      console.log('Modal elements check:');
      console.log('viewDateRequested exists:', $('#viewDateRequested').length > 0);
      console.log('viewDateNeeded exists:', $('#viewDateNeeded').length > 0);
      console.log('viewDepartmentUnit exists:', $('#viewDepartmentUnit').length > 0);
      console.log('viewPurpose exists:', $('#viewPurpose').length > 0);
      console.log('viewSalesType exists:', $('#viewSalesType').length > 0);
      console.log('viewCategory exists:', $('#viewCategory').length > 0);
      console.log('viewItemName exists:', $('#viewItemName').length > 0);
      console.log('viewBrand exists:', $('#viewBrand').length > 0);
      console.log('viewColor exists:', $('#viewColor').length > 0);
      console.log('viewQuantity exists:', $('#viewQuantity').length > 0);
      console.log('viewUnit exists:', $('#viewUnit').length > 0);
      console.log('viewRequestDescription exists:', $('#viewRequestDescription').length > 0);
      console.log('viewQualityIssued exists:', $('#viewQualityIssued').length > 0);
      console.log('viewPrice exists:', $('#viewPrice').length > 0);
      console.log('viewTotalAmount exists:', $('#viewTotalAmount').length > 0);

      // Set values in the modal - using text() instead of val() for paragraph elements
      $('#viewDateRequested').text(dateRequested || 'N/A');
      $('#viewDateNeeded').text(dateNeeded || 'N/A');
      $('#viewDepartmentUnit').text(departmentUnit || 'N/A');
      $('#viewPurpose').text(purpose || 'N/A');
      $('#viewSalesType').text(salesType || 'N/A');
      $('#viewCategory').text(category || 'N/A');
      $('#viewItemName').text(itemName || 'N/A');
      $('#viewBrand').text(brand || 'N/A');
      $('#viewColor').text(color || 'N/A');
      $('#viewQuantity').text(quantityRequested || 'N/A');
      $('#viewUnit').text(unit || 'N/A');
      $('#viewRequestDescription').text(requestDescription || 'N/A');
      $('#viewQualityIssued').text(qualityIssued || 'N/A');
      $('#viewPrice').text(unitCost ? '₱' + unitCost : 'N/A');
      $('#viewTotalAmount').text(totalCost ? '₱' + totalCost : 'N/A');

      // Store the data for the edit button
      $('#viewSupplyModal').data('viewData', {
        requestId: $(this).data('request-id'),
        dateRequested, dateNeeded, departmentUnit, purpose, salesType, category,
        itemName, brand, color, quantityRequested, unit, requestDescription, qualityIssued, unitCost, totalCost
      });
    });

    // Handle Edit button in view modal
    $(document).on('click', '#editFromViewBtn', function() {
      if (!table) {
        console.error('Cannot edit from view: DataTable not initialized');
        return;
      }
      
      const viewData = $('#viewSupplyModal').data('viewData');
      if (viewData) {
        // Close view modal
    $('#viewSupplyModal').modal('hide');
        
        // Set values in edit modal
        $('#editRequestId').val(viewData.requestId || '');
        $('#editDateRequest').val(viewData.dateRequested || '');
        $('#editDateNeeded').val(viewData.dateNeeded || '');
        $('#editDepartmentUnit').val(viewData.departmentUnit || '');
        $('#editPurpose').val(viewData.purpose || '');
        $('#editSalesType').val(viewData.salesType || '');
        $('#editCategorySelect').val(viewData.category || '');
        $('#editItemName').val(viewData.itemName || '');
        $('#editBrand').val(viewData.brand || '');
        $('#editColor').val(viewData.color || '');
        $('#editRequestDescription').val(viewData.requestDescription || '');
        $('#editQualityIssued').val(viewData.qualityIssued || '');
        $('#editQuantity').val(viewData.quantityRequested || '');
        $('#editUnit').val(viewData.unit || '');
        $('#editPrice').val(viewData.unitCost || '');
        $('#editTotalAmount').val(viewData.totalCost || '');

        // Trigger calculation for total
        $('#editQuantity, #editPrice').trigger('input');
        
        // Open edit modal
        $('#editSupplyModal').modal('show');
      }
    });



    //edit button
    $(document).on('click', '.editBtn', function() {
      if (!table) {
        console.error('Cannot edit: DataTable not initialized');
        return;
      }
      
      console.log('Edit button clicked!');
      
      // Debug: Check if edit modal elements exist
      console.log('Edit modal elements check:');
      console.log('editRequestId exists:', $('#editRequestId').length > 0);
      console.log('editDateRequest exists:', $('#editDateRequest').length > 0);
      console.log('editDateNeeded exists:', $('#editDateNeeded').length > 0);
      console.log('editDepartmentUnit exists:', $('#editDepartmentUnit').length > 0);
      console.log('editPurpose exists:', $('#editPurpose').length > 0);
      console.log('editSalesType exists:', $('#editSalesType').length > 0);
      console.log('editCategorySelect exists:', $('#editCategorySelect').length > 0);
      console.log('editItemName exists:', $('#editItemName').length > 0);
      console.log('editBrand exists:', $('#editBrand').length > 0);
      console.log('editColor exists:', $('#editColor').length > 0);
      console.log('editRequestDescription exists:', $('#editRequestDescription').length > 0);
      console.log('editQualityIssued exists:', $('#editQualityIssued').length > 0);
      console.log('editQuantity exists:', $('#editQuantity').length > 0);
      console.log('editUnit exists:', $('#editUnit').length > 0);
      console.log('editPrice exists:', $('#editPrice').length > 0);
      console.log('editTotalAmount exists:', $('#editTotalAmount').length > 0);

      // Get data attributes directly
      const requestId = $(this).data('request-id');
      const dateRequested = $(this).data('date-requested');
      const dateNeeded = $(this).data('date-needed');
      const departmentUnit = $(this).data('department-unit');
      const purpose = $(this).data('purpose');
      const salesType = $(this).data('sales-type');
      const category = $(this).data('category');
      const itemName = $(this).data('item-name');
      const brand = $(this).data('brand');
      const color = $(this).data('color');
      const requestDescription = $(this).data('request-description');
      const qualityIssued = $(this).data('quality-issued');
      const quantityRequested = $(this).data('quantity-requested');
      const unit = $(this).data('unit');
      const unitCost = $(this).data('unit-cost');
      const totalCost = $(this).data('total-cost');

      console.log('Data retrieved for edit:', {
        requestId, dateRequested, dateNeeded, departmentUnit, purpose, salesType, category,
        itemName, brand, color, requestDescription, qualityIssued, quantityRequested, unit, unitCost, totalCost
      });

      // Set values in the edit modal
      $('#editRequestId').val(requestId || '');
      $('#editDateRequest').val(dateRequested || '');
      $('#editDateNeeded').val(dateNeeded || '');
      $('#editDepartmentUnit').val(departmentUnit || '');
      $('#editPurpose').val(purpose || '');
      $('#editSalesType').val(salesType || '');
      $('#editCategorySelect').val(category || '');
      $('#editItemName').val(itemName || '');
      $('#editBrand').val(brand || '');
      $('#editColor').val(color || '');
      $('#editRequestDescription').val(requestDescription || '');
      $('#editQualityIssued').val(qualityIssued || '');
      $('#editQuantity').val(quantityRequested || '');
      $('#editUnit').val(unit || '');
      $('#editPrice').val(unitCost || '');
      $('#editTotalAmount').val(totalCost || '');

      // Trigger calculation for total
      $('#editQuantity, #editPrice').trigger('input');
    });
  });
</script>