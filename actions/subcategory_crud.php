<?php
include '../includes/db.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

$action = $_POST['action'] ?? '';
$parent_id = intval($_POST['parent_id'] ?? 0);

switch ($action) {
    case 'add':
        $subcategory_name = mysqli_real_escape_string($conn, trim($_POST['subcategory_name'] ?? ''));
        
        if (empty($subcategory_name)) {
            $response['message'] = 'Subcategory name cannot be empty';
            break;
        }
        
        if ($parent_id <= 0) {
            $response['message'] = 'Invalid parent category';
            break;
        }
        
        // Check if subcategory already exists for this parent
        $check_query = "SELECT id FROM account_subcategories WHERE parent_id = $parent_id AND name = '$subcategory_name'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $response['message'] = 'Subcategory already exists for this account type';
            break;
        }
        
        // Insert new subcategory
        $insert_query = "INSERT INTO account_subcategories (parent_id, name) VALUES ($parent_id, '$subcategory_name')";
        if (mysqli_query($conn, $insert_query)) {
            $response['success'] = true;
            $response['message'] = 'Subcategory added successfully';
        } else {
            $response['message'] = 'Error adding subcategory: ' . mysqli_error($conn);
        }
        break;
        
    case 'update':
        $subcategory_id = intval($_POST['subcategory_id'] ?? 0);
        $subcategory_name = mysqli_real_escape_string($conn, trim($_POST['subcategory_name'] ?? ''));
        
        if (empty($subcategory_name)) {
            $response['message'] = 'Subcategory name cannot be empty';
            break;
        }
        
        if ($subcategory_id <= 0) {
            $response['message'] = 'Invalid subcategory ID';
            break;
        }
        
        // Check if subcategory name already exists for this parent (excluding current record)
        $check_query = "SELECT id FROM account_subcategories WHERE parent_id = $parent_id AND name = '$subcategory_name' AND id != $subcategory_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $response['message'] = 'Subcategory name already exists for this account type';
            break;
        }
        
        // Update subcategory
        $update_query = "UPDATE account_subcategories SET name = '$subcategory_name' WHERE id = $subcategory_id AND parent_id = $parent_id";
        if (mysqli_query($conn, $update_query)) {
            $response['success'] = true;
            $response['message'] = 'Subcategory updated successfully';
        } else {
            $response['message'] = 'Error updating subcategory: ' . mysqli_error($conn);
        }
        break;
        
    case 'delete':
        $subcategory_id = intval($_POST['subcategory_id'] ?? 0);
        
        if ($subcategory_id <= 0) {
            $response['message'] = 'Invalid subcategory ID';
            break;
        }
        
        // Delete subcategory
        $delete_query = "DELETE FROM account_subcategories WHERE id = $subcategory_id AND parent_id = $parent_id";
        if (mysqli_query($conn, $delete_query)) {
            if (mysqli_affected_rows($conn) > 0) {
                $response['success'] = true;
                $response['message'] = 'Subcategory deleted successfully';
            } else {
                $response['message'] = 'Subcategory not found or already deleted';
            }
        } else {
            $response['message'] = 'Error deleting subcategory: ' . mysqli_error($conn);
        }
        break;
        
    default:
        $response['message'] = 'Invalid action';
        break;
}

echo json_encode($response);
?>
