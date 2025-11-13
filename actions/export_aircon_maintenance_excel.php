<?php
// Excel export (HTML table) for maintenance records by aircon_id
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
include '../includes/auth.php';
include '../includes/db.php';

$aircon_id = isset($_GET['aircon_id']) ? (int)$_GET['aircon_id'] : 0;
if ($aircon_id <= 0) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(400);
    echo 'Invalid aircon_id';
    exit;
}

$aircon = [
    'brand' => '',
    'model' => '',
    'serial_number' => ''
];
if ($stmt = $conn->prepare('SELECT brand, model, serial_number FROM aircons WHERE aircon_id = ?')) {
    $stmt->bind_param('i', $aircon_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows) {
        $aircon = $res->fetch_assoc();
    }
    $stmt->close();
}

$sql = "SELECT maintenance_id, service_date, service_type, technician, next_scheduled_date, remarks, created_by, date_created
        FROM aircon_maintenance WHERE aircon_id = ? ORDER BY service_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $aircon_id);
$stmt->execute();
$result = $stmt->get_result();

while (ob_get_level() > 0) { ob_end_clean(); }
$filename = 'maintenance_aircon_' . $aircon_id . '_' . date('Ymd_His') . '.xls';
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

echo "\xEF\xBB\xBF";

$esc = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };
$fmt = function($d) { if (!$d || $d === '0000-00-00' || $d === '0000-00-00 00:00:00') return ''; $t = strtotime($d); return $t ? date('Y-m-d', $t) : $d; };

?>
<html>
<head>
<meta charset="UTF-8" />
<style>
  body { font-family: Arial, Helvetica, sans-serif; }
  .title { text-align: center; font-size: 18px; font-weight: bold; margin: 10px 0 6px 0; }
  .subtitle { text-align: center; color: #555; font-size: 12px; margin: 0 0 12px 0; }
  .meta { margin: 8px 0 12px 0; }
  .meta td { padding: 4px 8px; }
  table.export { width: 100%; border-collapse: collapse; table-layout: fixed; }
  table.export col.date { width: 12%; }
  table.export col.type { width: 15%; }
  table.export col.tech { width: 15%; }
  table.export col.next { width: 12%; }
  table.export col.remarks { width: 28%; }
  table.export col.created { width: 10%; }
  table.export col.createdat { width: 8%; }
  th, td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
  th { background: #e8f4fa; font-weight: 600; }
  .text { mso-number-format:"\@"; }
  .date { mso-number-format:"yyyy-mm-dd"; }
  .nowrap { white-space: nowrap; }
  .wrap { word-wrap: break-word; }
</style>
</head>
<body>
  <div class="title">Aircon Maintenance Export</div>
  <div class="subtitle">Generated: <?= $esc(date('Y-m-d H:i')) ?></div>
  <table class="meta">
    <tr>
      <td><strong>Brand:</strong> <?= $esc($aircon['brand']) ?></td>
      <td><strong>Model:</strong> <?= $esc($aircon['model']) ?></td>
      <td><strong>Serial:</strong> <?= $esc($aircon['serial_number']) ?></td>
    </tr>
  </table>

  <table class="export">
    <colgroup>
      <col class="date" />
      <col class="type" />
      <col class="tech" />
      <col class="next" />
      <col class="remarks" />
      <col class="created" />
      <col class="createdat" />
    </colgroup>
    <thead>
      <tr>
        <th class="nowrap">Service Date</th>
        <th class="nowrap">Service Type</th>
        <th class="nowrap">Technician</th>
        <th class="nowrap">Next Scheduled</th>
        <th class="nowrap">Remarks</th>
        <th class="nowrap">Created By</th>
        <th class="nowrap">Date Created</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result): while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td class="date"><?= $esc($fmt($row['service_date'])) ?></td>
          <td class="text wrap"><?= $esc($row['service_type']) ?></td>
          <td class="text wrap"><?= $esc($row['technician']) ?></td>
          <td class="date"><?= $esc($fmt($row['next_scheduled_date'])) ?></td>
          <td class="text wrap"><?= $esc($row['remarks']) ?></td>
          <td class="text wrap"><?= $esc($row['created_by']) ?></td>
          <td class="date"><?= $esc($fmt($row['date_created'])) ?></td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</body>
</html>
