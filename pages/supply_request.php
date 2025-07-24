<?php
$pageTitle = 'Supply Request';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

$sql = "SELECT * FROM supply_request";
$result = $conn->query($sql);
?>

<?php include('../includes/navbar.php'); ?>
  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Supply Request</h3>
      <div>
        <a href="../dashboard.php" class="btn btn-secondary me-2">
          <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTransactionModal">+ New Transaction</button>
      </div>
    </div>
    <hr>
    <!-- Filter Row -->
    <div class="row align-items-end mb-4 g-2">
      <div class="col-md-3">
        <label for="dtSearch" class="form-label">Search</label>
        <input type="search" id="dtSearch" class="form-control" placeholder="Search...">
      </div>

      <div class="col-md-3">
        <label for="filterDate" class="form-label">Filter by Date</label>
        <input type="date" id="filterDate" class="form-control">
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
          </optgroup>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label d-block">Export</label>
        <div id="exportContainer"></div>
      </div>
    </div>
    <hr>
    <!-- Transactions Table -->
    <div class="table-responsive">
      <table id="transactionsTable" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Date Requested</th>
            <th>Date Needed</th>
            <th>Department</th>
            <th>Purpose</th>
            <th>Unit Cost</th>
            <th>Total Cost</th>
            <th>Request Quantity</th>
            <th>Unit</th>
            <th>Description</th>
            <th>Quality Issued</th>
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
              <td><?= $row['date_requested'] ?></td>
              <td><?= htmlspecialchars($row['date_needed']) ?></td>
              <td><?= $row['department'] ?></td>
              <td><?= htmlspecialchars($row['purpse']) ?></td>
              <td>₱<?= number_format($row['unit_cost'], 2) ?></td>
              <td>₱<?= number_format($row['total_cost'], 2) ?></td>
              <td><?= $row['request_quantity'] ?></td>
              <td><?= $row['unit'] ?></td>
              <td><?= $row['description'] ?></td>
              <td><?= $row['quality_issued'] ?></td>
                <button
                  class="btn btn-sm btn-warning editBtn"
                  data-id="<?= $row['request_id'] ?>"
                  data-date="<?= htmlspecialchars($row['date_requested']) ?>"
                  data-invoice="<?= htmlspecialchars($row['date_needed']) ?>"
                  data-sales="<?= trim($row['department']) ?>"
                  data-category="<?= trim($row['purpose']) ?>"
                  data-description="<?= htmlspecialchars($row['unit_cost']) ?>"
                  data-quantity="<?= $row['total_cost'] ?>"
                  data-unit="<?= $row['request_quantity'] ?>"
                  data-price="<?= $row['unit'] ?>"
                  data-description="<?= $row['description'] ?>"
                  data-quality_issued="<?= $row['quality_issued'] ?>"
                  data-bs-toggle="modal"
                  data-bs-target="#editTransactionModal">
                  Edit
                </button>
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
            extend: 'pdfHtml5',
            text: '<i class="fa-solid fa-file-pdf"></i>',
            orientation: 'landscape',
            pageSize: 'A4',
            title: 'Supplier Transactions',
            className: 'btn btn-sm',
            footer: true, // <-- include the footer (TOTAL row)
            exportOptions: {
              columns: ':not(:last-child)' // Exclude the last column (Action)
            },
            customize: function(doc) {
              // Set table borders
              doc.content[1].layout = {
                hLineWidth: function(i, node) {
                  return 1; // Horizontal line width
                },
                vLineWidth: function(i, node) {
                  return 1; // Vertical line width
                },
                hLineColor: function(i, node) {
                  return '#2d3748'; // Horizontal line color
                },
                vLineColor: function(i, node) {
                  return '#2d3748'; // Vertical line color
                }
              };

              // Style customization
              doc.styles.tableHeader.fillColor = '#FFFFFF';
              doc.styles.tableHeader.color = '#000000';
              doc.styles.tableBodyEven = {
                fillColor: '#FFFFFF'
              };
              doc.styles.tableBodyOdd = {
                fillColor: '#FFFFFF'
              };

              // Footer row customization
              const footer = doc.content[1].table.body[doc.content[1].table.body.length - 1];
              const totalAmount = footer[footer.length - 1].text;
              
              const newFooter = [
                { text: '', style: 'tableHeader' },
                { text: '', style: 'tableHeader' },
                { text: '', style: 'tableHeader' },
                { text: '', style: 'tableHeader' },
                { text: '', style: 'tableHeader' },
                { text: '', style: 'tableHeader' },
                { text: '', style: 'tableHeader' },
                { text: '', style: 'tableHeader' },
                { text: 'Total:', alignment: 'right', style: 'tableHeader' },
                { text: totalAmount, style: 'tableHeader' }
              ];
              
              doc.content[1].table.body[doc.content[1].table.body.length - 1] = newFooter;
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

      $('#filterDate').on('change', function() {
        const val = this.value;
        table.column(0).search(val ? '^' + val : '', true, false).draw();
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
    });
  </script>