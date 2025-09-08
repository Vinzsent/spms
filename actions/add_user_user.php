<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $title           = trim($_POST['title'] ?? '');
    $first_name      = trim($_POST['first_name'] ?? '');
    $middle_name     = trim($_POST['middle_name'] ?? '');
    $last_name       = trim($_POST['last_name'] ?? '');
    $suffix          = trim($_POST['suffix'] ?? '');
    $academic_title  = trim($_POST['academic_title'] ?? '');
    $user_type       = trim($_POST['user_type'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

    // Simple validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($_POST['password']) || empty($user_type)) {
        echo "Required fields are missing.";
        exit;
    }

    // Insert into the database
    $stmt = $conn->prepare("
        INSERT INTO user 
        (title, first_name, middle_name, last_name, suffix, academic_title, user_type, email, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssssss", 
        $title, $first_name, $middle_name, $last_name, $suffix, 
        $academic_title, $user_type, $email, $password
    );

    if ($stmt->execute()) {
        header("Location: ../pages/users.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}
?>
