<?php
$pageTitle = 'Office Supply Requests';
include '../includes/auth.php';
include '../includes/db.php';
include '../includes/header.php';

// Check role
$user_type = $_SESSION['user_type'] ?? $_SESSION['user']['user_type'] ?? '';
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? $_SESSION['user']['id'] ?? '';

$allowed_roles = ['supply in-charge', 'admin'];
if (!in_array(strtolower(trim($user_type)), $allowed_roles)) {
  echo '<div class="container mt-5"><div class="alert alert-danger" role="alert">
          <h4 class="alert-heading">Access Denied</h4>
          <p>You do not have permission to view this page. Restricted to Supply In-charge and Admin.</p>
          <button class="btn btn-primary" onclick="window.history.back()">Go Back</button>
        </div></div>';
  include '../includes/footer.php';
  exit;
}

$office = $_GET['office'] ?? '';
if (empty($office)) {
  echo "<script>alert('Invalid office specified.'); window.location.href='supply_offices_request.php';</script>";
  exit;
}

// Fetch queries
$sql = "SELECT * FROM supply_request WHERE department_unit = ? ORDER BY date_requested DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $office);
$stmt->execute();
$result = $stmt->get_result();

// Get user name
$user_name = $_SESSION['name'] ?? $_SESSION['user']['name'] ?? 'Unknown User';

// Fetch Active School Year
$sy_query = $conn->query("SELECT school_year_name FROM school_year WHERE current_year = 'Yes' LIMIT 1");
if ($sy_query && $sy_query->num_rows > 0) {
    $current_sy = $sy_query->fetch_assoc()['school_year_name'];
} else {
    // Fallback if no active year is set
    $sy_fallback = $conn->query("SELECT school_year_name FROM school_year ORDER BY shoo_year_id DESC LIMIT 1");
    $current_sy = ($sy_fallback && $sy_fallback->num_rows > 0) ? $sy_fallback->fetch_assoc()['school_year_name'] : '2025-2026';
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


  /* ─── Page Layout ─── */
  .wrap {
    margin-left: 280px;
    min-height: 100vh;
    padding-top: 10px;
  }

  #exportContainer {
    min-height: 40px;
    padding: 0;
  }

  #exportContainer .btn {
    margin: 2px;
    font-size: 11px;
    padding: 5px 10px;
    height: 32px;
  }

  /* ─── Print Styles (Long Bond Paper) ─── */
  @media print {
    @page {
      size: 8.5in 13in;
      margin: 0.5in;
    }

    body {
      background: #fff;
      font-size: 12px;
    }

    .sidebar,
    .d-print-none,
    #exportContainer,
    .navbar,
    .btn,
    .dataTables_filter,
    .dataTables_info,
    .dataTables_paginate {
      display: none !important;
    }

    .wrap {
      margin-left: 0 !important;
      padding-top: 0 !important;
    }

    .container-fluid {
      padding: 0 !important;
    }

    .card {
      border: none !important;
      box-shadow: none !important;
    }

    .table {
      width: 100% !important;
      border-collapse: collapse !important;
      margin-top: 20px !important;
    }

    .table th,
    .table td {
      border: none !important;
      padding: 6px !important;
      font-size: 11px !important;
    }

    .table thead {
      background-color: #eee !important;
      -webkit-print-color-adjust: exact;
    }

    .print-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .print-header h2 {
      margin: 0;
      font-size: 18px;
      font-weight: bold;
    }

    .print-header p {
      margin: 5px 0;
      font-size: 14px;
    }

    .signature-row {
      display: flex;
      justify-content: space-between;
      margin-top: 50px;
      page-break-inside: avoid;
    }

    .signature-box {
      width: 30%;
      text-align: center;
    }

    .sig-line {
      border-bottom: 1px solid #000;
      margin-top: 40px;
      margin-bottom: 5px;
    }
  }
</style>

<?php include '../includes/sidebar.php'; ?>

<div class="wrap">
  <div class="container-fluid px-4" style="margin-top: 70px;">
    <!-- Print-Only Header -->
    <div class="d-none d-print-block print-header">
      <h2>DAVAO CENTRAL COLLEGE</h2>
      <p>Supply Requisition - <?= htmlspecialchars($office) ?></p>
      <p style="font-size: 12px; color: #666;">Date Generated: <?= date('F d, Y') ?></p>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
      <h3 style="font-size: 20px; color: #1a5f3c; font-weight: 700;">
        <i class="fas fa-folder-open me-2"></i>Office: <?= htmlspecialchars($office) ?>
      </h3>
      <div class="d-flex gap-2">
        <a href="supply_offices_request.php" class="btn btn-sm btn-outline-secondary" style="padding: 6px 12px; font-weight: 500;">
          <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <button class="btn btn-sm btn-primary shadow-sm" style="background-color: #fd7e14; border: none; padding: 6px 15px; font-weight: 600;" data-bs-toggle="modal" data-bs-target="#addOfficeSupplyModal">
          <i class="fas fa-plus-circle me-1"></i> New Request
        </button>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
      <div class="card-body p-4">
        <div class="row mb-4 align-items-center">
          <div class="col-md-6">
            <h5 class="mb-0" style="font-size: 16px; font-weight: 600; color: #333;">Transaction History</h5>
            <small class="text-muted">Manage all requisitions for this department</small>
          </div>
          <div class="col-md-6 text-end">
            <div id="exportContainer" class="d-flex flex-wrap gap-1 justify-content-end"></div>
          </div>
        </div>

        <div class="table-responsive">
          <table id="officeTransactionsTable" class="table table-hover align-middle">
            <thead style="background-color: #f8f9fa;">
              <tr style="font-size: 12px; color: #555; text-transform: uppercase; letter-spacing: 0.5px;">
                <th class="border-0 px-3 py-3">ITEM</th>
                <th class="border-0 px-3 py-3">DATE REQUESTED <span style="font-weight: 200;" class="text-gray">(YY/MM/DD)</span></th>
                <th class="border-0 px-3 py-3">DESCRIPTION</th>
                <th class="border-0 px-3 py-3">QTY REQUESTED</th>
                <th class="border-0 px-3 py-3">UNIT</th>
                <th class="border-0 px-3 py-3">UNIT PRICE</th>
                <th class="border-0 px-3 py-3">TOTAL</th>
                <th class="border-0 px-3 py-3 text-center d-print-none">Action</th>
              </tr>
            </thead>
            <tbody style="font-size: 13px;">
              <?php
              $total_sum = 0;
              while ($row = $result->fetch_assoc()):
                $quantity = (float)($row['quantity_requested'] ?? 0);
                $unit_cost = (float)($row['unit_cost'] ?? 0);
                $computed_total = $quantity * $unit_cost;
                $total_sum += $computed_total;
              ?>
                <tr>
                  <td class="px-3"><?= htmlspecialchars($row['item_number'] ?? '0') ?></td>
                  <td class="px-3"><?= htmlspecialchars($row['date_requested']) ?></td>
                  <td class="px-3" style="max-width: 300px;">
                    <span class="fw-bold d-block"><?= htmlspecialchars($row['item_name']) ?></span>
                    <?php if (strcasecmp(trim($row['item_name']), trim($row['request_description'])) !== 0 && !empty(trim($row['request_description']))): ?>
                      <small class="text-muted d-block"><?= htmlspecialchars($row['request_description']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td class="px-3 text-center"><?= htmlspecialchars($row['quantity_requested']) ?></td>
                  <td class="px-3"><?= htmlspecialchars($row['unit']) ?></td>
                  <td class="px-3">₱<?= number_format($unit_cost, 2) ?></td>
                  <td class="px-3 fw-bold">₱<?= number_format($computed_total, 2) ?></td>
                  <td class="px-3 text-center d-print-none">
                    <button class="btn btn-sm btn-link text-success p-0 editOfficeBtn"
                      title="Edit Record"
                      data-request-id="<?= $row['request_id'] ?>"
                      data-date-requested="<?= htmlspecialchars($row['date_requested']) ?>"
                      data-date-needed="<?= htmlspecialchars($row['date_needed']) ?>"
                      data-purpose="<?= htmlspecialchars($row['purpose']) ?>"
                      data-item-name="<?= htmlspecialchars($row['item_name']) ?>"
                      data-quantity="<?= htmlspecialchars($row['quantity_requested']) ?>"
                      data-unit="<?= htmlspecialchars($row['unit']) ?>"
                      data-description="<?= htmlspecialchars($row['request_description']) ?>"
                      data-quantity-issued="<?= htmlspecialchars($row['quality_issued']) ?>"
                      data-unit-cost="<?= $unit_cost ?>"
                      data-total-cost="<?= $computed_total ?>"
                      data-item-number="<?= htmlspecialchars($row['item_number'] ?? '0') ?>"
                      data-bs-toggle="modal"
                      data-bs-target="#editOfficeSupplyModal">
                      <i class="fas fa-edit fa-lg"></i>
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
            <tfoot style="background-color: #f8f9fa;">
              <tr style="font-size: 14px; font-weight: 700; border: none !important;">
                <td class="text-start py-3 px-3 border-0">GRAND TOTAL:</td>
                <td colspan="5" class="border-0"></td>
                <td class="text-success py-3 px-3 border-0">₱<?= number_format($total_sum, 2) ?></td>
                <td class="d-print-none"></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Formal Grand Total Summary (Print Only) -->
        <div class="d-none d-print-block" style="margin-top: 10px; border: none !important; padding: 10px; width: 33.33%; margin-left: auto;">
          <table style="width: 100%; border: none !important;">
            <tr style="border: none !important;">
              <td style="font-weight: bold; border: none !important; font-size: 14px;">GRAND TOTAL:</td>
              <td style="font-weight: bold; border: none !important; text-align: right; font-size: 14px; color: #1a5f3c;">₱<?= number_format($total_sum, 2) ?></td>
            </tr>
          </table>
        </div>

        <!-- Print-Only Signatures -->
        <div class="d-none d-print-block">
          <div class="signature-row" style="margin-top: 80px;">
            <div class="signature-box">
              <p>Requested By:</p>
              <div class="sig-line"></div>
              <p>Faculty / Staff</p>
            </div>
            <div class="signature-box">
              <p>Checked By:</p>
              <div class="sig-line"></div>
              <p>Immediate Head</p>
            </div>
          </div>
          <div class="signature-row" style="margin-top: 60px;">
            <div class="signature-box">
              <p>Issued By:</p>
              <div class="sig-line"></div>
              <p>Supply In-charge</p>
            </div>
            <div class="signature-box">
              <p>Noted By:</p>
              <div class="sig-line"></div>
              <p>Finance / VPFA</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<?php
// Modals
include '../modals/add_office_supply_modal.php';
include '../modals/edit_office_supply_modal.php';

include '../includes/footer.php';
?>

<!-- Scripts -->
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
  $(document).ready(function() {
    var table = $('#officeTransactionsTable').DataTable({
      dom: 'Brtip',
      buttons: [{
          extend: 'print',
          text: '<i class="fas fa-print"></i> Print',
          className: 'btn btn-sm btn-secondary',
          title: '',
          messageTop: 'Office: <?= htmlspecialchars($office) ?><br>School Year: <?= htmlspecialchars($current_sy) ?>',
          footer: true,
          exportOptions: {
            columns: ':not(:last-child):not(:eq(1))'
          },
          customize: function(win) {
            $(win.document.body).css('font-size', '14pt');
            $(win.document.body).find('table').css('border-collapse', 'collapse');
            $(win.document.body).find('table, th, td').css({
              'border': '1px solid black',
              'font-size': '12pt',
              'padding': '8px'
            });
            $(win.document.head).append('<style>@page { margin: 0; } body { padding: 1in; }</style>');
          }
        },
        {
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Excel',
          className: 'btn btn-sm btn-success',
          title: 'Supply_Requisition_<?= str_replace(' ', '_', $office) ?>',
          messageTop: 'Office: <?= htmlspecialchars($office) ?> | School Year: <?= htmlspecialchars($current_sy) ?> | Date: <?= date('F d, Y') ?>',
          footer: true,
          exportOptions: {
            columns: ':not(:last-child)'
          }
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fas fa-file-pdf"></i> PDF',
          className: 'btn btn-sm btn-danger',
          title: 'DAVAO CENTRAL COLLEGE\nSupply Requisition - <?= htmlspecialchars($office) ?>',
          messageTop: 'School Year: <?= htmlspecialchars($current_sy) ?>\nReport Date: <?= date('F d, Y') ?>',
          footer: true,
          exportOptions: {
            columns: ':not(:last-child):not(:eq(1))'
          },
          customize: function(doc) {
            doc.defaultStyle.fontSize = 8;
            doc.styles.tableHeader.fontSize = 9;
            doc.styles.tableHeader.fillColor = '#1a5f3c';
            doc.pageOrientation = 'landscape';
          }
        }
      ]
    });
    table.buttons().container().appendTo('#exportContainer');

    // Edit Modal Binding
    $('.editOfficeBtn').on('click', function() {
      var modal = $('#editOfficeSupplyModal');
      modal.find('#edit_request_id').val($(this).data('request-id'));
      modal.find('#edit_date_requested').val($(this).data('date-requested'));
      modal.find('#edit_date_needed').val($(this).data('date-needed'));
      modal.find('#edit_purpose').val($(this).data('purpose'));
      modal.find('#edit_item_name').val($(this).data('item-name'));
      modal.find('#edit_quantity').val($(this).data('quantity'));
      modal.find('#edit_unit').val($(this).data('unit'));
      modal.find('#edit_description').val($(this).data('description'));
      modal.find('#edit_quantity_issued').val($(this).data('quantity-issued'));
      modal.find('#edit_unit_cost').val($(this).data('unit-cost'));
      modal.find('#edit_total_cost').val($(this).data('total-cost'));
      modal.find('#edit_item_number').val($(this).data('item-number'));
    });
  });
</script>