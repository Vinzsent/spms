<?php
include '../includes/db.php';
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

if ($action === 'add') {
  $name = mysqli_real_escape_string($conn, trim($_POST['child_name'] ?? ''));
  if ($name === '') { echo json_encode(['success'=>false,'message'=>'Name required']); exit; }
  // duplicate check within same subcategory
  $chk = mysqli_query($conn, "SELECT id FROM account_sub_subcategories WHERE subcategory_id=$subcategory_id AND name='$name'");
  if ($chk && mysqli_num_rows($chk) > 0) { echo json_encode(['success'=>false,'message'=>'Name already exists']); exit; }
  $q = "INSERT INTO account_sub_subcategories (subcategory_id, name) VALUES ($subcategory_id, '$name')";
  if (mysqli_query($conn, $q)) { $res=['success'=>true,'message'=>'Added']; } else { $res['message']='DB error: '.mysqli_error($conn); }
}

if ($action === 'update') {
  $child_id = intval($_POST['child_id'] ?? 0);
  $name = mysqli_real_escape_string($conn, trim($_POST['child_name'] ?? ''));
  if ($child_id<=0 || $name==='') { echo json_encode(['success'=>false,'message'=>'Invalid data']); exit; }
  $chk = mysqli_query($conn, "SELECT id FROM account_sub_subcategories WHERE subcategory_id=$subcategory_id AND name='$name' AND id != $child_id");
  if ($chk && mysqli_num_rows($chk) > 0) { echo json_encode(['success'=>false,'message'=>'Name already exists']); exit; }
  $q = "UPDATE account_sub_subcategories SET name='$name' WHERE id=$child_id AND subcategory_id=$subcategory_id";
  if (mysqli_query($conn, $q)) { $res=['success'=>true,'message'=>'Updated']; } else { $res['message']='DB error: '.mysqli_error($conn); }
}

if ($action === 'delete') {
  $child_id = intval($_POST['child_id'] ?? 0);
  if ($child_id<=0) { echo json_encode(['success'=>false,'message'=>'Invalid child id']); exit; }
  $q = "DELETE FROM account_sub_subcategories WHERE id=$child_id AND subcategory_id=$subcategory_id";
  if (mysqli_query($conn, $q)) {
    if (mysqli_affected_rows($conn) > 0) { $res=['success'=>true,'message'=>'Deleted']; }
    else { $res['message']='Not found'; }
  } else { $res['message']='DB error: '.mysqli_error($conn); }
}

echo json_encode($res);
