<?php
include '../includes/db.php';
include '../includes/auth.php';
header('Content-Type: application/json');

$res = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid method']);
  exit;
}

$action = $_POST['action'] ?? '';
$subcategory_id = intval($_POST['subcategory_id'] ?? 0);

if (!in_array($action, ['add','update','delete'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid action']);
  exit;
}

if ($subcategory_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid subcategory']);
  exit;
}

// Ensure table exists (auto-create for robustness)
$createSql = "CREATE TABLE IF NOT EXISTS `account_sub_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `subcategory_id` (`subcategory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
mysqli_query($conn, $createSql);

if ($action === 'add') {
  $name = trim($_POST['child_name'] ?? '');
  if ($name === '') { echo json_encode(['success'=>false,'message'=>'Name required']); exit; }

  // duplicate check within same subcategory
  $stmt = mysqli_prepare($conn, "SELECT id FROM account_sub_subcategories WHERE subcategory_id = ? AND name = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, 'is', $subcategory_id, $name);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  if (mysqli_stmt_num_rows($stmt) > 0) { mysqli_stmt_close($stmt); echo json_encode(['success'=>false,'message'=>'Name already exists']); exit; }
  mysqli_stmt_close($stmt);

  $ins = mysqli_prepare($conn, "INSERT INTO account_sub_subcategories (subcategory_id, name) VALUES (?, ?)");
  mysqli_stmt_bind_param($ins, 'is', $subcategory_id, $name);
  if (mysqli_stmt_execute($ins)) {
    $res = ['success'=>true,'message'=>'Added successfully'];
  } else {
    $res['message'] = 'DB error: '.mysqli_error($conn);
  }
  mysqli_stmt_close($ins);
}

if ($action === 'update') {
  $child_id = intval($_POST['child_id'] ?? 0);
  $name = trim($_POST['child_name'] ?? '');
  if ($child_id<=0 || $name==='') { echo json_encode(['success'=>false,'message'=>'Invalid data']); exit; }

  $stmt = mysqli_prepare($conn, "SELECT id FROM account_sub_subcategories WHERE subcategory_id = ? AND name = ? AND id != ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, 'isi', $subcategory_id, $name, $child_id);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  if (mysqli_stmt_num_rows($stmt) > 0) { mysqli_stmt_close($stmt); echo json_encode(['success'=>false,'message'=>'Name already exists']); exit; }
  mysqli_stmt_close($stmt);

  $upd = mysqli_prepare($conn, "UPDATE account_sub_subcategories SET name = ? WHERE id = ? AND subcategory_id = ?");
  mysqli_stmt_bind_param($upd, 'sii', $name, $child_id, $subcategory_id);
  if (mysqli_stmt_execute($upd)) { $res=['success'=>true,'message'=>'Updated']; }
  else { $res['message']='DB error: '.mysqli_error($conn); }
  mysqli_stmt_close($upd);
}

if ($action === 'delete') {
  $child_id = intval($_POST['child_id'] ?? 0);
  if ($child_id<=0) { echo json_encode(['success'=>false,'message'=>'Invalid child id']); exit; }
  $del = mysqli_prepare($conn, "DELETE FROM account_sub_subcategories WHERE id = ? AND subcategory_id = ?");
  mysqli_stmt_bind_param($del, 'ii', $child_id, $subcategory_id);
  if (mysqli_stmt_execute($del)) {
    if (mysqli_affected_rows($conn) > 0) { $res=['success'=>true,'message'=>'Deleted']; }
    else { $res['message']='Not found'; }
  } else { $res['message']='DB error: '.mysqli_error($conn); }
  mysqli_stmt_close($del);
}

echo json_encode($res);

