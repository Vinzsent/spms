<?php
include '../includes/db.php';
session_start();

// Function to set success/error messages
function setMessage($type, $message) {
    $_SESSION['message_type'] = $type;
    $_SESSION['message'] = $message;
}

if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['account_name']);
    
    if (!empty($name)) {
        // Check if name already exists (excluding current record)
        $check_query = "SELECT id FROM account_types WHERE name='$name' AND id != $id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            setMessage('error', 'Account type name already exists!');
        } else {
            $update_query = "UPDATE account_types SET name='$name' WHERE id=$id";
            if (mysqli_query($conn, $update_query)) {
                setMessage('success', 'Account type updated successfully!');
            } else {
                setMessage('error', 'Error updating account type: ' . mysqli_error($conn));
            }
        }
    } else {
        setMessage('error', 'Account type name cannot be empty!');
    }
}

if (isset($_POST['delete'])) {
    $id = intval($_POST['id']);
    
    // First delete all subcategories
    $delete_sub_query = "DELETE FROM account_subcategories WHERE parent_id = $id";
    mysqli_query($conn, $delete_sub_query);
    
    // Then delete the main category
    $delete_query = "DELETE FROM account_types WHERE id=$id";
    if (mysqli_query($conn, $delete_query)) {
        setMessage('success', 'Account type and all subcategories deleted successfully!');
    } else {
        setMessage('error', 'Error deleting account type: ' . mysqli_error($conn));
    }
}

if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, $_POST['new_account_name']);
    
    if (!empty($name)) {
        // Check if name already exists
        $check_query = "SELECT id FROM account_types WHERE name='$name'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            setMessage('error', 'Account type name already exists!');
        } else {
            $insert_query = "INSERT INTO account_types (name) VALUES ('$name')";
            if (mysqli_query($conn, $insert_query)) {
                setMessage('success', 'Account type added successfully!');
            } else {
                setMessage('error', 'Error adding account type: ' . mysqli_error($conn));
            }
        }
    } else {
        setMessage('error', 'Account type name cannot be empty!');
    }
}

header("Location: ../pages/assets.php");
exit();
