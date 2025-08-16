<?php
$pageTitle = 'Supplier Request';
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

$sql = "SELECT * FROM supply_request";
$result = $conn->query($sql);


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

<?php include('../includes/navbar.php'); ?>
<div class="container" style="margin-top: 100px;">

  <!-- User Information Display -->
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card border-dark">
        <div class="card-header py-2" style="background-color: #1a5f3c; color: white;">
          <h6 class="mb-0"><i class="fas fa-user-circle me-2"></i>Current User Information</h6>
        </div>
        <div class="card-body py-2">
          <div class="row align-items-center">
            <div class="col-md-8">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  <i class="fas fa-user-circle fa-2x" style="color: #1a5f3c;"></i>
                </div>
                <div>
                  <h5 class="mb-1"><?= htmlspecialchars($user_name) ?></h5>
                  <p class="mb-1 text-muted">
                    <?php if (!empty($user_type)): ?>
                      | <strong>Position:</strong> <?= htmlspecialchars(strtoupper($user_type)) ?>
                    <?php endif; ?>
                  </p>
                  <?php if (empty($user_id)): ?>
                    <div class="mt-2">
                      <small class="text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>Warning: User ID not found in session
                      </small>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="col-md-4 text-end">
              <button class="btn btn-sm" style="background-color: #fd7e14; color: white;" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                <i class="fas fa-plus me-2"></i>New Supply Request
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <h3>Supply Request</h3>
    <div>
      <button class="btn btn-sm" style="background-color: #fd7e14; color: white;" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Previous</button>
    </div>
  </div>

  <!-- Request Type Display -->
  <?php if (!empty($request_type)): ?>
    <div class="alert alert-info text-center mb-4" role="alert">
      <i class="fas fa-info-circle me-2"></i>
      <strong>Request Type:</strong>
      <span class="badge bg-primary ms-2">
        <?= ucfirst($request_type) ?>
      </span>
      <button type="button" class="btn btn-sm btn-outline-secondary ms-3" onclick="changeRequestType()">
        <i class="fas fa-edit me-1"></i>Change Type
      </button>
    </div>
  <?php endif; ?>

  <h5 class="text-center">List of employee request</h5>
  <hr>
  <!-- Filter Row -->
  <div class="row align-items-end mb-2 g-2">
    <div class="col-md-2">
      <label for="dtSearch" class="form-label">Search</label>
      <input type="search" id="dtSearch" class="form-control" placeholder="Search...">
    </div>

    <div class="col-md-2">
      <label for="filterDateStart" class="form-label">Date Range (From)</label>
      <input type="date" id="filterDateStart" class="form-control">
    </div>

    <div class="col-md-2">
      <label for="filterDateEnd" class="form-label">Date Range (To)</label>
      <input type="date" id="filterDateEnd" class="form-control">
    </div>

    <div class="col-md-4">
      <label for="filterCategory" class="form-label">Filter by Category</label>
      <select id="filterCategory" class="form-select">
        <option value="">-- Filter by Category --</option>
        <optgroup label="Capital Outlay (CO)">
          <option value="ICT Equipment and Devices">ICT Equipment and Devices</option>
          <option value="Office Equipment">Office Equipment</option>
          <option value="Air Conditioning Units and Cooling Systems">Air Conditioning Units and Cooling Systems</option>
          <option value="Furniture and Fixtures">Furniture and Fixtures</option>
          <option value="Laboratory Equipment">Laboratory Equipment</option>
          <option value="School Building Improvements">School Building Improvements</option>
          <option value="Other Machinery and Equipment">Other Machinery and Equipment</option>
        </optgroup>
        <optgroup label="Maintenance and Other Operating Expenses (MOOE)">
          <option value="Office Supplies and Materials">Office Supplies and Materials</option>
          <option value="Instructional and Learning Materials">Instructional and Learning Materials</option>
          <option value="Janitorial and Sanitation Supplies">Janitorial and Sanitation Supplies</option>
          <option value="Repairs and Maintenance – Buildings and Facilities">Repairs and Maintenance – Buildings and Facilities</option>
          <option value="Repairs and Maintenance – Equipment and Devices">Repairs and Maintenance – Equipment and Devices</option>
          <option value="Electrical and Lighting Supplies">Electrical and Lighting Supplies</option>
          <option value="Medical and First Aid Supplies">Medical and First Aid Supplies</option>
          <option value="Printing and Reproduction Services">Printing and Reproduction Services</option>
          <option value="Subscription, License, and Software Services">Subscription, License, and Software Services</option>
          <option value="Utilities and Facility Services">Utilities and Facility Services</option>
          <option value="Transportation or Delivery Services">Transportation or Delivery Services</option>
          <option value="Construction Materials">Construction Materials</option>
          <option value="Renovation Services">Renovation Services</option>
        </optgroup>
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label d-block">Export</label>
      <div id="exportContainer"></div>
    </div>
  </div>

  <!-- Quick Date Filters -->
  <div class="row mb-2">
    <div class="col-12">
      <label class="form-label">Quick Date Filters:</label>
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-primary btn-sm" id="currentMonth">Current Month</button>
        <button type="button" class="btn btn-outline-primary btn-sm" id="currentYear">Current Year</button>
        <button type="button" class="btn btn-outline-primary btn-sm" id="lastMonth">Last Month</button>
        <button type="button" class="btn btn-outline-primary btn-sm" id="lastYear">Last Year</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearDateFilter">Clear Date Filter</button>
      </div>
    </div>
  </div>
  <hr>
  <!-- Transactions Table -->
  <div class="table-responsive">
    <table id="transactionsTable" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Date Requested</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Date Needed</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Position/Role</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Purpose of the request</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Quantity Requested</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Unit</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Request Description</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Request Type</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Quality Issued</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Unit Cost</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Total Cost</th>
          <th style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;">Action</th>
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
          <tr>
            <td><?= $row['date_requested'] ?></td>
            <td><?= htmlspecialchars($row['date_needed']) ?></td>
            <td><?= $row['department_unit'] ?></td>
            <td><?= htmlspecialchars($row['purpose']) ?></td>
            <td><?= htmlspecialchars($row['quantity_requested']) ?></td>
            <td><?= htmlspecialchars($row['unit']) ?></td>
            <td><?= $row['request_description'] ?></td>
            <td style="text-transform: uppercase;"><?= htmlspecialchars($row['request_type']) ?></td>
            <td><?= htmlspecialchars($row['quality_issued']) ? htmlspecialchars($row['quality_issued']) : '<span style="color: red; font-weight: bold;">No Issued Recorded</span>'; ?></td>
            <td>
              ₱<?= !empty($unit_cost)
                  ? number_format($unit_cost, 2)
                  : ' <span style="color: red; font-weight: bold;">No Unit Cost Recorded</span>'; ?>
            </td>
            <td>
              ₱<?= !empty($computed_total_cost)
                  ? number_format($computed_total_cost, 2)
                  : ' <span style="color: red; font-weight: bold;">No Total Cost Recorded</span>'; ?>
            </td>
            <td>
              <button
                class="btn btn-sm editBtn" style="background: linear-gradient(135deg, #1a5f3c, #2d7a4d); color: white;"
                data-request-id="<?= $row['request_id'] ?>"
                data-date-requested="<?= htmlspecialchars($row['date_requested']) ?>"
                data-date-needed="<?= htmlspecialchars($row['date_needed']) ?>"
                data-department-unit="<?= trim($row['department_unit']) ?>"
                data-purpose="<?= trim($row['purpose']) ?>"
                data-sales-type="<?= htmlspecialchars($row['sales_type']) ?>"
                data-category="<?= htmlspecialchars($row['category']) ?>"
                data-quantity-requested="<?= htmlspecialchars($row['quantity_requested']) ?>"
                data-unit="<?= $row['unit'] ?>"
                data-request-description="<?= htmlspecialchars($row['request_description']) ?>"
                data-quality-issued="<?= htmlspecialchars($row['quality_issued']) ?>"
                data-unit-cost="<?= $unit_cost ?>"
                data-total-cost="<?= $computed_total_cost ?>"
                data-bs-toggle="modal"
                data-bs-target="#editSupplyModal"><i class="fas fa-edit me-1"></i>
                Edit Request Details
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="10" class="text-end fw-bold">Total:</td>
          <td class="fw-bold" id="grandTotalCell">₱<?= number_format($total_sum, 2) ?></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php include '../modals/add_supply_request.php'; ?>
<?php include '../modals/edit_supply_request.php'; ?>

<?php include '../includes/footer.php'; ?>

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
        // If no request type, redirect back to dashboard
        setTimeout(function() {
          window.location.href = '../dashboard.php';
        }, 1000);
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
    const table = $('#transactionsTable').DataTable({
      dom: 'Brtip',
      buttons: [{
          extend: 'excelHtml5',
          text: '<i class="fa-solid fa-file-excel"></i>',
          className: 'btn btn-sm',
          customize: function(xlsx) {
            let sheet = xlsx.xl.worksheets['sheet1.xml'];
            $('row c[r]', sheet).attr('s', '0');
          }
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fa-solid fa-file-pdf"></i>',
          title: 'Supply Request Report',
          className: 'btn btn-sm',
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
          text: '<i class="fa-solid fa-print"></i>',
          title: 'Supplier Request',
          className: 'btn btn-sm',
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

    table.buttons().container().appendTo('#exportContainer');

    function updateGrandTotal(table) {
      let total = 0;
      // Loop through only the filtered rows
      table.rows({
        search: 'applied'
      }).every(function() {
        // The "Amount" column is index 9 (0-based)
        const amountText = this.data()[9];
        // Remove currency symbol and commas, then parse as float
        const amount = parseFloat(amountText.replace(/[₱,]/g, ''));
        if (!isNaN(amount)) total += amount;
      });
      // Update the footer cell
      $('#grandTotalCell').text('₱' + total.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }));
    }

    // Initial total
    updateGrandTotal(table);

    // After every filter/search, update the total
    table.on('draw', function() {
      updateGrandTotal(table);
    });

    // Date range filtering function
    function filterByDateRange() {
      const startDate = $('#filterDateStart').val();
      const endDate = $('#filterDateEnd').val();

      // Custom filtering function for date range
      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        const dateReceived = data[0]; // Date Received column

        if (!startDate && !endDate) {
          return true; // No filter applied
        }

        const transactionDate = new Date(dateReceived);

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
      updateGrandTotal(table);
    }

    // Date range filter event handlers
    $('#filterDateStart, #filterDateEnd').on('change', function() {
      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
    });

    // Quick date filter buttons
    $('#currentMonth').on('click', function() {
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

      $('#filterDateStart').val(firstDay.toISOString().split('T')[0]);
      $('#filterDateEnd').val(lastDay.toISOString().split('T')[0]);

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
    });

    $('#currentYear').on('click', function() {
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), 0, 1);
      const lastDay = new Date(now.getFullYear(), 11, 31);

      $('#filterDateStart').val(firstDay.toISOString().split('T')[0]);
      $('#filterDateEnd').val(lastDay.toISOString().split('T')[0]);

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
    });

    $('#lastMonth').on('click', function() {
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
      const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);

      $('#filterDateStart').val(firstDay.toISOString().split('T')[0]);
      $('#filterDateEnd').val(lastDay.toISOString().split('T')[0]);

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
    });

    $('#lastYear').on('click', function() {
      const now = new Date();
      const firstDay = new Date(now.getFullYear() - 1, 0, 1);
      const lastDay = new Date(now.getFullYear() - 1, 11, 31);

      $('#filterDateStart').val(firstDay.toISOString().split('T')[0]);
      $('#filterDateEnd').val(lastDay.toISOString().split('T')[0]);

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      filterByDateRange();
    });

    $('#clearDateFilter').on('click', function() {
      $('#filterDateStart').val('');
      $('#filterDateEnd').val('');

      // Clear previous custom filters
      $.fn.dataTable.ext.search.pop();
      table.draw();
      updateGrandTotal(table);
    });

    $('#filterCategory').on('change', function() {
      table.column(4).search(this.value).draw();
      updateGrandTotal(table);
    });

    $('#dtSearch').on('keyup change', function() {
      table.search(this.value).draw();
      updateGrandTotal(table);
    });




    //edit button
    $(document).on('click', '.editBtn', function() {
      console.log('Date Requested:', $(this).data('date-requested'));
      console.log('Request ID:', $(this).data('request-id'));

      $('#editRequestId').val($(this).data('request-id'));
      $('#editDateRequest').val($(this).data('date-requested'));
      $('#editDateNeeded').val($(this).data('date-needed'));
      $('#editDepartmentUnit').val($(this).data('department-unit'));
      $('#editPurpose').val($(this).data('purpose'));
      $('#editSalesType').val($(this).data('sales-type'));
      $('#editCategory').val($(this).data('category'));
      $('#editRequestDescription').val($(this).data('request-description'));
      $('#editQualityIssued').val($(this).data('quality-issued'));
      $('#editQuantity').val($(this).data('quantity-requested'));
      $('#editUnit').val($(this).data('unit'));
      $('#editPrice').val($(this).data('unit-cost'));
      $('#editTotalAmount').val($(this).data('total-cost'));

      // Trigger calculation for total
      $('#editQuantity, #editPrice').trigger('input');

      // Debug: Log form data before submission
      $('#editSupplyModal form').on('submit', function(e) {
        console.log('Form submitting...');
        console.log('Request ID:', $('#editRequestId').val());
        console.log('Date Requested:', $('#editDateRequest').val());
        console.log('Total Cost:', $('#editTotalAmount').val());
      });
    });
  });
</script>