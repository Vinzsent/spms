<?php
include '../includes/db.php'; // make sure you connect to your DB
include '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['new_name']);
    $subcategory_id = intval($_POST['subcategory_id']);

    $sql = "INSERT INTO account_sub_subcategories (subcategory_id, name, created_at, updated_at) 
            VALUES ('$subcategory_id', '$name', NOW(), NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "New subcategory added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

?>
