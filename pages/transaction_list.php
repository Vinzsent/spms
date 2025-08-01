<?php
$pageTitle = 'Supplier Transactions';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';


$user_type = $_SESSION['user_type']?? '';

$dashboard_link = ($user_type == 'Admin') ? '../admin_dashboard.php' : '../dashboard.php';

$sql = "SELECT st.*, s.supplier_name 
        FROM supplier_transaction st
        JOIN supplier s ON s.supplier_id = st.supplier_id
        ORDER BY st.date_received DESC";
$result = $conn->query($sql);
?>



<?php include('../includes/navbar.php'); ?>
  <div class="container" style="margin-top: 120px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Recieved Items</h3>
      <div>
        <button class="btn btn-secondary me-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Previous</button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTransactionModal">+ New Transaction</button>
      </div>
    </div>
    <hr>
    <!-- Filter Row -->
    <div class="row align-items-end mb-4 g-2">
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
            <option value="Office Supplies and Materials">Office Supplies and Materials</option>
            <option value="Air Conditioning Units and Cooling Systems">Air Conditioning Units and Cooling Systems</option>
            <option value="Furniture and Fixtures">Furniture and Fixtures</option>
            <option value="Laboratory Equipment">Laboratory Equipment</option>
            <option value="School Building Improvements">School Building Improvements</option>
            <option value="Other Machinery and Equipment">Other Machinery and Equipment</option>
            <option value="Subscription, License, and Software Services">Subscription, License, and Software Services</option>
            <option value="Furniture and Fixtures">Furniture and Fixtures</option>
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
            <option value="Repairs and Maintenance – Equipment and Devices">Repairs and Maintenance – Equipment and Devices</option>
          </optgroup>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label d-block">Export</label>
        <div id="exportContainer"></div>
      </div>
    </div>
    
    <!-- Quick Date Filters -->
    <div class="row mb-3">
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
            <th>Date Received</th>
            <th>Invoice No.</th>
            <th>Sales Type</th>
            <th>Supplier Name</th>
            <th>Category</th>
            <th>Item Description</th>
            <th>Status</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Unit Price</th>
            <th>Amount</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $total_sum = 0;
          while ($row = $result->fetch_assoc()): 
            $total_sum += $row['amount'];
          ?>
            <tr>
              <td><?= $row['date_received'] ?></td>
              <td><?= htmlspecialchars($row['invoice_no']) ?></td>
              <td><?= $row['sales_type'] ?></td>
              <td><?= htmlspecialchars($row['supplier_name']) ?></td>
              <td><?= htmlspecialchars($row['category']) ?></td>
              <td><?= htmlspecialchars($row['item_description']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= $row['unit'] ?></td>
              <td>₱<?= number_format($row['unit_price'], 2) ?></td>
              <td>₱<?= number_format($row['amount'], 2) ?></td>
                             <td>
                 <div class="btn-group" role="group">
                   <button
                     class="btn btn-sm btn-info specsBtn"
                     data-id="<?= $row['transaction_id'] ?>"
                     data-date="<?= htmlspecialchars($row['date_received']) ?>"
                     data-invoice="<?= htmlspecialchars($row['invoice_no']) ?>"
                     data-description="<?= htmlspecialchars($row['item_description']) ?>"
                     data-supplier="<?= htmlspecialchars($row['supplier_name']) ?>"
                     data-category="<?= trim($row['category']) ?>"
                     data-bs-toggle="modal"
                     data-bs-target="#specificationsModal">
                     <i class="fas fa-cogs"></i> Specs
                   </button>
                   <button
                     class="btn btn-sm btn-warning editBtn"
                     data-id="<?= $row['transaction_id'] ?>"
                     data-date="<?= htmlspecialchars($row['date_received']) ?>"
                     data-invoice="<?= htmlspecialchars($row['invoice_no']) ?>"
                     data-sales="<?= trim($row['sales_type']) ?>"
                     data-category="<?= trim($row['category']) ?>"
                     data-description="<?= htmlspecialchars($row['item_description']) ?>"
                     data-status="<?= htmlspecialchars($row['status'])?>"
                     data-quantity="<?= $row['quantity'] ?>"
                     data-unit="<?= $row['unit'] ?>"
                     data-price="<?= $row['unit_price'] ?>"
                     data-amount="<?= $row['amount'] ?>"
                     data-supplier="<?= htmlspecialchars($row['supplier_name']) ?>"
                     data-bs-toggle="modal"
                     data-bs-target="#editTransactionModal">
                     <i class="fas fa-edit"></i> Edit
                   </button>
                   <button
                     class="btn btn-sm btn-success issuedBtn"
                     data-id="<?= $row['transaction_id'] ?>"
                     data-date="<?= htmlspecialchars($row['date_received']) ?>"
                     data-invoice="<?= htmlspecialchars($row['invoice_no']) ?>"
                     data-sales="<?= trim($row['sales_type']) ?>"
                     data-category="<?= trim($row['category']) ?>"
                     data-description="<?= htmlspecialchars($row['item_description']) ?>"
                     data-status="<?= htmlspecialchars($row['status'])?>"
                     data-quantity="<?= $row['quantity'] ?>"
                     data-unit="<?= $row['unit'] ?>"
                     data-price="<?= $row['unit_price'] ?>"
                     data-amount="<?= $row['amount'] ?>"
                     data-supplier="<?= htmlspecialchars($row['supplier_name']) ?>"
                     data-bs-toggle="modal"
                     data-bs-target="#issuedModal">
                     <i class="fas fa-check"></i> Issued
                   </button>
                 </div>
               </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="9" class="text-end fw-bold">Total:</td>
            <td colspan="2" class="fw-bold" id="grandTotalCell">₱<?= number_format($total_sum, 2) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <?php include '../modals/add_transaction_modal.php'; ?>
  <?php include '../modals/edit_transaction_modal.php'; ?>
  <?php include '../modals/view_specifications_modal.php'; ?>
  
     <!-- Issued Information Modal -->
   <div class="modal fade" id="issuedModal" tabindex="-1" aria-labelledby="issuedModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-lg">
       <div class="modal-content">
         <div class="modal-header bg-success text-white">
           <h5 class="modal-title" id="issuedModalLabel">
             <i class="fas fa-check-circle me-2"></i>Transaction Details
           </h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="issuedForm" action="../actions/update_transaction_status.php" method="POST">
           <div class="modal-body">
             <div class="row">
               <div class="col-md-6">
                 <div class="info-card mb-3">
                   <h6 class="info-title"><i class="fas fa-calendar me-2"></i>Transaction Info</h6>
                   <div class="info-content">
                     <div class="info-item"><span class="info-label">Date Received:</span><span class="info-value" id="issuedDate"></span></div>
                     <div class="info-item"><span class="info-label">Invoice No.:</span><span class="info-value" id="issuedInvoice"></span></div>
                     <div class="info-item"><span class="info-label">Supplier:</span><span class="info-value" id="issuedSupplier"></span></div>
                     <div class="info-item"><span class="info-label">Sales Type:</span><span class="info-value" id="issuedSales"></span></div>
                     <div class="info-item"><span class="info-label">Category:</span><span class="info-value" id="issuedCategory"></span></div>
                   </div>
                 </div>
               </div>
               <div class="col-md-6">
                 <div class="info-card mb-3">
                   <h6 class="info-title"><i class="fas fa-box me-2"></i>Item Details</h6>
                   <div class="info-content">
                     <div class="info-item"><span class="info-label">Description:</span><span class="info-value" id="issuedDescription"></span></div>
                     <div class="info-item"><span class="info-label">Quantity:</span><span class="info-value badge bg-primary text-white" id="issuedQuantity"></span></div>
                     <div class="info-item"><span class="info-label">Unit:</span><span class="info-value" id="issuedUnit"></span></div>
                     <div class="info-item"><span class="info-label">Unit Price:</span><span class="info-value text-success fw-bold" id="issuedUnitPrice"></span></div>
                     <div class="info-item"><span class="info-label">Amount:</span><span class="info-value total-value" id="issuedAmount"></span></div>
                     <div class="info-item"><span class="info-label">Status:</span><span class="info-value" id="issuedStatus"></span></div>
                   </div>
                 </div>
               </div>
             </div>
             
             <!-- Hidden form fields -->
             <input type="hidden" name="transaction_id" id="issuedTransactionId">
             <input type="hidden" name="new_status" value="Issued">
             
             <!-- Confirmation message -->
             <div class="alert alert-info">
               <i class="fas fa-info-circle me-2"></i>
               <strong>Confirmation:</strong> Are you sure you want to mark this transaction as "Issued"? This action cannot be undone.
             </div>
           </div>
           <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
               <i class="fas fa-times me-1"></i>Cancel
             </button>
             <button type="submit" class="btn btn-success" id="confirmIssuedModalBtn">
               <i class="fas fa-check me-1"></i>Confirm Issued
             </button>
           </div>
         </form>
       </div>
     </div>
   </div>

  <style>
    .info-card {
      background: #f8f9fa;
      border-radius: 10px;
      padding: 1.5rem;
      border-left: 4px solid #28a745;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .info-title { color: #28a745; font-weight: 600; margin-bottom: 1rem; font-size: 1.1rem; }
    .info-content { display: flex; flex-direction: column; gap: 0.75rem; }
    .info-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #e9ecef; }
    .info-item:last-child { border-bottom: none; }
    .info-label { font-weight: 600; color: #495057; min-width: 140px; }
    .info-value { color: #212529; text-align: right; flex: 1; }
    .total-value { font-size: 1.1rem; font-weight: 700; color: #155724; }
    .modal-header { background: linear-gradient(135deg, #28a745, #20c997); }
    .btn-close-white { filter: brightness(0) invert(1); }
    .badge { font-size: 0.9rem; padding: 0.5rem 0.75rem; }
  </style>

<?php include '../includes/footer.php'; ?>
<script>
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
        extend: 'print',
        text: '<i class="fa-solid fa-print"></i>',
        title: 'Supplier Transactions',
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
    table.rows({ search: 'applied' }).every(function() {
      // The "Amount" column is index 9 (0-based)
      const amountText = this.data()[9];
      // Remove currency symbol and commas, then parse as float
      const amount = parseFloat(amountText.replace(/[₱,]/g, ''));
      if (!isNaN(amount)) total += amount;
    });
    // Update the footer cell
    $('#grandTotalCell').text('₱' + total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
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

  $(document).on('click', '.editBtn', function() {
    console.log('Date Received:', $(this).data('date_received'));
    $('#editTransactionId').val($(this).data('id'));
    $('#editDateReceived').val($(this).data('date'));
    $('#editInvoiceNo').val($(this).data('invoice'));
    $('#editDescription').val($(this).data('description'));
    $('#editQuantity').val($(this).data('quantity'));
    $('#editUnit').val($(this).data('unit'));
    $('#editPrice').val($(this).data('price'));
    $('#editSalesType').val($(this).data('sales'));
    $('#editCategory').val($(this).data('category'));
    // Trigger calculation for total
    $('#editQuantity, #editPrice').trigger('input');
  });

     // Issued button click handler
   $(document).on('click', '.issuedBtn', function() {
     // Populate modal fields
     $('#issuedDate').text($(this).data('date'));
     $('#issuedInvoice').text($(this).data('invoice'));
     $('#issuedSupplier').text($(this).data('supplier'));
     $('#issuedSales').text($(this).data('sales'));
     $('#issuedCategory').text($(this).data('category'));
     $('#issuedDescription').text($(this).data('description'));
     $('#issuedQuantity').text($(this).data('quantity'));
     $('#issuedUnit').text($(this).data('unit'));
     $('#issuedUnitPrice').text('₱' + parseFloat($(this).data('price')).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
     $('#issuedAmount').text('₱' + parseFloat($(this).data('amount')).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
     $('#issuedStatus').text($(this).data('status'));
     
     // Populate hidden form field with transaction ID
     $('#issuedTransactionId').val($(this).data('id'));
   });

       // Form submission handler
    $('#issuedForm').on('submit', function(e) {
      e.preventDefault();
      
      // Show loading state
      $('#confirmIssuedModalBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');
      
      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
          // Reset button state
          $('#confirmIssuedModalBtn').prop('disabled', false).html('<i class="fas fa-check me-1"></i>Confirm Issued');
          
          if (response.success) {
            // Update the status in the table
            var row = table.row(function(idx, data, node) {
              return data[0] === $('#issuedDate').text() && data[1] === $('#issuedInvoice').text();
            });
            
            if (row.length > 0) {
              var data = row.data();
              data[6] = 'Issued'; // Update status column (index 6)
              row.data(data).draw();
              
              // Update the status cell display
              var statusCell = row.node().cells[6];
              $(statusCell).html('<span class="badge bg-success">Issued</span>');
            }
            
            // Close modal
            $('#issuedModal').modal('hide');
            
            // Show success message
            alert('Status updated to Issued successfully!');
            
            // Reload page to refresh the total calculation
            setTimeout(function() {
              location.reload();
            }, 1000);
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: function(xhr, status, error) {
          // Reset button state
          $('#confirmIssuedModalBtn').prop('disabled', false).html('<i class="fas fa-check me-1"></i>Confirm Issued');
          
          console.log('AJAX Error:', xhr.responseText);
          alert('An error occurred while updating the status. Please try again.');
        }
      });
    });

    // Specifications button click handler
    $(document).on('click', '.specsBtn', function() {
      const transactionId = $(this).data('id');
      const dateReceived = $(this).data('date');
      const invoiceNo = $(this).data('invoice');
      const description = $(this).data('description');
      const supplier = $(this).data('supplier');
      const category = $(this).data('category');

      // Populate modal header info
      $('#specTransactionInfo').text(`Invoice: ${invoiceNo} (${dateReceived})`);
      $('#specItemDescription').text(description);
      $('#specSupplier').text(supplier);
      $('#specCategory').text(category);
      $('#specTransactionId').val(transactionId);

      // Clear previous form data
      $('#specificationsForm')[0].reset();
      $('#specificationsStatus').hide();

      // Load existing specifications if any
      $.ajax({
        url: '../actions/get_specifications.php',
        type: 'GET',
        data: { transaction_id: transactionId },
        dataType: 'json',
        success: function(response) {
          if (response.success && response.data) {
            // Populate form with existing data
            $('#specBrand').val(response.data.brand || '');
            $('#specSerialNumber').val(response.data.serial_number || '');
            $('#specType').val(response.data.type || '');
            $('#specSize').val(response.data.size || '');
            $('#specModel').val(response.data.model || '');
            $('#specWarranty').val(response.data.warranty_info || '');
            $('#specNotes').val(response.data.additional_notes || '');
            
            $('#specificationsStatus').removeClass('alert-warning').addClass('alert-success').show();
            $('#specificationsStatusText').html('<i class="fas fa-check-circle me-2"></i>Existing specifications loaded');
          } else {
            $('#specificationsStatus').removeClass('alert-success').addClass('alert-warning').show();
            $('#specificationsStatusText').html('<i class="fas fa-exclamation-triangle me-2"></i>No specifications found for this item. You can add new specifications below.');
          }
        },
        error: function(xhr, status, error) {
          console.log('Error loading specifications:', error);
          $('#specificationsStatus').removeClass('alert-success').addClass('alert-warning').show();
          $('#specificationsStatusText').html('<i class="fas fa-exclamation-triangle me-2"></i>Error loading specifications. You can still add new specifications below.');
        }
      });
    });

    // Specifications form submission handler
    $('#specificationsForm').on('submit', function(e) {
      e.preventDefault();
      
      // Show loading state
      $('#saveSpecificationsBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
      
      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
          // Reset button state
          $('#saveSpecificationsBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Specifications');
          
          if (response.success) {
            // Show success message
            $('#specificationsStatus').removeClass('alert-warning alert-danger').addClass('alert-success').show();
            $('#specificationsStatusText').html('<i class="fas fa-check-circle me-2"></i>' + response.message);
            
            // Auto-hide success message after 3 seconds
            setTimeout(function() {
              $('#specificationsStatus').fadeOut();
            }, 3000);
          } else {
            // Show error message
            $('#specificationsStatus').removeClass('alert-warning alert-success').addClass('alert-danger').show();
            $('#specificationsStatusText').html('<i class="fas fa-exclamation-circle me-2"></i>Error: ' + response.message);
          }
        },
        error: function(xhr, status, error) {
          // Reset button state
          $('#saveSpecificationsBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Specifications');
          
          console.log('AJAX Error:', xhr.responseText);
          $('#specificationsStatus').removeClass('alert-warning alert-success').addClass('alert-danger').show();
          $('#specificationsStatusText').html('<i class="fas fa-exclamation-circle me-2"></i>An error occurred while saving specifications. Please try again.');
        }
      });
    });
});
</script>